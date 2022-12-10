<?php

namespace App\EventSubscriber;

use App\Cache\FileCache;
use App\Calculator\Calculator;
use App\Entity\Event;
use App\Entity\Result;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Event\EntityLifecycleEventInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Class EasyAdminSubscriber
 * @package App\EventSubscriber
 */
class EasyAdminSubscriber implements EventSubscriberInterface
{
    /**
     * @var Calculator
     */
    protected Calculator $calculator;

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * @var FileCache
     */
    protected FileCache $fileCache;

    /**
     * @var FlashBagInterface
     */
    protected FlashBagInterface $flashBag;

    /**
     * EasyAdminSubscriber constructor.
     * @param Calculator $calculator
     * @param EntityManagerInterface $em
     * @param FileCache $fileCache
     * @param FlashBagInterface $flashBag
     */
    public function __construct(Calculator $calculator, EntityManagerInterface $em, FileCache $fileCache, FlashBagInterface $flashBag)
    {
        $this->calculator = $calculator;
        $this->em = $em;
        $this->fileCache = $fileCache;
        $this->flashBag = $flashBag;
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            AfterEntityPersistedEvent::class => array('calculateResults'),
            AfterEntityUpdatedEvent::class => [['reCalculateResults'], ['clearEventCaches']],
            AfterEntityDeletedEvent::class => array('reCalculateResults'),
        );
    }

    /**
     * @param EntityLifecycleEventInterface $event
     * @return void
     */
    public function clearEventCaches(EntityLifecycleEventInterface $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Event)) {
            return;
        }

        $resultCache = $this->em->getConfiguration()->getResultCacheImpl();
        $resultCache->delete('AppEntityEventNextEvent');
        $resultCache->delete('AppEntityQualifyNextEvent');
        $resultCache->delete('AppEntityRaceNextEvent');
        $resultCache->delete('AppEntityEventActualWeekendEvents');
        $resultCache->delete('AppEntityQualifyActualWeekendEvents');
        $resultCache->delete('AppEntityRaceActualWeekendEvents');
        $resultCache->delete('AppEntityEventRemain');
        $resultCache->delete('AppEntityQualifyRemain');
        $resultCache->delete('AppEntityRaceRemain');

        $this->flashBag->add('success', 'flash_cache_clear');
    }

    /**
     * @param EntityLifecycleEventInterface $event
     */
    public function calculateResults(EntityLifecycleEventInterface $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Result)) {
            return;
        }
        $this->calculator->calculate();

        $this->flashBag->add('success', 'flash_calculate');
    }

    /**
     * @param EntityLifecycleEventInterface $event
     */
    public function reCalculateResults(EntityLifecycleEventInterface $event)
    {
        $entity = $event->getEntityInstance();

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

        $this->em->getCache()->evictEntityRegions();
        $this->em->getCache()->evictCollectionRegions();

        $this->calculator->calculate();

        $this->fileCache->clearAll();

        $this->flashBag->add('success', 'flash_re_calculate');
    }
}
