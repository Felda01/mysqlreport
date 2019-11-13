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

use TYPO3\CMS\Core\Database\Connection;

/**
 * Collection of methods to get records from tx_mysqlreport_domain_model_profile
 */
class ProfileRepository extends AbstractRepository
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
    public function findProfilingsForCall(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_mysqlreport_domain_model_profile');
        $records = $queryBuilder
            ->selectLiteral('crdate, unique_call_identifier, mode, SUM(duration) as duration, COUNT(*) as amount')
            ->from('tx_mysqlreport_domain_model_profile')
            ->groupBy('unique_call_identifier')
            ->orderBy('crdate', 'DESC')
            ->setMaxResults(100)
            ->execute()
            ->fetchAll();
        if ($records === false) {
            $records = [];
        }

        return $records;
    }

    /**
     * get a grouped version of a profiling
     *
     * @param string $uniqueIdentifier
     * @return array
     */
    public function getProfilingByUniqueIdentifier(string $uniqueIdentifier): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_mysqlreport_domain_model_profile');
        $records = $queryBuilder
            ->selectLiteral('request, query_type, unique_call_identifier, SUM(duration) as duration, COUNT(*) as amount')
            ->from('tx_mysqlreport_domain_model_profile')
            ->where(
                $queryBuilder->expr()->eq(
                    'unique_call_identifier',
                    $queryBuilder->createNamedParameter($uniqueIdentifier, Connection::PARAM_STR)
                )
            )
            ->groupBy('query_type')
            ->orderBy('duration', 'DESC')
            ->execute()
            ->fetchAll();
        if ($records === false) {
            $records = [];
        }

        return $records;
    }

    /**
     * get queries of defined query type
     *
     * @param string $uniqueIdentifier
     * @param string $queryType
     * @return array
     */
    public function getProfilingsByQueryType(string $uniqueIdentifier, string $queryType): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_mysqlreport_domain_model_profile');
        $records = $queryBuilder
            ->selectLiteral('uid, query_id, LEFT(query, 120) as query, not_using_index, duration')
            ->from('tx_mysqlreport_domain_model_profile')
            ->where(
                $queryBuilder->expr()->eq(
                    'unique_call_identifier',
                    $queryBuilder->createNamedParameter($uniqueIdentifier, Connection::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'query_type',
                    $queryBuilder->createNamedParameter($queryType, Connection::PARAM_STR)
                )
            )
            ->execute()
            ->fetchAll();
        if ($records === false) {
            $records = [];
        }

        return $records;
    }

    /**
     * get profiling information by UID
     *
     * @param int $uid
     * @return array
     */
    public function getProfilingByUid(int $uid): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_mysqlreport_domain_model_profile');
        $record = $queryBuilder
            ->selectLiteral('query, query_type, profile, explain_query, not_using_index, duration')
            ->from('tx_mysqlreport_domain_model_profile')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        if ($record === false) {
            $record = [];
        }

        return $record;
    }

    /**
     * find queries using FileSort
     *
     * @return array
     */
    public function findQueriesWithFilesort(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_mysqlreport_domain_model_profile');
        $records = $queryBuilder
            ->selectLiteral('LEFT(query, 255) as query, explain_query, duration')
            ->from('tx_mysqlreport_domain_model_profile')
            ->where(
                $queryBuilder->expr()->like(
                    'explain_query',
                    $queryBuilder->createNamedParameter('%using filesort%', Connection::PARAM_STR)
                )
            )
            ->orderBy('duration', 'DESC')
            ->setMaxResults(100)
            ->execute()
            ->fetchAll();
        if ($records === false) {
            $records = [];
        }

        return $records;
    }

    /**
     * find queries using full table scan
     *
     * @return array
     */
    public function findQueriesWithFullTableScan(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_mysqlreport_domain_model_profile');
        $records = $queryBuilder
            ->selectLiteral('LEFT(query, 255) as query, explain_query, duration')
            ->from('tx_mysqlreport_domain_model_profile')
            ->where(
                $queryBuilder->expr()->like(
                    'using_fulltable',
                    $queryBuilder->createNamedParameter(1, Connection::PARAM_INT)
                )
            )
            ->orderBy('duration', 'DESC')
            ->setMaxResults(100)
            ->execute()
            ->fetchAll();
        if ($records === false) {
            $records = [];
        }

        return $records;
    }
}
