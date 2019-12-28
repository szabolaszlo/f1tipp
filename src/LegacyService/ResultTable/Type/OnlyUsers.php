<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 01.
 * Time: 11:29
 */

namespace App\LegacyService\ResultTable\Type;

/**
 * Class OnlyUsers
 * @package App\LegacyService\ResultTable\Type
 */
class OnlyUsers extends ATableType
{
    /**
     * @var string
     */
    protected $type = 'only_users';

    /**
     * @var string
     */
    protected $template = 'result_table/type/only_users.html.twig';
}
