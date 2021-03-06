<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 31.
 * Time: 21:18
 */

namespace Controller\Module\Trophies;

use Controller\Controller;
use Entity\Result;
use Entity\Trophy;
use System\Lock\Lock;

/**
 * Class Trophies
 * @package Controller\Module\Trophies
 */
class Trophies extends Controller
{
    /**
     * @return mixed
     */
    public function indexAction()
    {
        $locker = new Lock();

        if ($locker->isLocked($this->id)) {
            $this->data['processing'] = true;
            return $this->render();
        }

        $cache = $this->registry->getCache();

        $cachedContent = $cache->getCache($this->getCacheId());

        if ($cachedContent) {
            return $cachedContent;
        }

        $locker->lock($this->id);

        $this->registry->getTrophyHandler()->collect();

        $results = $this->entityManager->getRepository('Entity\Result')->findByType('race');

        /** @var Result $result */
        $result = array_pop($results);

        if (!$result) {
            $locker->unlock($this->id);
            return false;
        }

        $trophies = $this->entityManager->getRepository('Entity\Trophy')->findBy(
            array('event' => $result->getEvent())
        );

        /** @var Trophy $trophy */
        foreach ($trophies as $trophy) {
            $this->data['podiumTrophies'][$trophy->getType()][] = $trophy;
        }

        $this->data['title'] = $result->getEvent()->getName();

        $this->data['eventId'] = $result->getEvent()->getId();

        $this->data['detailsLink'] = '/?page=trophies/index';

        $renderedContent = $this->render();

        $cache->setCache($this->getCacheId(), $renderedContent);

        $locker->unlock($this->id);

        return $renderedContent;
    }

    /**
     * @return string
     */
    protected function getCacheId()
    {
        return
            'trophies.'
            . count($this->entityManager->getRepository(Result::class)->findByType('race')) . '.'
            . $this->data['visibility'];
    }
}
