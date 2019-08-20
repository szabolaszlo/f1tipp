<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 20.
 * Time: 21:18
 */

namespace App\Controller\Page\Betting;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Bet;
use App\Entity\BetAttribute;
use App\Entity\Qualify;
use App\Entity\Race;
use App\Entity\Repository\Event;
use App\Entity\User;
use System\FormHelper\FormHelper;

/**
 * Class Betting
 * @package App\Controller\Page\Betting
 */
class Betting extends AbstractController
{
    /**
     * @var Qualify
     */
    protected $qualify;

    /**
     * @var array
     */
    protected $qualifyAttributes = array();

    /**
     * @var Race
     */
    protected $race;

    /**
     * @var array
     */
    protected $raceAttributes = array();

    /**
     * @var FormHelper
     */
    protected $formHelper;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var \App\Entity\Event
     */
    protected $event;

    /**
     * @var Bet
     */
    protected $qualifyBet;

    /**
     * @var Bet
     */
    protected $raceBet;

    /**
     * @var \DateTime
     */
    protected $now;

    /**
     * Betting constructor.
     * @param IRegistry $registry
     */
    public function __construct( )
    {

        //User
        $this->data['user'] = $this->user = $this->registry->getUserAuth()->getLoggedUser();

        //UserToken
        $this->data['userToken'] = $this->registry->getUserAuth()->getActualToken();

        //Qualify
        /** @var Event $repository */
        $repository = $this->entityManager->getRepository('App\Entity\Qualify');
        $this->qualify = $repository->getNextEvent();

        //QualifyAttributes
        $this->qualifyAttributes = $this->registry->getRule()->getRuleType('qualify')->getAllAttribute();

        //QualifyBet
        $this->qualifyBet = $this->entityManager
            ->getRepository('App\Entity\Bet')
            ->findOneBy(array('user_id' => $this->user, 'event_id' => $this->qualify));

        //Race
        $repository = $this->entityManager->getRepository('App\Entity\Race');
        $this->race = $repository->getNextEvent();

        //RaceAttributes
        $this->raceAttributes = $this->registry->getRule()->getRuleType('race')->getAllAttribute();

        //RaceBet
        $this->raceBet = $this->entityManager
            ->getRepository('App\Entity\Bet')
            ->findOneBy(array('user_id' => $this->user, 'event_id' => $this->race));

        //FormHelper
        $this->formHelper = $this->registry->getFormHelper();

        //Now
        $this->now = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function indexAction()
    {
        $this->data['error'] = $this->session->get('error');
        $this->session->remove('error');

        $this->data['success'] = $this->session->get('success');
        $this->session->remove('success');

        $this->data['token'] = md5(time());
        $this->session->set('BettingToken', $this->data['token']);

        $this->data['events'] = array(
            $this->getTimeDiff($this->qualify->getDateTime()) => array(
                'event' => $this->qualify,
                'eventAttributes' => $this->qualifyAttributes,
                'bet' => $this->qualifyBet,
                'inTime' => (bool)($this->now < $this->qualify->getDateTime())
            ),
            $this->getTimeDiff($this->race->getDateTime()) => array(
                'event' => $this->race,
                'eventAttributes' => $this->raceAttributes,
                'bet' => $this->raceBet,
                'inTime' => (bool)($this->now < $this->race->getDateTime())
            )
        );

        ksort($this->data['events']);

        $this->data['formHelper'] = $this->formHelper;

        return $this->render();
    }

    protected function getTimeDiff(\DateTime $time)
    {
        return abs(time() - $time->getTimestamp());
    }

    public function saveAction()
    {
        if (!$this->request->isPost()) {
            $this->redirectWithError();
        }

        if (!$this->validate()) {
            $this->redirectWithError();
        }

        $this->saveEntities();

        $this->session->set('success', $this->registry->getLanguage()->get('betting_success'));
        $this->registry->getServer()->redirect('page=betting/index');
    }

    protected function saveEntities()
    {
        $betAttributes = new ArrayCollection();
        $postedBetAttributes = $this->request->getPost('bet_attr', array());

        $bet = new Bet();
        $bet->setEvent($this->event);
        $bet->setUser($this->user);

        foreach ($postedBetAttributes as $key => $value) {
            $attribute = new BetAttribute();
            $attribute->setBet($bet);
            $attribute->setKey($key);
            $attribute->setValue($value);

            $betAttributes->add($attribute);
        }

        $bet->setAttributes($betAttributes);

        $this->entityManager->persist($bet);
        $this->entityManager->flush();
    }

    protected function redirectWithError()
    {
        $this->session->set('error', $this->registry->getLanguage()->get('betting_error'));
        $this->registry->getServer()->redirect('page=betting/index');
    }

    /**
     * @return bool
     */
    protected function validate()
    {
        $this->event = $this->entityManager->getRepository('App\Entity\Event')->find($this->request->getPost('event-id'));
        $this->user = $this->registry->getUserAuth()->getUserByToken($this->request->getPost('user-token'));

        $justInTime = (bool)($this->now < $this->event->getDateTime());

        if ($this->request->getPost('token', 'notEqual') != $this->session->get('BettingToken')) {
            $this->registry->getServer()->redirect('page=betting/index');
        }

        $this->session->remove('BettingToken');

        return (bool)($this->event && $this->user && $justInTime);
    }
}
