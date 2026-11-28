<?php

namespace App\Tests\Unit\Feed;

use App\Entity\Feed;
use App\LegacyService\Feed\Item\Item;
use App\LegacyService\Feed\Storage\Doctrine;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

/**
 * Hír mentése: az új hír beszúródik; a már tárolt hír címe és bevezetője frissül, a képe csak nem üres képre.
 * (A 2026-11-es feedváltás után kép nélkül, lábléccel mentett hírek így a következő gyűjtéskor javulnak.)
 */
class DoctrineStorageTest extends TestCase
{
    public function test_new_item_is_persisted(): void
    {
        $em = $this->entityManager(null);
        $em->expects(self::once())->method('persist')->with(self::callback(
            static fn (Feed $feed): bool => $feed->getId() === 1795813200 && $feed->getImage() === 'https://f1vilag.hu/new.jpg'
        ));

        (new Doctrine($em))->setItem(self::item('https://f1vilag.hu/new.jpg'));
    }

    public function test_stored_item_gets_the_new_title_lead_and_image(): void
    {
        $stored = self::stored('');
        $em = $this->entityManager($stored);
        $em->expects(self::never())->method('persist');

        (new Doctrine($em))->setItem(self::item('https://f1vilag.hu/new.jpg'));

        self::assertSame('Új cím', $stored->getTitle());
        self::assertSame('Tiszta bevezető.', $stored->getDescription());
        self::assertSame('https://f1vilag.hu/new.jpg', $stored->getImage());
        self::assertSame('https://f1vilag.hu/hirek/regi/', $stored->getLink());
        self::assertEquals(new \DateTime('2026-11-27 22:00:00'), $stored->getPublishDate());
    }

    public function test_empty_image_does_not_overwrite_the_stored_one(): void
    {
        $stored = self::stored('https://f1vilag.hu/stored.jpg');

        (new Doctrine($this->entityManager($stored)))->setItem(self::item(''));

        self::assertSame('https://f1vilag.hu/stored.jpg', $stored->getImage());
        self::assertSame('Tiszta bevezető.', $stored->getDescription());
    }

    private function entityManager(?Feed $stored): EntityManagerInterface
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(1795813200)->willReturn($stored);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->with(Feed::class)->willReturn($repository);

        return $em;
    }

    private static function item(string $image): Item
    {
        $item = new Item();
        $item->setId(1795813200);
        $item->setTitle('Új cím');
        $item->setDescription('Tiszta bevezető.');
        $item->setLink('https://f1vilag.hu/hirek/uj/');
        $item->setPublishDate('Fri, 27 Nov 2026 21:00:00 +0000');
        $item->setImage($image);

        return $item;
    }

    private static function stored(string $image): Feed
    {
        $feed = new Feed();
        $feed->setId(1795813200)
            ->setTitle('Régi cím')
            ->setDescription('Bevezető. A Régi cím bejegyzés először F1VILÁG.HU-én jelent meg.')
            ->setLink('https://f1vilag.hu/hirek/regi/')
            ->setPublishDate(new \DateTime('2026-11-27 22:00:00'))
            ->setImage($image);

        return $feed;
    }
}
