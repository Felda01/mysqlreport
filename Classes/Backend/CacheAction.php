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
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class hooks into ClearCache system of TYPO3 to remove our logged records
 */
class CacheAction implements ClearCacheActionsHookInterface
{
    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $databaseConnection;

    /**
     * constructor of this class
     */
    public function __construct()
    {
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
    }

    /**
     * Modifies CacheMenuItems array
     *
     * @param array $cacheActions Array of CacheMenuItems
     * @param array $optionValues Array of AccessConfigurations-identifiers (typically used by userTS with options.clearCache.identifier)
     *
     * @return void
     */
    public function manipulateCacheActions(&$cacheActions, &$optionValues)
    {
        $cacheAction = [
            'id' => 'mysqlprofiles',
            'title' => 'Clear MySQL Profiles',
            'description' => 'Clear collected profile records of extension mysqlreport. This table can grow very fast, so maybe it is good to clear this table.',
        ];
        if (GeneralUtility::compat_version('7.6')) {
            /** @var IconFactory $iconFactory */
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
            $cacheAction['icon'] = $iconFactory->getIcon('actions-system-cache-clear-impact-high');
            $cacheAction['href'] = BackendUtility::getModuleUrl('tce_db', [
                'vC' => $this->getBackendUser()->veriCode(),
                'cacheCmd' => 'mysqlprofiles',
                'ajaxCall' => 1
            ]);
        } else {
            $cacheAction['icon'] = IconUtility::getSpriteIcon('actions-system-cache-clear-impact-high');
            $cacheAction['href'] = 'tce_db.php?vC=' . $this->getBackendUser()->veriCode() . '&cacheCmd=mysqlprofiles&ajaxCall=1' . BackendUtility::getUrlToken('tceAction');
        }
        $cacheActions[] = $cacheAction;
        $optionValues[] = 'mysqlprofiles';
    }

    /**
     * Returns the current BE user.
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * truncate table tx_mysqlreport_domain_model_profile
     *
     * @param array $params
     * @return void
     */
    public function clearProfiles($params = [])
    {
        if ($params['cacheCmd'] === 'mysqlprofiles') {
            $this->databaseConnection->exec_TRUNCATEquery('tx_mysqlreport_domain_model_profile');
        }
    }
}
