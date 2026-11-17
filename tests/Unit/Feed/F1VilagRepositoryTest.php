<?php

namespace App\Tests\Unit\Feed;

use App\LegacyService\Feed\Item\Item;
use App\LegacyService\Feed\Repository\F1VilagRepository;
use PHPUnit\Framework\TestCase;

/**
 * Az f1vilag.hu WordPress RSS feedjének feldolgozása: a kép a description-be ágyazott <img>-ből jön,
 * a „The post … appeared first on” lábléc lekerül, a szöveg sima (HTML nélküli) bevezető.
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
}
