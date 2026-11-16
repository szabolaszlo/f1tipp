<?php

namespace App\Tests\Integration\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;

class ResultTableTemplatesTest extends KernelTestCase
{
    use ResultTableFixtures;

    private const RACE_RESULT = ['VER', 'PIA', 'NOR', 'LEC', 'RUS', 'ALO', 'OCO', 'HAM', 'HUL', 'STR', 'Nem'];

    private function render(string $template, array $vars): Crawler
    {
        self::bootKernel();
        $html = static::getContainer()->get('twig')->render($template, $vars);

        return new Crawler('<div id="root">' . $html . '</div>');
    }

    private function raceTable(array $bets): Crawler
    {
        $event = $this->event('race');
        $result = $this->result($event, $this->attrs(array_map(fn($v) => [$v, null], self::RACE_RESULT), 10));

        return $this->render('extension/result_table/type/full.html.twig', [
            'result' => $result,
            'bets' => $bets,
            'usersCount' => 11,
            'noBettingUsers' => ['Barala94', 'Borintius'],
            'countableBets' => $bets,
        ]);
    }

    private function raceBet(string $name, int $points, ?string $firstClass = null, ?string $secondClass = null): object
    {
        $values = [['VER', $firstClass], ['NOR', $secondClass]];
        foreach (array_slice(self::RACE_RESULT, 2, 8) as $v) {
            $values[] = [$v, null];
        }
        $values[] = ['Igen', null];

        return $this->bet($name, $points, $this->attrs($values, 10));
    }

    public function test_full_race_marks_exact_and_near_chips(): void
    {
        $crawler = $this->raceTable([$this->raceBet('Steve', 22, 'bet-high', 'bet-low')]);

        $chips = $crawler->filter('.f1-tiprow:not(.f1-tiprow--head):not(.f1-tiprow--result) .f1-chip');
        self::assertStringContainsString('f1-chip--exact', $chips->eq(0)->attr('class'));
        self::assertStringContainsString('f1-chip--near', $chips->eq(1)->attr('class'));
        $chips->each(fn(Crawler $chip) => self::assertNull($chip->attr('style'), 'tippelő chip nem kaphat csapatszínt'));
    }

    public function test_full_result_row_uses_team_colors(): void
    {
        $crawler = $this->raceTable([$this->raceBet('Steve', 22)]);

        $first = $crawler->filter('.f1-tiprow--result .f1-chip')->eq(0);
        self::assertSame('VER', trim($first->text()));
        self::assertStringContainsString('#3671C6', $first->attr('style'));
    }

    public function test_full_ranks_ties(): void
    {
        $crawler = $this->raceTable([
            $this->raceBet('Steve', 22),
            $this->raceBet('Blasio', 18),
            $this->raceBet('Quazi', 18),
            $this->raceBet('Matt', 15),
        ]);

        $positions = $crawler->filter('.f1-tiprow .f1-pos')->each(fn(Crawler $n) => trim($n->text()));
        self::assertSame(['1', '2', '2', '4'], $positions);
        self::assertCount(0, $crawler->filter('.f1-pos--p1'));
    }

    public function test_full_question_chip_is_last_in_row(): void
    {
        $crawler = $this->raceTable([$this->raceBet('Steve', 22), $this->raceBet('Blasio', 18)]);

        $crawler->filter('.f1-tiprow:not(.f1-tiprow--head) .f1-chips')->each(function (Crawler $row) {
            $chips = $row->filter('.f1-chip');
            self::assertCount(11, $chips);
            self::assertStringContainsString('f1-chip--q', $chips->last()->attr('class'));
        });
        self::assertStringContainsString('--cols: 10', $crawler->filter('.f1-tiprow--result .f1-chips')->attr('style'));
        self::assertStringContainsString('f1-chips--q', $crawler->filter('.f1-tiprow--result .f1-chips')->attr('class'));
    }

    public function test_full_legend_shows_rule_points(): void
    {
        $legend = $this->raceTable([$this->raceBet('Steve', 22)])->filter('.f1-legend')->text();

        self::assertStringContainsString('3 pont', $legend);
        self::assertStringContainsString('rossz helyen', $legend);
        self::assertStringContainsString('SC', $legend);
    }

