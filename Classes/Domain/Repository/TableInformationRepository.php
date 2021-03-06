<?php
namespace StefanFroemken\Mysqlreport\Domain\Repository;
    
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

use StefanFroemken\Mysqlreport\Domain\Model\TableInformation;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * This model saves the mysql status
 */
class TableInformationRepository extends AbstractRepository
{
    const INNODB = 'InnoDB';
    const MYISAM = 'MyISAM';

    /**
     * get table information from information_scheme
     *
     * @return array
     */
    public function findAll()
    {
        $schemaManager = $this
            ->getConnectionPool()
            ->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME)
            ->getSchemaManager();

        $rows = [];
        $rows = $schemaManager->listTableNames();
        /*while ($row = $statement->fetch()) {
            $rows[$row['TABLE_NAME']] = $this->dataMapper->mapSingleRow(
                TableInformation::class,
                $row
            );
        }*/
        return $rows;
    }

    /**
     * get table informations of a given engine from information_scheme
     *
     * @param string $engine
     *
     * @return array
     */
    public function findAllByEngine($engine)
    {
        $rows = [];
        $res = $this->databaseConnection->sql_query('
            SELECT *
            FROM information_schema.TABLES
            WHERE table_schema = "' . TYPO3_db . '"
            AND ENGINE = "' . $engine . '";
        ');
        while ($row = $this->databaseConnection->sql_fetch_assoc($res)) {
            $rows[$row['TABLE_NAME']] = $this->dataMapper->mapSingleRow(
                TableInformation::class,
                $row
            );
        }
        return $rows;
    }

    /**
     * get table information of a given table from information_scheme
     *
     * @param string $table
     * @return TableInformation[]
     */
    public function findByTable($table)
    {
        $res = $this->databaseConnection->sql_query('
            SELECT *
            FROM information_schema.TABLES
            WHERE table_schema = "' . TYPO3_db . '"
            AND TABLE_NAME = "' . $table . '";
        ');
        $rows = [];
        while ($row = $this->databaseConnection->sql_fetch_assoc($res)) {
            $rows[$row['TABLE_NAME']] = $this->dataMapper->mapSingleRow(
                TableInformation::class,
                $row
            );
        }
        return $rows;
    }

    /**
     * get indexSize from information_scheme
     *
     * @param string $engine
     * @return array
     */
    public function getIndexSize($engine = '')
    {
        $additionalWhere = '';
        if (!empty($engine)) {
            $additionalWhere = ' AND ENGINE = "' . $engine . '"';
        }
        $res = $this->databaseConnection->sql_query('
            SELECT SUM(INDEX_LENGTH) AS indexsize
            FROM information_schema.TABLES
            WHERE table_schema = "' . TYPO3_db . '"' .
            $additionalWhere . ';
        ');
        $row = $this->databaseConnection->sql_fetch_assoc($res);
        
        return $row['indexsize'];
    }
}
