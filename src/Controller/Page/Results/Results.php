<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 26.
 * Time: 20:41
 */
namespace App\Controller\Page\Results;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Result;
use System\Cache\Cache;
/**
 * Class Results
 * @package App\Controller\Page\Results
 */
class Results extends AbstractController
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * Betting constructor.
     * @param IRegistry $registry
     */
    public function __construct( )
    {
        $this->cache = $this->registry->getCache();
    }

    /**
     * @return mixed
     */
    public function indexAction()
    {
        $cachedContent = $this->cache->getCache($this->getCacheId());

        if ($cachedContent) {
            return $cachedContent;
        }

        $this->data['tables'] = array();

        $results = $this->entityManager->getRepository('App\Entity\Result')->findAll();

        /** @var Result $result */
        foreach ($results as $result) {
            $this->data['tables'][] = $this->registry->getResultTable()->getTableByType('full', $result->getEvent());
        }

        $renderedContent = $this->render();

        $this->cache->setCache($this->getCacheId(), $renderedContent);

        return $renderedContent;
    }

    /**
     * @return string
     */
    protected function getCacheId()
    {
        return 'results.' . count($this->entityManager->getRepository('App\Entity\Result')->findAll());
    }
}
