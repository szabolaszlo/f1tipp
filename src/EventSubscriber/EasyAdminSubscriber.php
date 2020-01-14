<?php

namespace App\EventSubscriber;

use App\Cache\FileCache;
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
     * @var FileCache
     */
    protected $fileCache;

    /**
     * EasyAdminSubscriber constructor.
     * @param Calculator $calculator
     * @param EntityManagerInterface $em
     * @param FileCache $fileCache
     */
    public function __construct(Calculator $calculator, EntityManagerInterface $em, FileCache $fileCache)
    {
        $this->calculator = $calculator;
        $this->em = $em;
        $this->fileCache = $fileCache;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'easy_admin.post_persist' => array('calculateResults'),
            'easy_admin.post_update' => array('reCalculateResults'),
            'easy_admin.post_remove' => array('reCalculateResults'),
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
        $this->calculator->calculate();
    }

    /**
     * @param GenericEvent $event
     */
    public function reCalculateResults(GenericEvent $event)
    {
        $entity = $event->getSubject();

        if (!($entity instanceof Result)) {
            return;
        }
        $event = $entity->getEvent();

        $this->em->getRepository('App:Bet')->clearBetPointsByEvent($event);
        $this->em->getRepository('App:BetAttribute')->clearBetAttributePointsByEvent($event);
        $weekendEvents = $this->em->getRepository('App:Event')->getWeekendEvents($event);
        $this->em->getRepository('App:Trophy')->removeTrophiesByEvents($weekendEvents);
        $this->em->getRepository('App:AlternativeChampionship')->removeAlterChampsByEvents($weekendEvents);

        $this->em->flush();

        $this->calculator->calculate();

        $this->fileCache->clearAll();
    }
}
