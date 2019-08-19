<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 21.
 * Time: 13:32
 */

namespace App\LegacyService\FormHelper\SelectOption;

/**
 * Class Question
 * @package App\LegacyService\FormHelper\SelectOption
 */
class Question extends ASelectOption
{
    /**
     * @var array
     */
    protected $options = array('Igen', 'Nem');

    /**
     * @param null $selectedValue
     * @return string
     */
    public function getOptions($selectedValue = null)
    {
        return $this->renderer->render(
            'system/form_helper/select_options/question.tpl',
            array(
                'options' => $this->options,
                'selectedValue' => $selectedValue
            )
        );
    }
}
