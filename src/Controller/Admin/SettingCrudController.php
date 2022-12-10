<?php

namespace App\Controller\Admin;

use App\Entity\Setting;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SettingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Setting::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'admin_setting')
            ->setSearchFields(['id', 'key', 'value'])
            ->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        $key = TextField::new('key');
        $value = TextareaField::new('value');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$key, $value];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $key, $value];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$key, $value];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$key, $value];
        }
    }
}
