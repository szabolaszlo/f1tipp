<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.28.
 * Time: 0:08
 */

namespace App\LegacyService\Feed\Storage;

use App\LegacyService\Feed\Item\Item;

/**
 * Class Doctrine
 * @package App\LegacyService\Feed\Storage
 */
interface IStorage
{
    /**
     * @param Item $item
     */
    public function setItem(Item $item);

    public function save();
}
