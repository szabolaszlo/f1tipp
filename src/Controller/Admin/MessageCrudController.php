<?php

namespace App\Controller\Admin;

use App\Entity\Message;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class MessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Message::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'admin_messages')
            ->setSearchFields(['id', 'content'])
            ->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        $content = TextareaField::new('content');
        $dateTime = DateTimeField::new('dateTime');
        $user = AssociationField::new('user');
        $id = IntegerField::new('id', 'ID');
        $userName = TextareaField::new('user.name');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $userName, $content, $dateTime];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $content, $dateTime, $user];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$content, $dateTime, $user];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$content, $dateTime, $user];
        }
    }
}
