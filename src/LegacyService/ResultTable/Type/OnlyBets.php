<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 01.
 * Time: 11:27
 */

namespace App\LegacyService\ResultTable\Type;

/**
 * Class OnlyBets
 * @package App\LegacyService\ResultTable\Type
 */
class OnlyBets extends ATableType
{
    /**
     * @var string
     */
    protected $type = 'only_bets';

    /**
     * @var string
     */
    protected $template = 'result_table/type/only_bets.html.twig';
}
