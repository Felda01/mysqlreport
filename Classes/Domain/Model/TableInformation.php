<?php
declare(strict_types = 1);
namespace StefanFroemken\Mysqlreport\Domain\Model;

/*
 * This file is part of the mysqlreport project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * This model saves the mysql status
 */
class TableInformation
{
    /**
     * @var string
     */
    protected $tableName = '';

    /**
     * @var string
     */
    protected $engine = '';

    /**
     * @var int
     */
    protected $tableRows = 0;

    /**
     * @var int
     */
    protected $avgRowLength = 0;

    /**
     * @var int
     */
    protected $dataLength = 0;

    /**
     * @var int
     */
    protected $indexLength = 0;

    /**
     * @var int
     */
    protected $dataFree = 0;

    /**
     * @var int
     */
    protected $autoIncrement = 0;

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName)
    {
        $this->tableName = $tableName;
    }

    public function getEngine(): string
    {
        return $this->engine;
    }

    public function setEngine(string $engine)
    {
        $this->engine = $engine;
    }

    public function getTableRows(): int
    {
        return $this->tableRows;
    }

    public function setTableRows(int $tableRows)
    {
        $this->tableRows = $tableRows;
    }

    public function getAvgRowLength(): int
    {
        return $this->avgRowLength;
    }

    public function setAvgRowLength(int $avgRowLength)
    {
        $this->avgRowLength = $avgRowLength;
    }

    public function getDataLength(): int
    {
        return $this->dataLength;
    }

    public function setDataLength(int $dataLength)
    {
        $this->dataLength = $dataLength;
    }

    public function getIndexLength(): int
    {
        return $this->indexLength;
    }

    public function setIndexLength(int $indexLength)
    {
        $this->indexLength = $indexLength;
    }

    public function getDataFree(): int
    {
        return $this->dataFree;
    }

    public function setDataFree(int $dataFree)
    {
        $this->dataFree = $dataFree;
    }

    public function getAutoIncrement(): int
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement(int $autoIncrement)
    {
        $this->autoIncrement = $autoIncrement;
    }
}
