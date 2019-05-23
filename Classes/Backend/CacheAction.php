<?php
namespace StefanFroemken\Mysqlreport\Backend;

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
use TYPO3\CMS\Backend\Toolbar\ClearCacheActionsHookInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class hooks into ClearCache system of TYPO3 to remove our logged records
 */
class CacheAction implements ClearCacheActionsHookInterface
{
    /**
     * Modifies CacheMenuItems array
     *
     * @param array $cacheActions Array of CacheMenuItems
     * @param array $optionValues Array of AccessConfigurations-identifiers (typically used by userTS with options.clearCache.identifier)
     */
    public function manipulateCacheActions(&$cacheActions, &$optionValues)
    {
        $cacheActions[] = [
            'id' => 'mysqlreport',
            'title' => 'LLL:EXT:mysqlreport/Resources/Private/Language/locallang.xlf:module.clearCache.title',
            'description' => 'LLL:EXT:mysqlreport/Resources/Private/Language/locallang.xlf:module.clearCache.description',
            'href' => BackendUtility::getModuleUrl('tce_db', ['cacheCmd' => 'mysqlreport']),
            'iconIdentifier' => 'actions-system-cache-clear-impact-high'
        ];
        $optionValues[] = 'mysqlreport';
    }

    /**
     * Truncate table tx_mysqlreport_domain_model_profile
     *
     * @param array $params
     */
    public function clearMySqlReports($params = [])
    {
        if ($params['cacheCmd'] === 'mysqlreport') {
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
            $connection = $connectionPool->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
            $connection->truncate('tx_mysqlreport_domain_model_profile');
        }
    }
}
