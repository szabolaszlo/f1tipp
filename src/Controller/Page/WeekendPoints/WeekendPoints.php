<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.12.28.
 * Time: 23:07
 */

namespace App\Controller\Page\WeekendPoints;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Controller\Page\WeekendPoints\ResultCollector\CollectedPointConverter\PointProvider\PointProvider;
use App\Controller\Page\WeekendPoints\ResultCollector\ResultCollector;
use App\Entity\Result;

/**
 * Class WeekendPoints
 * @package App\Controller\Page\WeekendPoints
 */
class WeekendPoints extends AbstractController
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
