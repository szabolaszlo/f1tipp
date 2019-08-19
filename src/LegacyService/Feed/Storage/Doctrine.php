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
 * @package App\LegacyService\Feed\Storage
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

        if ($existFeed) {
            return;
        }

        $feed = new Feed();

        $feed
            ->setId($item->getId())
            ->setTitle($item->getTitle())
            ->setDescription($item->getDescription())
            ->setLink($item->getLink())
            ->setImage($item->getImage())
            ->setPublishDate(new \DateTime($item->getPublishDate()));

        $this->entityManager->persist($feed);
    }

    public function save()
    {
        $this->entityManager->flush();
    }
}
