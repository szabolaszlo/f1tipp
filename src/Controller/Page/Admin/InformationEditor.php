<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 11.
 * Time: 11:14
 */

namespace App\Controller\Page\Admin;

use App\Controller\Controller;
use Entity\Information;

/**
 * Class InformationEditor
 * @package App\Controller\Page\Admin
 */
class InformationEditor extends Controller
{
    /**
     * @return mixed
     */
    public function indexAction()
    {
        if (!$this->registry->getUserAuth()->isAdmin()) {
            $this->data['error'] = $this->registry->getLanguage()->get('admin_no_permisson_or_data_error');
            return $this->render();
        }

        $this->data['informations'] = $this->entityManager
            ->getRepository('Entity\Information')
            ->findAll();

        $this->data['success'] = $this->session->get('success');
        $this->session->remove('success');

        return $this->render();
    }

    /**
     * @return string
     */
    public function updateAction()
    {
        $informationId = (int)$this->request->getQuery('information_id', 0);

        if (!$this->registry->getUserAuth()->isAdmin()) {
            $this->data['error'] = $this->registry->getLanguage()->get('admin_no_permisson_or_data_error');
            return $this->render();
        }

        if ($this->request->isPost()) {
            $this->save($informationId);
            $this->session->set('success', $this->registry->getLanguage()->get('admin_information_editor_success'));
            $this->registry->getServer()->redirect('page=admin/information_editor/index');
        }

        $this->data['information'] = $this->entityManager
            ->getRepository('Entity\Information')
            ->find($informationId);

        if (!$this->data['information']) {
            $this->data['information'] = new Information();
        }

        return $this->render();
    }

    /**
     * @return string
     */
    public function insertAction()
    {
        if (!$this->registry->getUserAuth()->isAdmin()) {
            $this->data['error'] = $this->registry->getLanguage()->get('admin_no_permisson_or_data_error');
            return $this->render();
        }

        $this->data['information'] = new Information();

        return $this->render();
    }
    
    /**
     * @return string
     */
    public function removeNewsAction()
    {
        return $this->saveNews(false);
    }

    /**
     * @return string
     */
    public function addNewsAction()
    {
        return $this->saveNews(true);
    }

    /**
     * @param $isNews
     * @return string
     */
    protected function saveNews($isNews)
    {
        $informationId = (int)$this->request->getQuery('information_id', 0);

        /** @var Information $information */
        $information = $this->entityManager
            ->getRepository('Entity\Information')
            ->find($informationId);

        if (!$this->registry->getUserAuth()->isAdmin() || !$information) {
            $this->data['error'] = $this->registry->getLanguage()->get('admin_no_permisson_or_data_error');
            return $this->render();
        }

        $information->setNews($isNews);
        $this->entityManager->persist($information);
        $this->entityManager->flush();

        $this->session->set('success', $this->registry->getLanguage()->get('admin_information_editor_success'));
        $this->registry->getServer()->redirect('page=admin/information_editor/index');
    }

    /**
     * @param $informationId
     */
    protected function save($informationId)
    {
        /** @var Information $information */
        $information = $this->entityManager
            ->getRepository('Entity\Information')
            ->find($informationId);

        if (!$information || !$informationId) {
            $information = new Information();
        }

        $information->setTitle($this->request->getPost('information_title', ''));
        $information->setContent($this->request->getPost('information_content', ''));

        $this->entityManager->persist($information);
        $this->entityManager->flush();
    }
}
