<?php
namespace StefanFroemken\Mysqlreport\Reports;

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

use StefanFroemken\Mysqlreport\Domain\Model\Calculation;
use StefanFroemken\Mysqlreport\Domain\Model\Report;
use StefanFroemken\Mysqlreport\Domain\Model\Status;
use StefanFroemken\Mysqlreport\Domain\Model\Variables;
use StefanFroemken\Mysqlreport\Domain\Repository\StatusRepository;
use StefanFroemken\Mysqlreport\Domain\Repository\TableInformationRepository;
use StefanFroemken\Mysqlreport\Domain\Repository\VariablesRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Analyse key_buffer
 */
abstract class AbstractReport implements ReportInterface
{
    /**
     * @var StatusRepository
     */
    protected $statusRepository;

    /**
     * @var VariablesRepository
     */
    protected $variablesRepository;

    /**
     * @var TableInformationRepository
     */
    protected $tableInformationRepository;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var Variables
     */
    protected $variables;

    public function injectStatusRepository(StatusRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }

    public function injectVariablesRepository(VariablesRepository $variablesRepository)
    {
        $this->variablesRepository = $variablesRepository;
    }

    public function injectTableInformationRepository(TableInformationRepository $tableInformationRepository)
    {
        $this->tableInformationRepository = $tableInformationRepository;
    }

    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Initializes this object
     * Fill status and variables
     */
    public function initializeObject()
    {
        $this->variables = $this->variablesRepository->findAll();
        $this->status = $this->statusRepository->findAll();
    }

    /**
     * Add calculation
     *
     * @param Report $report
     * @param string $title
     * @param string $result
     * @param string $description
     * @param int $minAllowedValue
     * @param int $maxAllowedValue
     */
    protected function addCalculation(Report $report, $title, $result, $description, $minAllowedValue, $maxAllowedValue)
    {
        /** @var Calculation $calculation */
        $calculation = $this->objectManager->get(Calculation::class);
        $calculation->setTitle($title);
        $calculation->setDescription($description);
        $calculation->setMinAllowedValue($minAllowedValue);
        $calculation->setMaxAllowedValue($maxAllowedValue);
        $calculation->setResult($result);
        $report->addCalculation($calculation);
    }

    /**
     * Format bytes
     *
     * @param $sizeInByte
     * @return string
     */
    public function formatSize($sizeInByte)
    {
        return GeneralUtility::formatSize($sizeInByte, $labels = ' Byte | KB | MB | GB');
    }
}
