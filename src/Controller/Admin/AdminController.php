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

    protected function updateEntity($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();

        $this->addFlash('success', 'admin_success_message');
    }

    protected function persistResultEntity($entity, $newForm)
    {
        $result = $newForm->getData();
        $this->getDoctrine()->getManager()->persist($result);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success', 'admin_result_upload_success');
    }

    protected function updateEventEntity($entity, $newForm)
    {
        $this->getDoctrine()->getManager()->persist($entity);
        $this->getDoctrine()->getManager()->flush();

        $resultCache = $this->getDoctrine()->getManager()->getConfiguration()->getResultCacheImpl();
        $resultCache->delete('App\Entity\EventNextEvent');
        $resultCache->delete('App\Entity\QualifyNextEvent');
        $resultCache->delete('App\Entity\RaceNextEvent');
        $resultCache->delete('App\Entity\EventActualWeekendEvents');
        $resultCache->delete('App\Entity\QualifyActualWeekendEvents');
        $resultCache->delete('App\Entity\RaceActualWeekendEvents');
        $resultCache->delete('App\Entity\EventRemain');
        $resultCache->delete('App\Entity\QualifyRemain');
        $resultCache->delete('App\Entity\RaceRemain');

        $this->addFlash('success', 'admin_result_upload_success');
    }
}
