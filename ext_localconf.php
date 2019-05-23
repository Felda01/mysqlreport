<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Add Debug Logger to Doctrine via first Hook in TYPO3
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'][] = \StefanFroemken\Mysqlreport\Hooks\RegisterDatabaseLoggerHook::class . '->addLoggerToDatabaseConnection';

// add button to clear cache of profiling table
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['additionalBackendItems']['cacheActions'][] = \StefanFroemken\Mysqlreport\Backend\CacheAction::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = \StefanFroemken\Mysqlreport\Backend\CacheAction::class . '->clearMySqlReports';
