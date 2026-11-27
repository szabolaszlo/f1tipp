<?php

namespace App\Tests\Unit\Season;

use App\Season\ChecklistItem;
use App\Season\SeasonChecklist;
use App\Season\SeasonCounts;
use App\Season\SeasonFacts;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class SeasonChecklistTest extends TestCase
{
    public function test_finds_hashed_image_urls(): void
    {
        $html = '<p><img src="/build/images/trophy_gold.c61f05af.png"> <img src="/build/images/trophy/trophy_gold.svg"></p>';

        self::assertSame(['/build/images/trophy_gold.c61f05af.png'], SeasonChecklist::findHashedImageUrls($html));
    }

    public function test_hashless_image_urls_are_not_found(): void
    {
        self::assertSame([], SeasonChecklist::findHashedImageUrls('<img src="/build/images/checkered-flag.png">'));
    }

    public function test_drivers_without_team(): void
    {
        $teams = [['name' => 'Red Bull', 'color' => '#3671C6', 'match' => ['red bull'], 'drivers' => ['VER']]];

        self::assertSame(['TSU'], SeasonChecklist::driversWithoutTeam(['VER', 'TSU'], $teams));
    }

    public function test_empty_rapidapi_key_fails(): void
    {
        foreach ([null, ''] as $key) {
            $item = $this->item($this->checklist($key)->build($this->facts(), new \DateTimeImmutable()), 'rapidapi_key');

            self::assertSame('fail', $item->status);
        }
    }

    public function test_set_rapidapi_key_is_ok(): void
    {
        $item = $this->item($this->checklist('secret')->build($this->facts(), new \DateTimeImmutable()), 'rapidapi_key');

        self::assertSame('ok', $item->status);
    }

    public function test_sections_and_keys(): void
    {
        $sections = $this->checklist('secret')->build($this->facts(), new \DateTimeImmutable());

        self::assertSame(['before_reset', 'new_season', 'after_first_race'], array_keys($sections));
        self::assertSame(
            ['backup', 'archive', 'hashed_images', 'alter_archive'],
            array_map(static fn (ChecklistItem $i) => $i->key, $sections['before_reset'])
        );
        self::assertSame(
            ['stale_events', 'championship_points', 'prize', 'participants', 'drivers_without_team', 'rules_title', 'deploy_code', 'after_deploy'],
            array_map(static fn (ChecklistItem $i) => $i->key, $sections['new_season'])
        );
        self::assertSame(
            ['rapidapi_key', 'driver_sync', 'championship_sidebar'],
            array_map(static fn (ChecklistItem $i) => $i->key, $sections['after_first_race'])
        );
    }

    public function test_without_closing_year_the_archive_checks_are_neutral(): void
    {
        $sections = $this->checklist('secret')->build($this->facts(closingYear: null, archive: false), new \DateTimeImmutable());

        self::assertSame('neutral', $this->item($sections, 'archive')->status);
        self::assertSame('neutral', $this->item($sections, 'hashed_images')->status);
        self::assertSame('neutral', $this->item($sections, 'alter_archive')->status);
    }

    public function test_missing_archive_fails(): void
    {
        $sections = $this->checklist('secret')->build($this->facts(archive: false), new \DateTimeImmutable());

        self::assertSame('fail', $this->item($sections, 'archive')->status);
    }

    public function test_archive_with_hashed_image_fails_and_lists_the_url(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('fetchFirstColumn')->willReturnCallback(
            static fn (string $sql) => str_contains($sql, 'FROM information')
                ? ['<img src="/build/images/trophy_gold.c61f05af.png">']
                : []
        );
        $checklist = new SeasonChecklist($connection, [], 'secret');

        $item = $this->item($checklist->build($this->facts(), new \DateTimeImmutable()), 'hashed_images');

        self::assertSame('fail', $item->status);
        self::assertStringContainsString('/build/images/trophy_gold.c61f05af.png', (string) $item->detail);
    }

    public function test_stale_events_are_counted_with_the_given_now(): void
    {
        $now = new \DateTimeImmutable('2027-01-02 10:00:00');
        $connection = $this->createMock(Connection::class);
        $connection->method('fetchFirstColumn')->willReturn([]);
        $connection->method('fetchAssociative')->willReturn(false);
        $connection->method('fetchOne')->willReturnCallback(
            static function (string $sql, array $params = []) use ($now) {
                if (str_contains($sql, 'FROM `event`') && str_contains($sql, 'NOT EXISTS')) {
                    self::assertSame([$now->format('Y-m-d H:i:s')], $params);

                    return '4';
                }

                return 0;
            }
        );
        $checklist = new SeasonChecklist($connection, [], 'secret');

        $item = $this->item($checklist->build($this->facts(), $now), 'stale_events');

        self::assertSame('fail', $item->status);
        self::assertStringContainsString('4 esemény', $item->label);
    }

    public function test_rules_title_must_contain_the_current_year(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('fetchFirstColumn')->willReturn([]);
        $connection->method('fetchAssociative')->willReturn(false);
        $connection->method('fetchOne')->willReturnCallback(
            static fn (string $sql) => str_contains($sql, 'SELECT title FROM information') ? 'Szabályzat 2027' : 0
        );
        $checklist = new SeasonChecklist($connection, [], 'secret');

        self::assertSame('ok', $this->item($checklist->build($this->facts(currentYear: 2027), new \DateTimeImmutable()), 'rules_title')->status);
        self::assertSame('fail', $this->item($checklist->build($this->facts(currentYear: 2028), new \DateTimeImmutable()), 'rules_title')->status);
    }

    /** a lekérdezések üres vagy nulla értéket adnak; a DB-hez a unit teszt soha nem nyúl */
    private function checklist(?string $rapidApiKey): SeasonChecklist
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('fetchOne')->willReturn(0);
        $connection->method('fetchFirstColumn')->willReturn([]);
        $connection->method('fetchAssociative')->willReturn(false);

        return new SeasonChecklist($connection, [], $rapidApiKey);
    }

    private function facts(?int $closingYear = 2026, ?int $currentYear = 2027, bool $archive = true): SeasonFacts
    {
        return new SeasonFacts(new SeasonCounts(1, 1, 1, 1, 1, 1, 1), $closingYear, $currentYear, $archive, false, 0, 0);
    }

    /** @param array<string, list<ChecklistItem>> $sections */
    private function item(array $sections, string $key): ChecklistItem
    {
        foreach ($sections as $items) {
            foreach ($items as $item) {
                if ($item->key === $key) {
                    return $item;
                }
            }
        }

        self::fail("nincs $key pont");
    }
}
