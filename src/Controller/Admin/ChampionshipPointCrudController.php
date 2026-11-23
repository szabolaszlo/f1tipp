<?php

namespace App\Controller\Admin;

use App\Entity\AlternativeChampionship;
use App\Entity\ChampionshipPoint;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Bajnoki pontozási tábla (hétvégi helyezés => bajnoki pont). Mentés / törlés után a kiszámolt hétvégék
 * újrapontozódnak (ChampionshipPointSubscriber).
 */
class ChampionshipPointCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ChampionshipPoint::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $calculatedWeekends = count($this->em->getRepository(AlternativeChampionship::class)->getCalculatedRaces());
        $help = $this->translator->trans('admin_championship_points_help', ['%count%' => $calculatedWeekends]);

        return $crud
            ->setPageTitle('index', 'admin_championship_points')
            ->setEntityLabelInSingular('admin_championship_point')
            ->setEntityLabelInPlural('admin_championship_points')
            ->setDefaultSort(['place' => 'ASC'])
            ->setPaginatorPageSize(50)
            ->setSearchFields(null)
            ->setHelp('index', $help)
            ->setHelp('new', $help)
            ->setHelp('edit', $help)
            ->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('place', 'admin_championship_point_place'),
            IntegerField::new('points', 'admin_championship_point_points'),
        ];
    }
}
