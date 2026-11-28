<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.27.
 * Time: 23:46
 */

namespace App\LegacyService\Feed\Storage;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Feed;
use App\LegacyService\Feed\Item\Item;

/**
 * Class Doctrine
 * @package App\LegacyService\FeedController\Storage
 */
class Doctrine implements IStorage
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Doctrine constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Item $item
     */
    public function setItem(Item $item)
    {
        $existFeed = $this
            ->entityManager
            ->getRepository(Feed::class)
            ->find($item->getId());

        // a már tárolt hír a feed szerint frissül (pl. egy parser-javítás után); az id, a link és a dátum marad,
        // és üres képpel (pl. elérhetetlen WordPress API) nem írjuk felül a meglévőt
        if ($existFeed) {
            $existFeed
                ->setTitle($item->getTitle())
                ->setDescription($item->getDescription());
            if ($item->getImage() !== '') {
                $existFeed->setImage($item->getImage());
            }

            return;
        }

        $feed = new Feed();

        $feed
            ->setId($item->getId())
            ->setTitle($item->getTitle())
            ->setDescription($item->getDescription())
            ->setLink($item->getLink())
            ->setImage($item->getImage())
            // a DB időzóna nélkül tárol, ezért az alkalmazás időzónájára váltunk (a feed UTC-ben adja)
            ->setPublishDate(
                (new \DateTime($item->getPublishDate()))
                    ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
            );

        $this->entityManager->persist($feed);
    }

    public function save()
    {
        $this->entityManager->flush();
    }
}
