<?php

namespace App\EventSubscriber;

use App\Calculator\Calculator;
use App\Entity\Result;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class EasyAdminSubscriber
 * @package App\EventSubscriber
 */
class EasyAdminSubscriber implements EventSubscriberInterface
{
    /**
     * @var Calculator
     */
    protected $calculator;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * EasyAdminSubscriber constructor.
     * @param Calculator $calculator
     * @param EntityManagerInterface $em
     */
    public function __construct(Calculator $calculator, EntityManagerInterface $em)
    {
        $this->calculator = $calculator;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'easy_admin.post_persist' => array('calculateResults'),
            'easy_admin.post_update' => array('calculateResults'),
            'easy_admin.post_remove' => array('calculateResults'),
        );
    }

    /**
     * @param GenericEvent $event
     */
    public function calculateResults(GenericEvent $event)
    {
        $entity = $event->getSubject();

        if (!($entity instanceof Result)) {
            return;
        }
        $event = $entity->getEvent();

        $this->em->getRepository('App:Bet')->clearBetPointsByEvent($event);
        $weekendEvents = $this->em->getRepository('App:Event')->getWeekendEvents($event);
        $this->em->getRepository('App:Trophy')->removeTrophiesByEvents($weekendEvents);
        $this->em->getRepository('App:AlternativeChampionship')->removeAlterChampsByEvents($weekendEvents);

        //TODO: Clear cache
        $this->em->flush();

        $this->calculator->calculate();
    }
}
