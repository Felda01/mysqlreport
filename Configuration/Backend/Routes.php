<?php
/**
 * Definitions for routes provided by EXT:mysqlreport
 * Contains all "regular" routes for entry points
 */

return [
    'Report' => [
        'path' => '/mysqlReport/report',
        'target' => \StefanFroemken\Mysqlreport\Controller\ReportController::class . '::handleRequest'
    ],
    'Profile' => [
        'path' => '/mysqlReport/profile',
        'target' => \StefanFroemken\Mysqlreport\Controller\ProfileController::class . '::handleRequest'
    ],
    'Query' => [
        'path' => '/mysqlReport/query',
        'target' => \StefanFroemken\Mysqlreport\Controller\QueryController::class . '::handleRequest'
    ],
];
