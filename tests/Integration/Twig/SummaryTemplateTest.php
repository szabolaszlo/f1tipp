<?php

namespace App\Tests\Integration\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;

class SummaryTemplateTest extends KernelTestCase
{
    private function row(string $name, int $collected, int $points): object
    {
        return new class($name, $collected, $points) {
            private $user; private $collected; private $points; private $race;
            public function __construct($name, $collected, $points) {
                $this->user = new class($name) { private $n; public function __construct($n) { $this->n = $n; } public function getName() { return $this->n; } };
                $this->race = new class { public function getName() { return 'ABU DHABI'; } };
                $this->collected = $collected; $this->points = $points;
            }
            public function getUser() { return $this->user; }
            public function getCollectedPoints() { return $this->collected; }
            public function getPoints() { return $this->points; }
            public function getRace() { return $this->race; }
        };
    }

    private function render(array $result): Crawler
    {
        self::bootKernel();
        $html = static::getContainer()->get('twig')->render('extension/result_table/type/summary.html.twig', ['result' => $result]);

        return new Crawler('<div id="root">' . $html . '</div>');
    }

    public function test_summary_ranks_ties_and_formats_points(): void
    {
        $crawler = $this->render([
            $this->row('Steve', 31, 14),
            $this->row('Karlitosz', 25, 10),
            $this->row('Blasio', 24, 8),
            $this->row('Quazi', 24, 8),
            $this->row('Borintius', 0, 0),
        ]);

        self::assertSame(['1', '2', '3', '3', '5'], $crawler->filter('.f1-row .f1-pos')->each(fn(Crawler $n) => trim($n->text())));
        self::assertSame(['+14', '+10', '+8', '+8', '0'], $crawler->filter('.f1-row .f1-num--main')->each(fn(Crawler $n) => trim($n->text())));
        self::assertSame(['31', '25', '24', '24', '0'], $crawler->filter('.f1-row .f1-num--dim')->each(fn(Crawler $n) => trim($n->text())));
        self::assertCount(2, $crawler->filter('.f1-pos--p3'));
        self::assertStringContainsString('ABU DHABI', $crawler->filter('.f1-board__title')->text());
    }

    public function test_summary_empty_result_renders_nothing(): void
    {
        self::assertSame('', trim($this->render([])->filter('#root')->html()));
    }
}
