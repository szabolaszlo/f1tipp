<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.12.28.
 * Time: 23:07
 */

namespace Controller\Page\WeekendPoints;

use Controller\Controller;
use Controller\Page\WeekendPoints\ResultCollector\CollectedPointConverter\PointProvider\PointProvider;
use Controller\Page\WeekendPoints\ResultCollector\ResultCollector;
use Entity\Result;

/**
 * Class WeekendPoints
 * @package Controller\Page\WeekendPoints
 */
class WeekendPoints extends Controller
{
    /**
     * @return string
     */
    public function indexAction()
    {
        $cachedContent = $this->registry->getCache()->getCache($this->getCacheId());

        if ($cachedContent) {
            return $cachedContent;
        }

        $collector = new ResultCollector($this->entityManager, $this->registry->getCalculator());

        $collector->collect();

        $this->data['usersPoints'] = $collector->getPoints();

        $this->data['weekends'] = $collector->getWeekends();

        $this->data['pointProvider'] = new PointProvider();

        $renderedContent = $this->render();

        $this->registry->getCache()->setCache($this->getCacheId(), $renderedContent);

        return $renderedContent;
    }

    /**
     * @return string
     */
    protected function getCacheId()
    {
        return $this->id . count($this->entityManager->getRepository(Result::class)->findByType('race'));
    }
}
