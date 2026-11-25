<?php

namespace App\Tests\Integration\Twig;

use App\Entity\Prize;
use App\Entity\PrizeDraw;
use App\Repository\PrizeDrawRepository;
use App\Repository\UserRepository;
use App\Twig\PrizeDrawRuntimeExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Nyeremény modul (templates/extension/prize_draw.html.twig): az admin "Nyeremény" beállításából (PrizeDraw, id = 1).
 */
class PrizeDrawTemplateTest extends KernelTestCase
{
    private function prizeDraw(bool $entryOpen, ?string $note, array $prizes): PrizeDraw
    {
        $prizeDraw = (new PrizeDraw())->setEntryOpen($entryOpen)->setNote($note);
        foreach ($prizes as $place => $amount) {
            $prizeDraw->addPrize((new Prize())->setPlace($place)->setAmount($amount));
        }

        return $prizeDraw;
    }

    private function render(?PrizeDraw $prizeDraw, int $participants = 0, bool $loggedIn = true): Crawler
    {
        self::bootKernel();
        $prizeDraws = $this->createMock(PrizeDrawRepository::class);
        $prizeDraws->method('get')->willReturn($prizeDraw);
        $users = $this->createMock(UserRepository::class);
        $users->method('countAlternativeChampionshipUsers')->willReturn($participants);

        $extension = new PrizeDrawRuntimeExtension(static::getContainer()->get('twig'), $prizeDraws, $users);

        return new Crawler('<div id="root">' . $extension->renderPrizeDrawExtension($loggedIn ? 1 : 0) . '</div>');
    }

    private function rows(Crawler $crawler): array
    {
        return $crawler->filter('.f1-row')->each(fn(Crawler $row) => trim($row->filter('.f1-pos')->text()) . ':' . trim($row->filter('.f1-num--main')->text()));
    }

    public function test_closed_entry_lists_prizes_by_place_with_the_note(): void
    {
        $crawler = $this->render($this->prizeDraw(false, '12 résztvevő X 5000', [2 => 14400, 1 => 24000, 3 => 9600]), 7);

        self::assertSame(['1:24,000', '2:14,400', '3:9,600'], $this->rows($crawler));
        self::assertCount(1, $crawler->filter('.f1-pos--p1'));
        self::assertSame('12 résztvevő X 5000', trim($crawler->filter('.f1-board__note')->text()));
        self::assertStringNotContainsString('résztvevő van', $crawler->text());
    }

    public function test_open_entry_shows_only_the_live_participant_count(): void
    {
        $crawler = $this->render($this->prizeDraw(true, '12 résztvevő X 5000', [1 => 24000]), 4);

        self::assertSame([], $this->rows($crawler));
        self::assertCount(1, $crawler->filter('.f1-board__note'));
        self::assertSame('Jelenleg 4 résztvevő van, a díjazás még nincs kiszámolva.', trim($crawler->filter('.f1-board__note')->text()));
        self::assertCount(1, $crawler->filter('.f1-board__tag'));
    }

    public function test_closed_entry_without_prizes_and_note_renders_an_empty_board(): void
    {
        $crawler = $this->render($this->prizeDraw(false, '  ', []));

        self::assertSame([], $this->rows($crawler));
        self::assertCount(0, $crawler->filter('.f1-board__note'));
        self::assertSame('Nyeremény', trim($crawler->filter('.f1-board__tag')->text()));
    }

    public function test_missing_prize_draw_row_renders_nothing(): void
    {
        self::assertSame('', trim($this->render(null)->filter('#root')->html()));
    }

    public function test_guest_sees_nothing(): void
    {
        $crawler = $this->render($this->prizeDraw(false, 'x', [1 => 100]), 3, false);

        self::assertSame('', trim($crawler->filter('#root')->html()));
    }
}
