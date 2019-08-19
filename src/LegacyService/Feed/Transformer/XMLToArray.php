<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.27.
 * Time: 23:06
 */

namespace App\LegacyService\Feed\Transformer;

/**
 * Class XMLToArray
 * @package App\LegacyService\Feed\Transformer
 */
class XMLToArray implements ITransformer
{
    /**
     * @param $sourceData
     * @return mixed
     */
    public function transform($sourceData)
    {
        $json = json_encode($sourceData);
        return json_decode($json,TRUE);
    }
}
