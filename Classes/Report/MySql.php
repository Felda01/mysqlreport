<?php
namespace StefanFroemken\Mysqlreport\Report;

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

use StefanFroemken\Mysqlreport\Domain\Model\Report;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Reports\ReportInterface;

/**
 * The mysql status report
 */
class MySql implements ReportInterface {

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var StandaloneView
     */
    protected $view;

    /**
     * @var array
     */
    protected $tableInformation = [];

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * as long as this class was NOT called over ObjectManager we have to implement properties on our own
     */
    public function __construct()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->view = $objectManager->get(StandaloneView::class);
        $this->view->setTemplatePathAndFilename('EXT:mysqlreportResources/Private/Templates/Reports/List.html');
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = $objectManager->get(PageRenderer::class);
        $pageRenderer->addCssFile('EXT:mysqlreport/Resources/Public/Css/Main.css');
        $this->objectManager = $objectManager;
    }

    /**
     * Takes care of creating / rendering the mysql status report
     *
     * @return string The status report as HTML
     */
    public function getReport()
    {
        $reports = [];
        $availableReports = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mysqlreport']['reports'];
        if (is_array($availableReports) && count($availableReports)) {
            foreach ($availableReports as $availableReport) {
                if (class_exists($availableReport)) {
                    /** @var \StefanFroemken\Mysqlreport\Reports\ReportInterface $report */
                    $availableReportObject = $this->objectManager->get($availableReport);
                    if ($availableReportObject instanceof \StefanFroemken\Mysqlreport\Reports\ReportInterface) {
                        $report = $availableReportObject->getReport();
                        if ($report instanceof Report) {
                            $reports[] = $report;
                        }
                    }
                }
            }
        }
        $this->view->assign('reports', $reports);

        return $this->view->render();
    }

    /**
     * Call this method to generate the global mysql variables var as key/value pairs
     */
    public function setTableInformation()
    {
        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('
            TABLE_NAME, table_rows, data_length, index_length',
            'information_schema.TABLES',
            'table_schema = "' . TYPO3_db . '"
        ');
        $this->tableInformation = $rows;
    }

    /**
     * getter for table information
     *
     * @return array
     */
    public function getTableInformation()
    {
        return $this->tableInformation;
    }

    /**
     * Call this method to generate a list of all columns
     */
    public function setColumns()
    {
        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            '*',
            'information_schema.COLUMNS',
            'TABLE_SCHEMA = "' . TYPO3_db . '"
        ');
        $this->columns = $rows;
    }

    /**
     * getter for table columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }
}
