<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 15.
 * Time: 21:43
 */

namespace App\Controller\Module\TableRefresh;

use App\Controller\Controller;
use Entity\Event;

/**
 * Class TableRefresh
 * @package App\Controller\Module\TableRefresh
 */
class TableRefresh extends Controller
{

    public function isNeedRefreshEventTableAction()
    {
        $eventId = (int)$this->request->getPost('eventId', 0);

        $postedNumberOfBets = $this->request->getPost('numberOfBets', 0);

        /** @var Event $event */
        $bets = $this->entityManager->getRepository('Entity\Bet')->findBy(array('event_id' => $eventId));

        $result = $this->entityManager->getRepository('Entity\Result')->findBy(array('event' => $eventId))
            ? $this->entityManager->getRepository('Entity\Result')->findBy(array('event' => $eventId))
            : array();

        if ((count($bets) + count($result)) > $postedNumberOfBets) {
            echo true;
        }
    }

    public function isNeedRefreshResultTableAction()
    {
        $resultsCount = (int)$this->request->getPost('resultsCount', 0);

        if (count($this->entityManager->getRepository('Entity\Result')->findAll()) > $resultsCount) {
            echo true;
        }
    }

    public function isNeedRefreshTrophiesAction()
    {
        $trophyResultId = (int)$this->request->getPost('trophyResultId', 0);

        if (count($this->entityManager->getRepository('Entity\Result')->findAll()) > $trophyResultId) {
            echo true;
        }
    }
    
    /**
     * @return \System\ResultTable\Type\ITableType
     */
    public function getTableAction()
    {
        /** @var Event $event */
        $event = $this->entityManager->getRepository('Entity\Event')->find($this->request->getPost('eventId'));

        $user = $this->registry->getUserAuth()->getLoggedUser();

        return $this->registry->getResultTable()->getTable($user, $event);
    }
}
