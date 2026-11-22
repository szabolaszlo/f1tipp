<?php

namespace App\EventSubscriber;

use App\Cache\ResultCache;
use App\Cache\FileCache;
use App\Calculator\Calculator;
use App\Entity\Event;
use App\Entity\Result;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Event\EntityLifecycleEventInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
     * @var RequestStack
     */
    protected RequestStack $requestStack;

    /**
     * EasyAdminSubscriber constructor.
     * @param Calculator $calculator
     * @param EntityManagerInterface $em
     * @param FileCache $fileCache
     * @param RequestStack $requestStack
     */
    public function __construct(Calculator $calculator, EntityManagerInterface $em, FileCache $fileCache, RequestStack $requestStack)
    {
        $this->calculator = $calculator;
        $this->em = $em;
        $this->fileCache = $fileCache;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            // új vagy törölt esemény is megváltoztatja a "következő esemény" / aktuális hétvége cache-t
            AfterEntityPersistedEvent::class => [['calculateResults'], ['clearEventCaches'], ['clearUserCaches']],
            AfterEntityUpdatedEvent::class => [['reCalculateResults'], ['clearEventCaches'], ['clearUserCaches']],
            AfterEntityDeletedEvent::class => [['reCalculateResults'], ['clearEventCaches'], ['clearUserCaches']],
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

        $resultCache = ResultCache::of($this->em);
        $resultCache->delete('AppEntityEventNextEvent');
        $resultCache->delete('AppEntityQualifyNextEvent');
        $resultCache->delete('AppEntityRaceNextEvent');
        $resultCache->delete('AppEntityEventActualWeekendEvents');
        $resultCache->delete('AppEntityQualifyActualWeekendEvents');
        $resultCache->delete('AppEntityRaceActualWeekendEvents');
        $resultCache->delete('AppEntityEventRemain');
        $resultCache->delete('AppEntityQualifyRemain');
        $resultCache->delete('AppEntityRaceRemain');
        $resultCache->delete('userChampionshipTrophyAwareOrder');

        $this->addFlash('success', 'flash_cache_clear');
    }

    /**
     * Az oldalsáv VB tabellájának sorrendje (UserChampionshipOrderResolver) cache-elt user id lista:
     * új / átírt / törölt user (pl. bajnokság tagja lett) csak ürítés után jelenik meg benne.
     */
    public function clearUserCaches(EntityLifecycleEventInterface $event): void
    {
        if (!($event->getEntityInstance() instanceof User)) {
            return;
        }

        ResultCache::of($this->em)->delete('userChampionshipTrophyAwareOrder');
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

        $resultCache = ResultCache::of($this->em);
        $resultCache->delete('userChampionshipTrophyAwareOrder');
        $resultCache->delete('AppEntityEventRemain');
        $resultCache->delete('AppEntityQualifyRemain');
        $resultCache->delete('AppEntityRaceRemain');

        $this->addFlash('success', 'flash_calculate');
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

        $this->em->getRepository(\App\Entity\Bet::class)->clearBetPointsByEvent($event);
        $this->em->getRepository(\App\Entity\BetAttribute::class)->clearBetAttributePointsByEvent($event);
        $weekendEvents = $this->em->getRepository(\App\Entity\Event::class)->getWeekendEvents($event);
        $this->em->getRepository(\App\Entity\Trophy::class)->removeTrophiesByEvents($weekendEvents);
        $this->em->getRepository(\App\Entity\AlternativeChampionship::class)->removeAlterChampsByEvents($weekendEvents);

        $this->em->flush();

        $this->em->getCache()->evictEntityRegions();
        $this->em->getCache()->evictCollectionRegions();

        $this->calculator->calculate();

        $this->fileCache->clearAll();

        $resultCache = ResultCache::of($this->em);
        $resultCache->delete('userChampionshipTrophyAwareOrder');

        $this->addFlash('success', 'flash_re_calculate');
    }

    private function addFlash(string $type, string $message): void
    {
        $this->requestStack->getSession()->getFlashBag()->add($type, $message);
    }
}
