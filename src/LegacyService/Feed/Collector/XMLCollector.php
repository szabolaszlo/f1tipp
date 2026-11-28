<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.27.
 * Time: 20:09
 */

namespace App\LegacyService\Feed\Collector;

use App\LegacyService\Feed\Fetcher\HttpFetcher;
use App\LegacyService\Feed\Fetcher\IFetcher;

/**
 * Class XMLCollector
 * @package App\LegacyService\FeedController\Collector
 */
class XMLCollector implements ICollector
{
    public function __construct(private readonly IFetcher $fetcher = new HttpFetcher())
    {
    }

    /**
     * @param $source
     * @return \SimpleXMLElement|false false, ha a forrás nem érhető el vagy nem érvényes XML
     */
    public function getData($source)
    {
        $previousUseErrors = libxml_use_internal_errors(true);

        try {
            $content = $this->fetcher->fetch($source);
            if ($content === null) {
                return false;
            }

            return simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseErrors);
        }
    }
}
