<?php

namespace App\LegacyService\ResultTable;

use App\LegacyService\ResultTable\Type\ITableType;

/**
 * Class ResultTableRegistry
 * @package App\LegacyService\ResultTable
 */
class ResultTableRegistry
{
    /**
     * @var array
     */
    protected $tables = [];

    /**
     * @param ITableType $tableType
     */
    public function addTable(ITableType $tableType)
    {
        $this->tables[$tableType->getType()] = $tableType;
    }

    /**
     * @param $type
     * @return mixed
     * @throws \Exception
     */
    public function getTableByType($type)
    {
        if (!isset($this->tables[$type])) {
            throw new \Exception('This table type not exist: ' . $type);
        }

        return $this->tables[$type];
    }
}