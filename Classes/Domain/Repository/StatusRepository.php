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

use StefanFroemken\Mysqlreport\Domain\Model\Status;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * This model saves the mysql status
 */
class StatusRepository extends AbstractRepository
{
    /**
     * get status from MySql
     *
     * @return Status
     */
    public function findAll()
    {
        $connection = $this->getConnectionPool()->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $statement = $connection->query('SHOW GLOBAL STATUS;');

        $rows = [];
        while ($row = $statement->fetch()) {
            $rows[strtolower($row['Variable_name'])] = $row['Value'];
        }

        /** @var Status $status */
        $status = $this->dataMapper->mapSingleRow(
            Status::class,
            $rows
        );

        return $status;
    }
}
