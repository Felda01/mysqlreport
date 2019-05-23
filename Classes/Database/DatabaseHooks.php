<?php
namespace StefanFroemken\Mysqlreport\Database;

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
use TYPO3\CMS\Core\Database\PostProcessQueryHookInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * @package mysqlreport
 */
class DatabaseHooks implements PostProcessQueryHookInterface, SingletonInterface
{
    /**
     * @var DatabaseConnection
     */
    protected $databaseConnection;

    /**
     * @var array
     */
    protected $extConf = [];

    /**
     * save profiles
     *
     * @var array
     */
    protected $profiles = [];

    /**
     * constructor of this class
     */
    public function __construct()
    {
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
        $this->extConf = is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mysqlreport']) ?: unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mysqlreport']);
    }

    /**
     * destructor of this class
     */
    public function __destruct()
    {
        // we done want to add additional queries to profiling table
        $this->databaseConnection->sql_query('SET profiling = 0;');

        // A page can be called multiple times each second. So we need an unique identifier.
        $uniqueIdentifier = uniqid('', true);
        $pid = is_object($GLOBALS['TSFE']) ? $GLOBALS['TSFE']->id : 0;

        // extend profiles array
        foreach ($this->profiles as $key => $profile) {
            $profile['pid'] = $pid;
            $profile['mode'] = (string)TYPO3_MODE;
            $profile['unique_call_identifier'] = $uniqueIdentifier;
            $profile['crdate'] = (int)$GLOBALS['EXEC_TIME'];
            $profile['query_id'] = $key;

            $this->profiles[$key] = $profile;
        }

        // save profilings to database
        if (!empty($this->profiles)) {
            $this->databaseConnection->exec_INSERTmultipleRows(
                'tx_mysqlreport_domain_model_profile',
                ['query_type', 'duration', 'profile', 'query', 'explain_query', 'not_using_index', 'using_fulltable', 'pid', 'mode', 'unique_call_identifier', 'crdate', 'query_id'],
                $this->profiles
            );
        }
    }

    /**
     * Post-processor for the SELECTquery method.
     *
     * @param string $select_fields Fields to be selected
     * @param string $from_table Table to select data from
     * @param string $where_clause Where clause
     * @param string $groupBy Group by statement
     * @param string $orderBy Order by statement
     * @param int $limit Database return limit
     * @param \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject
     *
     * @return void
     */
    public function exec_SELECTquery_postProcessAction(&$select_fields, &$from_table, &$where_clause, &$groupBy, &$orderBy, &$limit, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject)
    {
        // don't log profiles of this extension
        if (strpos($from_table, 'tx_mysqlreport_domain_model_profile') === false) {
            $row = [];
            // Save kind of query
            $row['query_type'] = 'SELECT';
            // save profiling information
            $this->addProfilingInformation($row);
            // build full query
            $row['query'] = $this->databaseConnection->SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);
            // save explain information
            $this->addExplainInformation($row);

            // save collected data temporary
            $this->profiles[] = $row;
        }
    }
    
    /**
     * Post-processor for the exec_INSERTquery method.
     *
     * @param string $table Database table name
     * @param array $fieldsValues Field values as key => value pairs
     * @param string|array $noQuoteFields List/array of keys NOT to quote
     * @param \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject
     *
     * @return void
     */
    public function exec_INSERTquery_postProcessAction(&$table, array &$fieldsValues, &$noQuoteFields, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject)
    {
        // don't log profiles of this extension
        if (strpos($table, 'tx_mysqlreport_domain_model_profile') === false) {
            $this->databaseConnection->setInsertedId($this->databaseConnection->getInsertedId());
            $row = [];
            // Save kind of query
            $row['query_type'] = 'INSERT';
            // save profiling information
            $this->addProfilingInformation($row);
            // build full query
            $row['query'] = $this->databaseConnection->INSERTquery($table, $fieldsValues, $noQuoteFields);
            // save explain information
            $this->addExplainInformation($row);

            // save collected data temporary
            $this->profiles[] = $row;
        }
    }

    /**
     * Post-processor for the exec_INSERTmultipleRows method.
     *
     * @param string $table Database table name
     * @param array $fields Field names
     * @param array $rows Table rows
     * @param string|array $noQuoteFields List/array of keys NOT to quote
     * @param \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject
     * @return void
     */
    public function exec_INSERTmultipleRows_postProcessAction(&$table, array &$fields, array &$rows, &$noQuoteFields, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject)
    {
        // don't log profiles of this extension
        if (strpos($table, 'tx_mysqlreport_domain_model_profile') === false) {
            $this->databaseConnection->setInsertedId($this->databaseConnection->getInsertedId());
            $row = [];
            // Save kind of query
            $row['query_type'] = 'INSERT';
            // save profiling information
            $this->addProfilingInformation($row);
            // build full query
            $row['query'] = $this->databaseConnection->INSERTmultipleRows($table, $fields, $rows, $noQuoteFields);
            // save explain information
            $this->addExplainInformation($row);

            // save collected data temporary
            $this->profiles[] = $row;
        }
    }

    /**
     * Post-processor for the exec_UPDATEquery method.
     *
     * @param string $table Database table name
     * @param string $where WHERE clause
     * @param array $fieldsValues Field values as key => value pairs
     * @param string|array $noQuoteFields List/array of keys NOT to quote
     * @param \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject
     * @return void
     */
    public function exec_UPDATEquery_postProcessAction(&$table, &$where, array &$fieldsValues, &$noQuoteFields, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject)
    {
        // don't log profiles of this extension
        if (strpos($table, 'tx_mysqlreport_domain_model_profile') === false) {
            $this->databaseConnection->setAffectedRows($this->databaseConnection->getAffectedRows());
            $row = [];
            // Save kind of query
            $row['query_type'] = 'UPDATE';
            // save profiling information
            $this->addProfilingInformation($row);
            // build full query
            $row['query'] = $this->databaseConnection->UPDATEquery($table, $where, $fieldsValues, $noQuoteFields);
            // save explain information
            $this->addExplainInformation($row);

            // save collected data temporary
            $this->profiles[] = $row;
        }
    }

    /**
     * Post-processor for the exec_DELETEquery method.
     *
     * @param string $table Database table name
     * @param string $where WHERE clause
     * @param \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject
     * @return void
     */
    public function exec_DELETEquery_postProcessAction(&$table, &$where, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject)
    {
        // don't log profiles of this extension
        if (strpos($table, 'tx_mysqlreport_domain_model_profile') === false) {
            $this->databaseConnection->setAffectedRows($this->databaseConnection->getAffectedRows());
            $row = [];
            // Save kind of query
            $row['query_type'] = 'DELETE';
            // save profiling information
            $this->addProfilingInformation($row);
            // build full query
            $row['query'] = $this->databaseConnection->DELETEquery($table, $where);
            // save explain information
            $this->addExplainInformation($row);

            // save collected data temporary
            $this->profiles[] = $row;
        }
    }

    /**
     * Post-processor for the exec_TRUNCATEquery method.
     *
     * @param string $table Database table name
     * @param \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject
     * @return void
     */
    public function exec_TRUNCATEquery_postProcessAction(&$table, \TYPO3\CMS\Core\Database\DatabaseConnection $parentObject)
    {
        // don't log profiles of this extension
        if (strpos($table, 'tx_mysqlreport_domain_model_profile') === false) {
            $row = [];
            // Save kind of query
            $row['query_type'] = 'TRUNCATE';
            // save profiling information
            $this->addProfilingInformation($row);
            // build full query
            $row['query'] = $this->databaseConnection->TRUNCATEquery($table);
            // save explain information
            $this->addExplainInformation($row);

            // save collected data temporary
            $this->profiles[] = $row;
        }
    }

    /**
     * Add profiling information
     *
     * @param array $row
     *
     * @return void
     */
    protected function addProfilingInformation(array &$row)
    {
        $profile = [];
        $duration = 0;
        // @ToDo: This line deletes last_insert_id() Maybe we should extend DatabaseConnection and replace that function
        $mysqlResult = $this->databaseConnection->sql_query('SHOW PROFILE');
        while ($profileRow = $this->databaseConnection->sql_fetch_assoc($mysqlResult)) {
            $duration += $profileRow['Duration'];
            $profile[] = $profileRow;
        }
        $row['duration'] = $duration;
        $row['profile'] = serialize($profile);
    }

    /**
     * add result of EXPLAIN to profiling
     *
     * @param array $row
     * @return void
     */
    protected function addExplainInformation(&$row)
    {
        $row['explain_query'] = serialize([]);
        $row['not_using_index'] = 0;
        $row['using_fulltable'] = 0;

        // save explain information of query if activated
        // EXPLAIN with other types than SELECT works since MySQL 5.6.3 only
        // EXPLAIN does not work, if we have PreparedStatements (Statements with ?)
        if (
            $this->extConf['addExplain'] &&
            $row['query_type'] === 'SELECT' &&
            strpos($row['query'], '?') === false
        ) {
            $explain = [];
            $notUsingIndex = false;
            $usingFullTable = false;
            $showExplain = $this->databaseConnection->sql_query('EXPLAIN ' . $row['query']);
            while ($explainRow = $this->databaseConnection->sql_fetch_assoc($showExplain)) {
                if ($notUsingIndex === false && empty($explainRow['key'])) {
                    $notUsingIndex = true;
                }
                if ($usingFullTable === false && strtolower($explainRow['select_type']) === 'all') {
                    $usingFullTable = true;
                }
                $explain[] = $explainRow;
            }
            $row['explain_query'] = serialize($explain);
            $row['not_using_index'] = (int)$notUsingIndex;
            $row['using_fulltable'] = (int)$usingFullTable;
        }
    }

}
