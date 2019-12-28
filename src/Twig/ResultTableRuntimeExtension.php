<?php

namespace App\Twig;

use App\LegacyService\ResultTable\ResultTable;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Class ResultTableRuntimeExtension
 * @package App\Twig
 */
class ResultTableRuntimeExtension implements RuntimeExtensionInterface
{
    /**
     * @var ResultTable
     */
    protected $resultTable;

    /**
     * ResultTableRuntimeExtension constructor.
     * @param ResultTable $resultTable
     */
    public function __construct(ResultTable $resultTable)
    {
        $this->resultTable = $resultTable;
    }

    /**
     * @param $user
     * @param $event
     * @param null $type
     * @return string
     * @throws \Exception
     */
    public function renderResultTable($user, $event, $type = null)
    {
        return $this->resultTable->getTable($user, $event, $type)->renderTable($event);
    }
}
