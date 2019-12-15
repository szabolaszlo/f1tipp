<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.27.
 * Time: 20:09
 */

namespace App\LegacyService\Feed\Collector;

/**
 * Class XMLCollector
 * @package App\LegacyService\FeedController\Collector
 */
class XMLCollector implements ICollector
{
    /**
     * @param $source
     * @return \SimpleXMLElement
     */
    public function getData($source)
    {
        return simplexml_load_file($source, 'SimpleXMLElement', LIBXML_NOCDATA);
    }
}
