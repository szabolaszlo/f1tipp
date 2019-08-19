<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.12.25.
 * Time: 22:45
 */

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\Feed as FeedEntity;

/**
 * Class Feed
 * @package App\Repository
 */
class Feed extends EntityRepository
{
    /**
     * @return array|mixed
     */
    public function deleteOldFeeds()
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval('P15D'));

        $query = $this->createQueryBuilder('f')
            ->delete(FeedEntity::class, 'f')
            ->where('f.publishDate < :time')
            ->setParameter('time', $date)
            ->getQuery();

        $query->execute();
    }
}
