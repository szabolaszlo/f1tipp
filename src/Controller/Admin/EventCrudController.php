<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW);

    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'admin_events')
            ->setSearchFields(['id', 'weekendOrder', 'name'])
            ->setPaginatorPageSize(50)
            ->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        $weekendOrder = IntegerField::new('weekendOrder', 'admin_event_weekend_order');
        $name = TextField::new('name', 'admin_event_name');
        $dateTime = DateTimeField::new('date_time', 'admin_event_date');
        $id = IntegerField::new('id', 'ID');
        $type = TextareaField::new('type', 'admin_event_type');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $weekendOrder, $name, $dateTime, $type];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $weekendOrder, $name, $dateTime];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$weekendOrder, $name, $dateTime];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$weekendOrder, $name, $dateTime];
        }
    }
}
