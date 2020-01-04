<?php

namespace App\Controller\Admin;

use App\Form\ResultType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;

/**
 * Class ResultController
 * @package App\Controller\Admin
 */
class AdminController extends EasyAdminController
{
    // TODO: This forms have ugly design, do some pretty thing
    protected function createResultEditForm($entity, $entityProperties)
    {
        return $this->get('form.factory')->createNamedBuilder(mb_strtolower($this->entity['name']), ResultType::class, $entity)->getForm();
    }

    protected function createResultNewForm($entity, $entityProperties)
    {
        return $this->get('form.factory')->createNamedBuilder(mb_strtolower($this->entity['name']), ResultType::class)->getForm();
    }

    protected function persistResultEntity($entity, $newForm)
    {
        $result = $newForm->getData();
        $this->getDoctrine()->getManager()->persist($result);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success', 'admin_result_upload_success');
    }
}
