<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.12.26.
 * Time: 20:47
 */

namespace App\Controller\Page\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Bet;
use App\Entity\Event;
use App\Entity\Qualify;
use App\Entity\Race;
use App\Entity\Result;
use App\Entity\Trophy;

/**
 * Class ResetSeason
 * @package App\Controller\Page\Admin
 */
class ResetSeason extends AbstractController
{
    public function resetAction()
    {
        if (!$this->registry->getUserAuth()->isAdmin()) {
            return;
        }

        $qb = $this->entityManager->createQueryBuilder();

        $qb->delete(Bet::class)->getQuery()->execute();

        $qb->delete(Result::class)->getQuery()->execute();

        $qb->delete(Trophy::class)->getQuery()->execute();

        $qualifies = $this->entityManager->getRepository(Qualify::class)->findAll();

        $races = $this->entityManager->getRepository(Race::class)->findAll();

        $events = array_merge($qualifies, $races);

        /** @var Event $event */
        foreach ($events as $event) {
            $eventDate = $event->getDateTime();
            $eventYear = (int)$eventDate->format('Y');
            $now = new \DateTime();
            $actualYear = (int)$now->format('Y');

            if ($eventYear <= $actualYear) {
                $eventDate->add(new \DateInterval('P1Y'));
                $event->setDateTime(new \DateTime($eventDate->format('Y-m-d H:i:s')));
                $this->entityManager->persist($event);
            }
        }

        $this->entityManager->flush();

        $this->registry->getServer()->redirect('page=admin/clear_cache/clear');
    }
}
