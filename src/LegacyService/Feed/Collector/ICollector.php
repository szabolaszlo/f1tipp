<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.27.
 * Time: 23:08
 */

namespace App\LegacyService\Feed\Collector;


/**
 * Class XMLCollector
 * @package App\LegacyService\FeedController\Collector
 */
interface ICollector
{
    /**
     * @param $source
     * @return \SimpleXMLElement
     */
    public function getData($source);
}