<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 26.
 * Time: 20:49
 */

namespace App\LegacyService\ResultTable\Type;

use App\Entity\Event;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class Full
 * @package App\LegacyService\ResultTable\Type
 */
class Full extends ATableType
{
    /**
     * @var string
     */
    protected $type = 'full';

    /**
     * @var string
     */
    protected $template = 'extension/result_table/type/full.html.twig';

    /**
     * @param Event $event
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderTable(Event $event)
    {
        $data['result'] = $this->em->getRepository('App:Result')->getResultByEvent($event);

        $data['bets'] = $this->em->getRepository('App:Bet')->getBetsByEventOrderByPoints($event);

        $data['usersCount'] = count(
            $this->em->getRepository('App\Entity\User')->findAll()
        );

        $data['noBettingUsers'] = $this->getNoBettingUsers($data['bets']);

        return $this->renderer->render($this->template, $data);
    }
}
