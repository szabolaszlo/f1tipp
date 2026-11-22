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
    private const TIMEOUT_SECONDS = 15;

    // a forrás WAF-ja bizonyos UA-kat (pl. „collector”) 403-mal tilt
    private const USER_AGENT = 'Mozilla/5.0 (compatible; F1Tipp/1.0; +https://www.f1tipp.nhely.hu)';

    /**
     * @param $source
     * @return \SimpleXMLElement|false false, ha a forrás nem érhető el vagy nem érvényes XML
     */
    public function getData($source)
    {
        $previousUseErrors = libxml_use_internal_errors(true);
        $context = stream_context_create([
            'http' => [
                'timeout' => self::TIMEOUT_SECONDS,
                'user_agent' => self::USER_AGENT,
            ],
        ]);

        try {
            $content = @file_get_contents($source, false, $context);
            if ($content === false || $content === '') {
                return false;
            }

            return simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseErrors);
        }
    }
}
