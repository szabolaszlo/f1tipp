<?php

namespace App\Controller\Admin;

use App\Entity\Prize;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

/**
 * A nyeremény egy sorának (helyezés => összeg) űrlapja a PrizeDrawCrudController kollekciójában.
 * Önállóan nem használható: menüben nincs, minden saját művelete tiltva.
 */
class PrizeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Prize::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::INDEX, Action::NEW, Action::EDIT, Action::DETAIL, Action::DELETE, Action::BATCH_DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('place', 'admin_prize_place')->setColumns(6),
            IntegerField::new('amount', 'admin_prize_amount')->setColumns(6),
        ];
    }
}
