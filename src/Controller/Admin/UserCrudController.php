<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'admin_menu_users')
            ->setSearchFields(['id', 'name', 'username', 'roles', 'timestamp', 'pointSummary', 'pointDifference', 'alternativePointSummary', 'alternativePointDifference'])
            ->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $username = TextField::new('username');
        $roles = ArrayField::new('roles');
        $password = TextField::new('password');
        $isAlterChamps = Field::new('isAlterChamps');
        $id = IntegerField::new('id', 'ID');
        $timestamp = IntegerField::new('timestamp');
        $pointSummary = IntegerField::new('pointSummary');
        $pointDifference = IntegerField::new('pointDifference');
        $alternativePointSummary = IntegerField::new('alternativePointSummary');
        $alternativePointDifference = IntegerField::new('alternativePointDifference');
        $trophies = AssociationField::new('trophies');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$username, $roles, $isAlterChamps];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $username, $roles, $password, $timestamp, $isAlterChamps, $pointSummary, $pointDifference, $alternativePointSummary, $alternativePointDifference, $trophies];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $username, $roles, $password, $isAlterChamps];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $username, $roles, $password, $isAlterChamps];
        }
    }
}
