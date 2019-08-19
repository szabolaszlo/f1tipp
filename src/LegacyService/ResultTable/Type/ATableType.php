<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 01.
 * Time: 11:55
 */

namespace App\LegacyService\ResultTable\Type;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Bet;
use App\Entity\Event;
use App\Entity\User;
use App\LegacyService\Language\Language;
use App\LegacyService\Registry\IRegistry;

/**
 * Class ATableType
 * @package App\LegacyService\ResultTable\Type
 */
abstract class ATableType implements ITableType
{
    /**
     * @var IRegistry
     */
    protected $registry;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \Twig_Environment
     */
    protected $renderer;

    /**
     * @var Language
     */
    protected $language;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * OnlyUsers constructor.
     * @param IRegistry $registry
     */
    public function __construct(IRegistry $registry)
    {
        $this->registry = $registry;

        $this->entityManager = $this->registry->getEntityManager();
        $this->renderer = $this->registry->getRenderer();
        $this->data['language'] = $this->registry->getLanguage();
    }
    
    /**
     * @param Event $event
     * @return string
     */
    public function getTable(Event $event)
    {
        $this->data['bets']  = $this->entityManager
            ->getRepository('App\Entity\Bet')
            ->findBy(array('event_id' => $event));

        $this->data['event'] = $event;

        $this->data['usersCount'] = count(
            $this->entityManager->getRepository('App\Entity\User')->findAll()
        );

        $this->data['noBettingUsers'] = $this->getNoBettingUsers($this->data['bets']);

        return $this->render();
    }

    /**
     * @return string
     */
    protected function render()
    {
        $templatePath = strtolower(get_class($this)) . '.tpl';
        return $this->renderer->render($templatePath, $this->data);
    }

    /**
     * @param array $bets
     * @return array
     */
    protected function getNoBettingUsers(array $bets)
    {
        $users = $this->entityManager->getRepository('App\Entity\User')->findAll();
        $userNames = array();

        /** @var User $user */
        foreach ($users as $user) {
            $userNames[$user->getName()] = $user->getName();
        }

        /** @var Bet $bet */
        foreach ($bets as $bet) {
            /** @var User $user */
            $user = $bet->getUser();
            if (array_key_exists($user->getName(), $userNames)) {
                unset($userNames[$user->getName()]);
            }
        }
        return $userNames;
    }
}
