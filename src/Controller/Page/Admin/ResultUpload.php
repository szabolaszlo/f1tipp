<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 02.
 * Time: 19:55
 */

namespace App\Controller\Page\Admin;

use App\Controller\Controller;
use Doctrine\Common\Collections\ArrayCollection;
use Entity\Event;
use Entity\Result;
use Entity\ResultAttribute;
use System\FormHelper\FormHelper;
use System\Registry\IRegistry;

/**
 * Class Admin
 * @package App\Controller\Page\Admin
 */
class ResultUpload extends Controller
{
    /**
     * @var FormHelper
     */
    protected $formHelper;

    /**
     * @var \Entity\Event
     */
    protected $event;

    /**
     * @var \DateTime
     */
    protected $now;

    /**
     * Betting constructor.
     * @param IRegistry $registry
     */
    public function __construct(IRegistry $registry)
    {
        parent::__construct($registry);

        $this->data['formHelper'] = $this->registry->getFormHelper();
    }

    /**
     * @return mixed
     */
    public function indexAction()
    {
        if (!$this->registry->getUserAuth()->isAdmin()) {
            $this->data['error'] = $this->registry->getLanguage()->get('admin_no_permisson_or_data_error');
            return $this->render();
        }

        $this->data['token'] = md5(time());
        $this->session->set('ResultToken', $this->data['token']);

        $eventId = $this->session->get('eventId', $this->getNextEventId());

        $event = $this->getEvent($eventId);

        $this->data['attributes'] = $this->registry->getRule()->getRuleType($event->getType())->getAllAttribute();

        $this->data['event'] = $event;

        $this->data['result'] = $this->entityManager
            ->getRepository('Entity\Result')
            ->findOneBy(array('event' => $eventId));

        $this->data['success'] = $this->session->get('success');
        $this->data['error'] = $this->session->get('error');
        $this->session->remove('success');
        $this->session->remove('error');
        $this->session->remove('eventId');

        return $this->render();
    }

    /**
     * @param $eventId
     * @return Event
     */
    protected function getEvent($eventId)
    {
        /** @var Event $event */
        $event = $this->entityManager->getRepository('Entity\Event')->find($eventId);

        if (!$event) {
            $event = $this->entityManager->getRepository('Entity\Event')->find($eventId - 1);
        }

        return $event;
    }

    /**
     * @return int
     */
    protected function getNextEventId()
    {
        $results = $this->entityManager->getRepository('Entity\Result')->findBy(array(), array('id' => 'DESC'));

        /** @var Result $result */
        $result = reset($results);

        return $result ? $result->getEvent()->getId() + 1 : 1;
    }

    public function saveAction()
    {
        if (!$this->isValidPost()) {
            $this->redirectWithError();
        }

        $this->saveEntities();

        $this->session->set('success', $this->registry->getLanguage()->get('admin_result_upload_success'));
        $this->session->set('eventId', $this->request->getPost('event-id'));
        $this->registry->getServer()->redirect('page=admin/result_upload/index');
    }

    /**
     * @return bool
     */
    protected function isValidPost()
    {
        if (!$this->request->isPost()) {
            return false;
        }

        if ($this->request->getPost('token', 'notEqual') != $this->session->get('ResultToken')) {
            $this->registry->getServer()->redirect('page=admin/result_upload/index');
        }

        $this->session->remove('ResultToken');
        return true;
    }

    protected function saveEntities()
    {
        $resultAttributes = new ArrayCollection();
        $postedBetAttributes = $this->request->getPost('result_attr', array());

        $result = new Result();
        $result->setEvent($this->getEvent($this->request->getPost('event-id')));

        foreach ($postedBetAttributes as $key => $value) {
            $attribute = new ResultAttribute();
            $attribute->setResult($result);
            $attribute->setKey($key);
            $attribute->setValue($value);

            $resultAttributes->add($attribute);
        }

        $result->setAttributes($resultAttributes);

        $this->entityManager->persist($result);
        $this->entityManager->flush();
    }

    protected function redirectWithError()
    {
        $this->session->set('error', $this->registry->getLanguage()->get('admin_no_permisson_or_data_error'));
        $this->registry->getServer()->redirect('page=admin/result_upload/index');
    }
}
