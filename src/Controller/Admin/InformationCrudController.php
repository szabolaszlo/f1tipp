<?php

namespace App\Controller\Admin;

use App\Entity\Information;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class InformationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Information::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'admin_information')
            ->setSearchFields(['id', 'title', 'content', 'slug'])
            ->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        $title = TextField::new('title');
        $content = TextareaField::new('content');
        $slug = TextField::new('slug');
        $news = Field::new('news');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$title, $slug];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $title, $content, $news, $slug];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$title, $content, $slug, $news];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$title, $content, $slug, $news];
        }
    }
}
