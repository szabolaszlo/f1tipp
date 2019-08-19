<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 26.
 * Time: 20:49
 */

namespace App\LegacyService\ResultTable\Type;

use App\Entity\Bet;
use App\Entity\Event;
use App\LegacyService\Calculator\ICalculator;
use App\LegacyService\Registry\IRegistry;
use App\LegacyService\ResultTable\Decorator\IDecorator;

/**
 * Class Full
 * @package App\LegacyService\ResultTable\Type
 */
class Full extends ATableType
{
    /**
     * @var ICalculator
     */
    protected $calculator;

    /**
     * @var IDecorator
     */
    protected $decorator;
    
    /**
     * Full constructor.
     * @param IRegistry $registry
     * @param ICalculator $calculator
     * @param IDecorator $decorator
     */
    public function __construct(IRegistry $registry, ICalculator $calculator, IDecorator $decorator)
    {
        parent::__construct($registry);
        
        $this->registry = $registry;
        $this->calculator = $calculator;
        $this->decorator = $decorator;

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
        $this->data['result'] = $this->entityManager
            ->getRepository('App\Entity\Result')
            ->findOneBy(array('event' => $event));

        $bets = $this->entityManager
            ->getRepository('App\Entity\Bet')
            ->findBy(array('event_id' => $event));

        $sortMap = array();

        /** @var Bet $bet */
        foreach ($bets as $bet) {
            $this->calculator->calculateBetPoints($bet, $this->data['result']);
            $this->decorator->decorate($bet);
            $sortMap[] = $bet->getPoint();
        }

        array_multisort($sortMap, SORT_DESC, $bets, SORT_DESC);

        $this->data['bets'] = $bets;

        $this->data['usersCount'] = count(
            $this->entityManager->getRepository('App\Entity\User')->findAll()
        );

        $this->data['noBettingUsers'] = $this->getNoBettingUsers($bets);

        return $this->render();
    }
}
