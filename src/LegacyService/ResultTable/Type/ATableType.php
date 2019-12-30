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
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class ATableType
 * @package App\LegacyService\ResultTable\Type
 */
abstract class ATableType implements ITableType
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var Environment
     */
    protected $renderer;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var string
     */
    protected $type = 'abstract_type';

    /**
     * @var string
     */
    protected $template = 'extension/result_table/type/abstract.html.twig';

    /**
     * ATableType constructor.
     * @param EntityManagerInterface $em
     * @param Environment $renderer
     */
    public function __construct(EntityManagerInterface $em, Environment $renderer)
    {
        $this->em = $em;
        $this->renderer = $renderer;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param Event $event
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderTable(Event $event)
    {
        $data['bets'] = $this->em->getRepository('App:Bet')->getBetsByEvent($event);

        $data['event'] = $event;

        $data['usersCount'] = count(
            $this->em->getRepository('App\Entity\User')->findAll()
        );

        $data['noBettingUsers'] = $this->getNoBettingUsers($data['bets']);

        return $this->renderer->render($this->template, $data);
    }

    /**
     * @param array $bets
     * @return array
     */
    protected function getNoBettingUsers(array $bets)
    {
        $users = $this->em->getRepository('App\Entity\User')->findAll();
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
