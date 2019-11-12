<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

call_user_func(function($extKey) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'StefanFroemken.' . $extKey,
        'system', // Make module a submodule of 'web'
        'mysql', // Submodule key
        '', // Position
        [
            'MySql' => 'index, queryCache, innoDbBuffer, threadCache, tableCache, report',
            'Profile' => 'list, show, queryType, profileInfo',
            'Query' => 'filesort, fullTableScan',
        ],
        [
            'access' => 'user,group',
            'icon'   => 'EXT:' . $extKey . '/ext_icon.svg',
            'labels' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_report.xlf',
        ]
    );
}, $_EXTKEY);
