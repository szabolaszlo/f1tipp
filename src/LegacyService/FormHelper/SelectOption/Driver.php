<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 21.
 * Time: 13:15
 */

namespace App\LegacyService\FormHelper\SelectOption;

/**
 * Class Driver
 * @package App\LegacyService\FormHelper\SelectOption
 */
class Driver extends ASelectOption
{
    /**
     * @param null $selectedValue
     * @return string
     */
    public function getOptions($selectedValue = null)
    {
        $drivers = $this->entityManager
            ->getRepository('App\Entity\Driver')
            ->findBy(array('status' => '1'), array('point' => 'DESC'));

        return $this->renderer->render(
            'system/form_helper/select_options/driver.tpl',
            array(
                'drivers' => $drivers,
                'selectedValue' => $selectedValue
            )
        );
    }
}
