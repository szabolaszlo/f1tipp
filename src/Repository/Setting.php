<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class Setting
 * @package App\Repository
 */
class Setting extends EntityRepository
{
    /**
     * @param string $key
     * @return \Entity\Setting|null
     */
    public function getValueByKey($key = '')
    {
        $results = parent::findAll();

        /**
         * @var  integer $key
         * @var  \Entity\Setting $setting
         */
        foreach ($results as $setting) {
            if ($setting->getKey() === $key) {
                return $setting->getValue();
            }
        }

        return null;
    }
}