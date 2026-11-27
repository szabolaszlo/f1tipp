<?php

namespace App\Season;

use App\Entity\PrizeDraw;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * A Szezonváltás oldal élő checklistje (csak olvas): a pontok szakaszonként, az oldal sorrendjében.
 * A kézi pontok csak emlékeztetők, az állapotukat nem tároljuk.
 */
class SeasonChecklist
{
    /** a Szabályzat Információ-bejegyzés id-ja (a Rules.vue fixen ezt kéri) */
    private const RULES_INFORMATION_ID = 23;

    private const HASHED_IMAGE_PATTERN = '#/build/images/[^"\'\s]+\.[0-9a-f]{8}\.(png|svg|jpe?g|gif|webp)#';

    /**
     * @param list<array{drivers: list<string>}> $teams a f1tipp.teams paraméter
     */
    public function __construct(
        private readonly Connection $connection,
        #[Autowire('%f1tipp.teams%')] private readonly array $teams,
        #[Autowire('%env(default::RAPIDAPI_KEY)%')] private readonly ?string $rapidApiKey,
    ) {
    }

    /**
     * @return array<string, list<ChecklistItem>> before_reset, new_season, after_first_race
     */
    public function build(SeasonFacts $facts, \DateTimeImmutable $now): array
    {
        return [
            'before_reset' => [
                new ChecklistItem('backup', ChecklistItem::MANUAL, 'DB-mentés: Nethely phpMyAdmin → export'),
                $this->archive($facts),
                $this->hashedImages($facts),
                $this->alterArchive($facts),
            ],
            'new_season' => [
                $this->staleEvents($now),
                $this->championshipPoints(),
                $this->prize(),
                $this->participants(),
                $this->driversWithoutTeamItem(),
                $this->rulesTitle($facts),
                new ChecklistItem(
                    'deploy_code',
                    ChecklistItem::MANUAL,
                    'Kódban, deploy: csapatszínek és párosítás (f1tipp_teams.yaml), tippenkénti pontok, kupapontok, download_drivers.py'
                ),
                new ChecklistItem(
                    'after_deploy',
                    ChecklistItem::MANUAL,
                    'Élesítés után: docs/sql/pending.sql az éles DB-n, var/cache/prod törlése, Cache ürítése az adminban'
                ),
            ],
            'after_first_race' => [
                $this->rapidApiKey(),
                new ChecklistItem(
                    'driver_sync',
                    ChecklistItem::MANUAL,
                    'Pilótapontok szinkronja (Pilóták → szinkron gomb), csak az első futam után'
                ),
                new ChecklistItem(
                    'championship_sidebar',
                    ChecklistItem::MANUAL,
                    'Az oldalsáv pilóta-/konstruktőr-VB már az idei állást mutatja'
                ),
            ],
        ];
    }

    /**
     * @return list<string> az összes, hash-es fájlnevű (a következő buildnél eltörő) kép-URL
     */
    public static function findHashedImageUrls(string $html): array
    {
        preg_match_all(self::HASHED_IMAGE_PATTERN, $html, $matches);

        return array_values(array_unique($matches[0]));
    }

    /**
     * @param list<string>                       $activeShorts az aktív pilóták kódjai
     * @param list<array{drivers: list<string>}> $teams
     *
     * @return list<string> azok az aktív kódok, amelyek egyik csapat pilótalistájában sincsenek
     */
    public static function driversWithoutTeam(array $activeShorts, array $teams): array
    {
        $inTeam = [];
        foreach ($teams as $team) {
            foreach ($team['drivers'] ?? [] as $short) {
                $inTeam[$short] = true;
            }
        }

        return array_values(array_filter($activeShorts, static fn (string $short) => !isset($inTeam[$short])));
    }

    private function archive(SeasonFacts $facts): ChecklistItem
    {
        if ($facts->closingYear === null) {
            return new ChecklistItem('archive', ChecklistItem::NEUTRAL, 'Nincs lezárandó szezon, nincs mit archiválni');
        }

        return $facts->archiveExists
            ? new ChecklistItem('archive', ChecklistItem::OK, sprintf('A „%d” archív Információ-bejegyzés megvan', $facts->closingYear))
            : new ChecklistItem('archive', ChecklistItem::FAIL, sprintf('Hiányzik a „%d” archív Információ-bejegyzés (a nullázás tiltva)', $facts->closingYear));
    }

    private function hashedImages(SeasonFacts $facts): ChecklistItem
    {
        if ($facts->closingYear === null) {
            return new ChecklistItem('hashed_images', ChecklistItem::NEUTRAL, 'Nincs lezárandó szezon, nincs archív bejegyzés');
        }

        $contents = $this->connection->fetchFirstColumn(
            'SELECT content FROM information WHERE slug IN (?, ?)',
            [(string) $facts->closingYear, 'alter-' . $facts->closingYear]
        );
        $urls = [];
        foreach ($contents as $content) {
            $urls = [...$urls, ...self::findHashedImageUrls((string) $content)];
        }
        $urls = array_values(array_unique($urls));

        if ($urls === []) {
            return new ChecklistItem('hashed_images', ChecklistItem::OK, 'Az archív bejegyzésekben nincs hash-es kép-URL');
        }

        return new ChecklistItem(
            'hashed_images',
            ChecklistItem::FAIL,
            'Hash-es kép-URL az archív bejegyzésben, a következő buildnél eltörik (nem tiltja a nullázást)',
            implode(', ', $urls)
        );
    }

