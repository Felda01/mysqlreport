<?php
namespace StefanFroemken\Mysqlreport\Database;

/*
 * This file is part of the TYPO3 CMS project.
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
 * Class DatabaseConnection
 *
 * @package StefanFroemken\Core\Database
 */
class DatabaseConnection extends \TYPO3\CMS\Core\Database\DatabaseConnection
{
    /**
     * @var int
     */
    protected $insertedId;
    
    /**
     * @var int
     */
    protected $affectedRows;
    
    /**
     * @return int
     */
    public function getInsertedId()
    {
        return $this->link->insert_id;
    }
    
    /**
     * @param int $insertedId
     */
    public function setInsertedId($insertedId)
    {
        $this->insertedId = (int)$insertedId;
    }
    
    /**
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->link->affected_rows;
    }
    
    /**
     * @param int $affectedRows
     */
    public function setAffectedRows($affectedRows)
    {
        $this->affectedRows = (int)$affectedRows;
    }
    
    /**
     * Get the ID generated from the previous INSERT operation
     *
     * @return int The uid of the last inserted record.
     */
    public function sql_insert_id()
    {
        $insertId = $this->insertedId;
        $this->insertedId = null;
        return $insertId;
    }

    /**
     * Returns the number of rows affected by the last INSERT, UPDATE or DELETE query
     *
     * @return int Number of rows affected by last query
     */
    public function sql_affected_rows()
    {
        $affectedRows = $this->affectedRows;
        $this->affectedRows = null;
        return $affectedRows;
    }
}
