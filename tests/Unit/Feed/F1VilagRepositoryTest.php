<?php

namespace App\Tests\Unit\Feed;

use App\LegacyService\Feed\Collector\XMLCollector;
use App\LegacyService\Feed\Image\WordPressFeaturedImages;
use App\LegacyService\Feed\Item\Item;
use App\LegacyService\Feed\Repository\F1VilagRepository;
use PHPUnit\Framework\TestCase;

/**
 * Az f1vilag.hu WordPress RSS feedjének feldolgozása: a kép a poszt kiemelt képe (WordPress REST API), ennek
 * hiányában a description-be ágyazott <img>; a WordPress lábléc (angolul és magyarul) lekerül, a szöveg sima
 * (HTML nélküli) bevezető.
 */
class F1VilagRepositoryTest extends TestCase
{
    private const FIXTURE = __DIR__ . '/fixtures/f1vilag.xml';

    public function test_items_are_parsed_newest_first(): void
    {
        $items = (new F1VilagRepository(self::FIXTURE))->getItems();

        self::assertCount(2, $items);
        $first = array_values($items)[0];
        self::assertInstanceOf(Item::class, $first);
        self::assertSame(strtotime('Tue, 17 Nov 2026 10:20:00 +0000'), $first->getId());
        self::assertSame('Russell nem változtatna semmin az Antonelli elleni veresége után', $first->getTitle());
        self::assertSame('https://f1vilag.hu/hirek/george-russell-antonelli-vilagbajnoki-vereseg/', $first->getLink());
        self::assertSame('Tue, 17 Nov 2026 10:20:00 +0000', $first->getPublishDate());
    }

    public function test_image_comes_from_embedded_img_tag(): void
    {
        $first = array_values((new F1VilagRepository(self::FIXTURE))->getItems())[0];

        self::assertSame(
            'https://f1vilag.hu/wp-content/uploads/2026/11/2026MexicoGP_SUN_Mercedes_M647367_F1_Grand_Prix_of_Mexico.jpg',
            $first->getImage()
        );
    }

    public function test_description_is_the_lead_text_without_wordpress_footer(): void
    {
        $first = array_values((new F1VilagRepository(self::FIXTURE))->getItems())[0];

        self::assertSame(
            'George Russell csalódottan vette tudomásul, hogy mercedeses csapattársa, Kimi Antonelli szerezte meg '
            . 'az idei világbajnoki címet, saját felkészülésén és hozzáállásán azonban utólag sem változtatna.',
            $first->getDescription()
        );
    }

    public function test_item_without_image_gets_empty_image_and_tags_are_stripped(): void
    {
        $second = array_values((new F1VilagRepository(self::FIXTURE))->getItems())[1];

        self::assertSame('', $second->getImage());
        self::assertSame('Kép nélküli hír & R&D', $second->getTitle());
        // a description v-html-lel jelenik meg, ezért HTML-escape-elt sima szöveg
        self::assertSame('Rövid szöveg kiemeléssel &amp; entitással.', $second->getDescription());
    }

    public function test_unreachable_source_yields_no_items(): void
    {
        self::assertSame([], (new F1VilagRepository(__DIR__ . '/fixtures/missing.xml'))->getItems());
    }

    // --- 2026-11 óta: a description-ben nincs kép, a lábléc magyar; a kép a WordPress REST API-ból jön

    public function test_new_format_image_comes_from_the_featured_media_of_the_post_in_the_guid(): void
    {
        $fetcher = new RecordingFetcher([self::API => json_encode([
            self::post(1000577, 'https://f1vilag.hu/wp-content/uploads/2026/11/qatar-768x512.jpg'),
            ['id' => 1000500, 'featured_media' => 0],
        ])]);

        $items = array_values($this->newFormatRepository($fetcher)->getItems());

        self::assertCount(4, $items);
        self::assertSame('https://f1vilag.hu/wp-content/uploads/2026/11/qatar-768x512.jpg', $items[0]->getImage());
        // a REST nem adott képet: a content:encoded csapatlogója nem borítókép
        self::assertSame('', $items[1]->getImage());
        self::assertSame('', $items[2]->getImage());
        self::assertSame('', $items[3]->getImage());
        // egy kérés, csak a guid-ból kiolvasható poszt-id-kkel (a permalinkes guid kimarad)
        self::assertCount(1, $fetcher->requested);
        parse_str((string) parse_url($fetcher->requested[0], PHP_URL_QUERY), $query);
        self::assertSame('1000577,1000540,1000500', $query['include']);
    }

    public function test_new_format_lead_drops_the_hungarian_footer_but_keeps_a_lead_starting_with_a(): void
    {
        $items = array_values($this->newFormatRepository(new RecordingFetcher())->getItems());

        self::assertSame(
            'A Forma-1-es Katari Nagydíj pénteki napján egyértelműen a Mercedes diktálta a tempót.',
            $items[0]->getDescription()
        );
        self::assertSame('Kimi Antonelli nyerte a második szabadedzést.', $items[1]->getDescription());
    }

    public function test_featured_media_wins_over_the_embedded_image(): void
    {
        $fetcher = new RecordingFetcher([self::API => json_encode([self::post(221819, 'https://f1vilag.hu/featured.jpg')])]);
        $repository = new F1VilagRepository(self::FIXTURE, new XMLCollector(), new WordPressFeaturedImages(self::API, $fetcher));

        $items = array_values($repository->getItems());

        self::assertSame('https://f1vilag.hu/featured.jpg', $items[0]->getImage());
        self::assertSame('', $items[1]->getImage());
    }

    public function test_unreachable_api_keeps_the_embedded_image_and_leaves_the_others_empty(): void
    {
        $repository = new F1VilagRepository(self::FIXTURE, new XMLCollector(), new WordPressFeaturedImages(self::API, new RecordingFetcher()));

        $items = array_values($repository->getItems());

        self::assertSame(
            'https://f1vilag.hu/wp-content/uploads/2026/11/2026MexicoGP_SUN_Mercedes_M647367_F1_Grand_Prix_of_Mexico.jpg',
            $items[0]->getImage()
        );
        self::assertSame('', $items[1]->getImage());
        self::assertSame('', array_values($this->newFormatRepository(new RecordingFetcher())->getItems())[0]->getImage());
    }

    private const API = 'https://f1vilag.hu/wp-json/wp/v2/posts';

    private function newFormatRepository(RecordingFetcher $fetcher): F1VilagRepository
    {
        return new F1VilagRepository(
            __DIR__ . '/fixtures/f1vilag-2026-11.xml',
            new XMLCollector(),
            new WordPressFeaturedImages(self::API, $fetcher)
        );
    }

    private static function post(int $id, string $image): array
    {
        return ['id' => $id, 'featured_media' => 1, '_embedded' => ['wp:featuredmedia' => [['source_url' => $image]]]];
    }
}
