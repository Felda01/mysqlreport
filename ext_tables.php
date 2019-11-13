<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

call_user_func(function() {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'system',
        'mysqlreport',
        '',
        '',
        [
            'routeTarget' => \StefanFroemken\Mysqlreport\Controller\ReportController::class . '::handleRequest',
            'access' => 'group,user',
            'name' => 'system_mysqlreport',
            'icon' => 'EXT:mysqlreport/Resources/Public/Icons/Extension.svg',
            'labels' => 'LLL:EXT:mysqlreport/Resources/Private/Language/locallang_module.xlf'
        ]
    );
});
