<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 29.
 * Time: 15:58
 */

namespace App\LegacyService\ResultTable\Type;

use App\Entity\Event;

/**
 * Interface ITableType
 * @package App\LegacyService\ResultTable\Type
 */
interface ITableType
{
    /**
     * @param Event $event
     * @return string
     */
    public function renderTable(Event $event);

    /**
     * @return string
     */
    public function getType();
}
