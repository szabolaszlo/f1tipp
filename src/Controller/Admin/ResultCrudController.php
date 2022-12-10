<?php

namespace App\Controller\Admin;

use App\Entity\Result;
use App\Form\ResultType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ResultCrudController extends AbstractCrudController
{

    public function createEditForm(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormInterface
    {
        return $this->get('form.factory')->createNamedBuilder(mb_strtolower($entityDto->getName()), ResultType::class, $entityDto->getInstance())->getForm();
    }

     public function createNewForm(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormInterface
    {
        return $this->get('form.factory')->createNamedBuilder(mb_strtolower($entityDto->getName()), ResultType::class)->getForm();
    }

    public static function getEntityFqcn(): string
    {
        return Result::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'admin_result')
            ->setSearchFields(['id'])
            ->overrideTemplate('crud/new', 'admin/result_new.html.twig')
            ->overrideTemplate('crud/edit', 'admin/result_edit.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $isCalculated = Field::new('isCalculated');
        $event = AssociationField::new('event');
        $attributes = AssociationField::new('attributes');
        $id = IntegerField::new('id', 'ID');
        $eventName = TextareaField::new('event.name', 'admin_event_name'); //->setTemplatePath('admin/event_field_text.html.twig');
        $eventDateTime = DateTimeField::new('event.date_time', 'admin_event_date');
        $eventType = TextareaField::new('event.type', 'admin_event_type'); //->setTemplatePath('admin/event_field_text.html.twig');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$eventName, $eventDateTime, $eventType];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $isCalculated, $event, $attributes];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$isCalculated, $event, $attributes];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$isCalculated, $event, $attributes];
        }
    }

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        $url = $this->container->get(AdminUrlGenerator::class)
            ->setAction(Action::EDIT)
            ->setEntityId($context->getEntity()->getPrimaryKeyValue())
            ->generateUrl();

        return $this->redirect($url);
    }
}
