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

use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * This model saves the mysql status
 */
class DatabaseRepository extends AbstractRepository
{
    /**
     * @var string
     */
    protected $tableName = 'tx_mysqlreport_domain_model_profile';

    /**
     * get grouped profilings grouped by unique identifier
     * and ordered by crdate descending
     *
     * @return array
     */
    public function findProfilingsForCall()
    {
        $connection = $this->getConnectionPool()->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $statement = $connection->query('
            SELECT crdate, unique_call_identifier, mode, SUM(duration) as duration, COUNT(*) as amount
            FROM tx_mysqlreport_domain_model_profile
            GROUP BY unique_call_identifier
            ORDER BY crdate DESC
            LIMIT 100;
        ');

        return $statement->fetchAll();
    }

    /**
     * get a grouped version of a profiling
     *
     * @param string $uniqueIdentifier
     * @return array
     */
    public function getProfilingByUniqueIdentifier($uniqueIdentifier)
    {
        $connection = $this->getConnectionPool()->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $statement = $connection->query('
            SELECT query_type, unique_call_identifier, SUM(duration) as duration, COUNT(*) as amount
            FROM tx_mysqlreport_domain_model_profile
            WHERE unique_call_identifier = "' . $uniqueIdentifier . '"
            GROUP BY query_type
            ORDER BY duration DESC;
        ');

        return $statement->fetchAll();
    }

    /**
     * get queries of defined query type
     *
     * @param string $uniqueIdentifier
     * @param string $queryType
     * @return array
     */
    public function getProfilingsByQueryType($uniqueIdentifier, $queryType)
    {
        $connection = $this->getConnectionPool()->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $statement = $connection->query('
            SELECT uid, query_id, LEFT(query, 120) as query, not_using_index, duration
            FROM tx_mysqlreport_domain_model_profile
            WHERE unique_call_identifier = "' . $uniqueIdentifier . '"
            AND query_type = "' . $queryType . '"
            ORDER BY duration DESC;
        ');

        return $statement->fetchAll();
    }

    /**
     * get profiling infomation by uid
     *
     * @param string $uid
     * @return array
     */
    public function getProfilingByUid($uid)
    {
        $connection = $this->getConnectionPool()->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $statement = $connection->query('
            SELECT query, query_type, profile, explain_query, not_using_index, duration
            FROM tx_mysqlreport_domain_model_profile
            WHERE uid = ' . $uid . ';
        ');

        return $statement->fetch();
    }

    /**
     * find queries using filesort
     *
     * @return array
     */
    public function findQueriesWithFilesort()
    {
        $connection = $this->getConnectionPool()->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $statement = $connection->query('
            SELECT LEFT(query, 255) as query, explain_query, duration
            FROM tx_mysqlreport_domain_model_profile
            WHERE explain_query LIKE "%using filesort%"
            ORDER BY duration DESC
            LIMIT 100;
        ');

        return $statement->fetchAll();
    }

    /**
     * find queries using full table scan
     *
     * @return array
     */
    public function findQueriesWithFullTableScan()
    {
        $connection = $this->getConnectionPool()->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $statement = $connection->query('
            SELECT LEFT(query, 255) as query, explain_query, duration
            FROM tx_mysqlreport_domain_model_profile
            WHERE using_fulltable = 1
            ORDER BY duration DESC
            LIMIT 100;
        ');

        return $statement->fetchAll();
    }
}