    private function alterArchive(SeasonFacts $facts): ChecklistItem
    {
        if ($facts->closingYear === null) {
            return new ChecklistItem('alter_archive', ChecklistItem::NEUTRAL, 'Nincs lezárandó szezon');
        }

        return new ChecklistItem(
            'alter_archive',
            ChecklistItem::INFO,
            sprintf('„alter-%d” bejegyzés: %s', $facts->closingYear, $facts->alterArchiveExists ? 'létezik' : 'nincs (csak ha volt alternatív összesítő)')
        );
    }

    private function staleEvents(\DateTimeImmutable $now): ChecklistItem
    {
        $count = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM `event` e WHERE e.date_time < ? AND NOT EXISTS (SELECT 1 FROM result r WHERE r.event = e.id)',
            [$now->format('Y-m-d H:i:s')]
        );

        return $count === 0
            ? new ChecklistItem('stale_events', ChecklistItem::OK, 'Nincs régi dátumú, eredmény nélküli esemény')
            : new ChecklistItem('stale_events', ChecklistItem::FAIL, sprintf('%d esemény régi dátummal, eredmény nélkül', $count));
    }

    private function championshipPoints(): ChecklistItem
    {
        $points = $this->connection->fetchFirstColumn('SELECT points FROM championship_point ORDER BY place');

        return new ChecklistItem(
            'championship_points',
            ChecklistItem::INFO,
            'Bajnoki pontok: ' . ($points === [] ? 'nincs sor' : implode('-', $points))
        );
    }

    private function prize(): ChecklistItem
    {
        $draw = $this->connection->fetchAssociative('SELECT entry_open FROM prize_draw WHERE id = ?', [PrizeDraw::ID]);
        if (!is_array($draw)) {
            return new ChecklistItem('prize', ChecklistItem::INFO, 'Nyeremény: hiányzik a beállítás-sor');
        }

        $rows = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM prize WHERE prize_draw_id = ?', [PrizeDraw::ID]);

        return new ChecklistItem(
            'prize',
            ChecklistItem::INFO,
            sprintf('Nyeremény: %s, %d díjsor', (bool) $draw['entry_open'] ? 'nevezés nyitva' : 'lezárva', $rows)
        );
    }

    private function participants(): ChecklistItem
    {
        $count = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM `user` WHERE is_alter_champs = 1');

        return new ChecklistItem('participants', ChecklistItem::INFO, sprintf('Résztvevők: %d „bajnokság tagja” felhasználó', $count));
    }

    private function driversWithoutTeamItem(): ChecklistItem
    {
        $missing = self::driversWithoutTeam(
            array_map('strval', $this->connection->fetchFirstColumn('SELECT short FROM driver WHERE status = 1 ORDER BY short')),
            $this->teams
        );

        return $missing === []
            ? new ChecklistItem('drivers_without_team', ChecklistItem::OK, 'Minden aktív pilóta szerepel a f1tipp_teams.yaml-ben')
            : new ChecklistItem(
                'drivers_without_team',
                ChecklistItem::FAIL,
                'Aktív pilóta csapat nélkül a f1tipp_teams.yaml-ben: ' . implode(', ', $missing)
            );
    }

    private function rulesTitle(SeasonFacts $facts): ChecklistItem
    {
        if ($facts->currentYear === null) {
            return new ChecklistItem('rules_title', ChecklistItem::NEUTRAL, 'Nincs esemény, nincs mihez hasonlítani a Szabályzat címét');
        }

        $title = $this->connection->fetchOne('SELECT title FROM information WHERE id = ?', [self::RULES_INFORMATION_ID]);
        if (!is_string($title)) {
            return new ChecklistItem('rules_title', ChecklistItem::FAIL, 'Hiányzik a Szabályzat (Információ #' . self::RULES_INFORMATION_ID . ')');
        }

        return str_contains($title, (string) $facts->currentYear)
            ? new ChecklistItem('rules_title', ChecklistItem::OK, sprintf('Szabályzat (Információ #%d) címe tartalmazza: %d', self::RULES_INFORMATION_ID, $facts->currentYear), $title)
            : new ChecklistItem('rules_title', ChecklistItem::FAIL, sprintf('Szabályzat (Információ #%d) címe nem tartalmazza: %d', self::RULES_INFORMATION_ID, $facts->currentYear), $title);
    }

    private function rapidApiKey(): ChecklistItem
    {
        return $this->rapidApiKey !== null && $this->rapidApiKey !== ''
            ? new ChecklistItem('rapidapi_key', ChecklistItem::OK, 'RAPIDAPI_KEY be van állítva (az érvényességét nem ellenőrzi)')
            : new ChecklistItem('rapidapi_key', ChecklistItem::FAIL, 'RAPIDAPI_KEY nincs beállítva');
    }
}
