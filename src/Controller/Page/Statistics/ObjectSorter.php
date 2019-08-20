<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 04. 01.
 * Time: 19:10
 */

namespace App\Controller\Page\Statistics;

use App\Entity\Bet;
use App\Entity\Result;

/**
 * Class ObjectSorter
 * @package App\Controller\Page\Statistics
 */
class ObjectSorter
{
    /**
     * @var array
     */
    protected $objects;

    /**
     * @param array $objects
     * @throws \Exception
     */
    public function addObjects($objects = array())
    {
        $this->objects = array();

        foreach ($objects as $object) {
            if ($object instanceof Bet || $object instanceof Result) {
                $type = $object->getEvent()->getType();
                $this->objects[$type][] = $object;
            } else {
                throw new \Exception('You can sort only Bet or Result Entity!');
            }
        }
    }

    /**
     * @param $type
     * @return array|mixed
     */
    public function getObjectsByType($type)
    {
        return isset($this->objects[$type])
            ? $this->objects[$type]
            : array();
    }
}
