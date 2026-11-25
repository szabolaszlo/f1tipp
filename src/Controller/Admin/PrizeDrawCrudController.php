<?php

namespace App\Controller\Admin;

use App\Entity\PrizeDraw;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

/**
 * Nyeremény modul beállítása: egyetlen sor (PrizeDraw::ID), ezért nincs lista, új és törlés,
 * csak szerkesztés (a menüpont közvetlenül ide visz). A helyezés => összeg sorok a PrizeCrudController űrlapjával.
 */
class PrizeDrawCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PrizeDraw::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_EDIT, 'admin_prize_draw')
            ->setPageTitle(Crud::PAGE_DETAIL, 'admin_prize_draw')
            ->setEntityLabelInSingular('admin_prize_draw')
            ->setEntityLabelInPlural('admin_prize_draw')
            ->setHelp(Crud::PAGE_EDIT, 'admin_prize_draw_help')
            ->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN)
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn(Action $action) => $action->setLabel('admin_prize_draw_save'))
            ->disable(Action::INDEX, Action::NEW, Action::DELETE, Action::BATCH_DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            BooleanField::new('entryOpen', 'admin_prize_draw_entry_open')
                ->renderAsSwitch(false)
                ->setHelp('admin_prize_draw_entry_open_help'),
            TextareaField::new('note', 'admin_prize_draw_note'),
            CollectionField::new('prizes', 'admin_prize_draw_prizes')
                ->useEntryCrudForm(PrizeCrudController::class)
                ->setFormTypeOption('by_reference', false)
                ->allowAdd()
                ->allowDelete()
                ->renderExpanded(),
        ];
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);
        $this->addFlash('success', 'admin_prize_draw_saved');
    }
}
