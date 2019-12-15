<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.27.
 * Time: 19:57
 */

namespace App\LegacyService\Feed;

use App\LegacyService\Feed\Repository\IRepository;
use App\LegacyService\Feed\Storage\IStorage;

/**
 * Class Handler
 * @package App\LegacyService\FeedController
 */
class Handler
{
    /**
     * @var array
     */
    protected $repositories = array();

    /**
     * @var IStorage
     */
    protected $storage;

    /**
     * Handler constructor.
     * @param array $repositories
     * @param IStorage $storage
     */
    public function __construct(array $repositories, IStorage $storage)
    {
        $this->repositories = $repositories;
        $this->storage = $storage;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $items = array();

        /** @var IRepository $repository */
        foreach ($this->repositories as $repository) {
            $items = array_merge($items, $repository->getItems());
        }

        return $items;
    }

    public function saveItems(array $items)
    {
        foreach ($items as $item) {
            $this->storage->setItem($item);
        }

        $this->storage->save();
    }
}
