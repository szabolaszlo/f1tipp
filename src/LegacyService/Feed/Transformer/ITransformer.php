<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.27.
 * Time: 23:08
 */

namespace App\LegacyService\Feed\Transformer;


/**
 * Class XMLToArray
 * @package App\LegacyService\FeedController\Transformer
 */
interface ITransformer
{
    /**
     * @param $sourceData
     * @return mixed
     */
    public function transform($sourceData);
}