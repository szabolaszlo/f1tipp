<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 21.
 * Time: 13:15
 */

namespace App\LegacyService\FormHelper\SelectOption;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ASelectOption
 * @package App\LegacyService\FormHelper\SelectOption
 */
abstract class ASelectOption implements ISelectOption
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \Twig_Environment
     */
    protected $renderer;

    /**
     * Driver constructor.
     * @param EntityManagerInterface $entityManager
     * @param \Twig_Environment $renderer
     */
    public function __construct(EntityManagerInterface $entityManager, \Twig_Environment $renderer)
    {
        $this->entityManager = $entityManager;
        $this->renderer = $renderer;
    }

    /**
     * @param null $selectedValue
     * @return mixed
     */
    abstract public function getOptions($selectedValue = null);
}
