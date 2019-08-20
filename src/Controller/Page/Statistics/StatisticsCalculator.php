<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 04. 02.
 * Time: 13:06
 */

namespace App\Controller\Page\Statistics;

use Entity\Bet;
use Entity\Result;

/**
 * Class StatisticsCalculator
 * @package App\Controller\Page\Statistics
 */
class StatisticsCalculator
{
    /**
     * @param array $objects
     * @return array
     */
    public function getStatistics($objects = array())
    {
        $analyzedObjects = $this->analyzeObjects($objects);
        return $this->calculateStatistics($analyzedObjects);
    }

    /**
     * @param array $objects
     * @return array
     * @throws \Exception
     */
    protected function analyzeObjects($objects = array())
    {
        $analyzedObjects = array();

        foreach ($objects as $object) {
            if ($object instanceof Bet || $object instanceof Result) {
                $attributes = $object->getAttributes();
                foreach ($attributes as $attribute) {
                    $analyzedObjects[$attribute->getKey()][] = $attribute->getValue();
                }
            } else {
                throw new \Exception('You can sort only Bet or Result Entity!');
            }
        }

        return $analyzedObjects;
    }

    /**
     * @param array $analyzedObjects
     * @return array
     */
    protected function calculateStatistics($analyzedObjects = array())
    {
        foreach ($analyzedObjects as $key => $analyzedObject) {
            $analyzedValues = array_count_values($analyzedObject);
            $sum = array_sum($analyzedValues);
            foreach ($analyzedValues as $vKey => $value) {
                $analyzedValues[$vKey] = round(($value / $sum) * 100, 2);
            }
            arsort($analyzedValues);
            $analyzedObjects[$key] = $analyzedValues;
        }

        return $analyzedObjects;
    }
}
