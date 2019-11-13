<?php
declare(strict_types = 1);
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

use StefanFroemken\Mysqlreport\Domain\Model\Variables;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Collection of methods to get data like status and variables directly from database
 */
class DatabaseRepository extends AbstractRepository
{
    /**
     * Get status from MySQL
     *
     * @return array
     */
    public function getStatus(): array
    {
        $connection = $this
            ->getConnectionPool()
            ->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $statement = $connection->query('SHOW GLOBAL STATUS;');

        $records = [];
        while ($row = $statement->fetch()) {
            $records[strtolower($row['Variable_name'])] = $row['Value'];
        }

        return $records;
    }

    /**
     * Get variables from MySQL
     *
     * @return array
     */
    public function getVariables()
    {
        $connection = $this
            ->getConnectionPool()
            ->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $statement = $connection->query('SHOW GLOBAL VARIABLES;');

        $records = [];
        while ($row = $statement->fetch()) {
            $records[strtolower($row['Variable_name'])] = $row['Value'];
        }

        return $records;
    }
}
