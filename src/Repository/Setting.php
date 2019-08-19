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

    /**
     * @param $key
     * @param null $value
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setKeyValue($key, $value = null)
    {
        $results = parent::findAll();

        /**
         * @var  integer $key
         * @var  \Entity\Setting $setting
         */
        foreach ($results as $setting) {
            if ($setting->getKey() === $key) {
                if ($setting->getValue() !== $value) {
                    $setting->setValue($value);
                    $this->_em->persist($setting);
                    $this->_em->flush();
                }
            }
        }

        if (empty($results)) {
            $setting = new \Entity\Setting();
            $setting->setKey($key);
            $setting->setValue($value);
            $this->_em->persist($setting);
            $this->_em->flush();
        }
    }
}