    public function test_sprint_table_has_eight_columns_and_no_near_legend(): void
    {
        $event = $this->event('sprint_qualify', 'BELGIUM', '2025-07-26 12:00:00');
        $codes = ['VER', 'PIA', 'NOR', 'LEC', 'RUS', 'ALO', 'OCO', 'HAM'];
        $result = $this->result($event, $this->attrs(array_map(fn($v) => [$v, null], $codes), 8));
        $bet = $this->bet('Steve', 3, $this->attrs(array_map(fn($v) => [$v, null], $codes), 8));

        $crawler = $this->render('extension/result_table/type/full.html.twig', [
            'result' => $result, 'bets' => [$bet], 'usersCount' => 11, 'noBettingUsers' => [], 'countableBets' => [$bet],
        ]);

        self::assertStringContainsString('--cols: 8', $crawler->filter('.f1-tiprow--result .f1-chips')->attr('style'));
        // repeat(0, ...) érvénytelen CSS lenne és szétesne a rács: kérdés nélkül nincs q-oszlop, se --q változó
        $crawler->filter('.f1-chips')->each(function (Crawler $chips) {
            self::assertStringNotContainsString('f1-chips--q', $chips->attr('class'));
            self::assertStringNotContainsString('--q', (string)$chips->attr('style'));
        });
        self::assertStringNotContainsString('rossz helyen', $crawler->filter('.f1-legend')->text());
    }

    public function test_only_bets_renders_neutral_chips_without_points(): void
    {
        $bet = $this->raceBet('Steve', 0);
        $crawler = $this->render('extension/result_table/type/only_bets.html.twig', [
            'event' => $this->event('race'), 'bets' => [$bet], 'usersCount' => 11, 'noBettingUsers' => [], 'countableBets' => [$bet],
        ]);

        self::assertCount(1, $crawler->filter('.f1-board'));
        self::assertCount(0, $crawler->filter('.f1-tiprow--result'));
        self::assertCount(0, $crawler->filter('.f1-legend'));
        self::assertCount(0, $crawler->filter('.f1-chip--exact, .f1-chip--near'));
        self::assertCount(11, $crawler->filter('.f1-tiprow:not(.f1-tiprow--head) .f1-chip'));
    }

    public function test_only_users_without_bets_shows_nobody_text(): void
    {
        $crawler = $this->render('extension/result_table/type/only_users.html.twig', [
            'event' => $this->event('race'), 'bets' => [], 'usersCount' => 11, 'noBettingUsers' => [], 'countableBets' => [],
        ]);

        self::assertStringContainsString('Még senki sem tippelt.', $crawler->filter('.f1-board')->text());
        self::assertCount(0, $crawler->filter('.f1-tiprow'));
        self::assertCount(1, $crawler->filter('#event-table-bet-numbers'));
    }

    public function test_pre_result_tables_list_users_without_tips(): void
    {
        foreach (['only_bets', 'only_users'] as $type) {
            $bets = array_map(fn($i) => $this->raceBet('User' . $i, 0), range(1, 9));
            $crawler = $this->render('extension/result_table/type/' . $type . '.html.twig', [
                'event' => $this->event('race'), 'bets' => $bets, 'usersCount' => 11,
                'noBettingUsers' => ['Barala94', 'Borintius'], 'countableBets' => $bets,
            ]);

            $text = $crawler->filter('.f1-board')->text();
            self::assertStringContainsString('Tippnélküliek', $text, $type);
            self::assertStringContainsString('Barala94, Borintius', $text, $type);
            self::assertStringContainsString('9/11', $text, $type);
        }
    }

    public function test_no_betting_list_hidden_until_majority_tipped(): void
    {
        $bet = $this->raceBet('Steve', 0);
        $crawler = $this->render('extension/result_table/type/only_bets.html.twig', [
            'event' => $this->event('race'), 'bets' => [$bet], 'usersCount' => 11,
            'noBettingUsers' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'], 'countableBets' => [$bet],
        ]);

        $text = $crawler->filter('.f1-board')->text();
        self::assertStringNotContainsString('Tippnélküliek', $text);
        self::assertStringNotContainsString('1/11', $text);
    }
}
