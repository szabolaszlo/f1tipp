<?php

namespace App\EventSubscriber;

use App\Cache\FileCache;
use App\Calculator\ChampionshipPointRecalculator;
use App\Entity\ChampionshipPoint;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Event\EntityLifecycleEventInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * A bajnoki pontozási tábla adminos módosítása (új / átírt / törölt sor) után a kiszámolt hétvégék
 * az új tábla szerint újrapontozódnak, és a táblát vagy a bajnoki pontokat tartalmazó cache-ek ürülnek.
 */
class ChampionshipPointSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ChampionshipPointRecalculator $recalculator,
        private readonly FileCache $fileCache,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => 'rescore',
            AfterEntityUpdatedEvent::class => 'rescore',
            AfterEntityDeletedEvent::class => 'rescore',
        ];
    }

    public function rescore(EntityLifecycleEventInterface $event): void
    {
        if (!($event->getEntityInstance() instanceof ChampionshipPoint)) {
            return;
        }

        // a pontozási tábla a second-level query cache-ből jön: a régi tábla ne számoljon tovább
        $cache = $this->em->getCache();
        $cache?->evictEntityRegion(ChampionshipPoint::class);
        $cache?->evictQueryRegions();

        $count = $this->recalculator->recalculate();

        $cache?->evictEntityRegions();
        $cache?->evictCollectionRegions();
        $cache?->evictQueryRegions();
        $this->fileCache->clearAll();

        $this->requestStack->getSession()->getFlashBag()->add(
            'success',
            $this->translator->trans('flash_championship_points_recalculated', ['%count%' => $count])
        );
    }
}
