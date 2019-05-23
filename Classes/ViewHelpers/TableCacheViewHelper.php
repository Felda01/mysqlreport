<?php
namespace StefanFroemken\Mysqlreport\ViewHelpers;

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

use StefanFroemken\Mysqlreport\Domain\Model\Status;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * VIewHelper to show TableCache of MySQL
 */
class TableCacheViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize all arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument(
            'status',
            Status::class,
            'This argument contains all fetched status values of MySQL server',
            true
        );
    }

    /**
     * analyze QueryCache parameters
     *
     * @return string
     */
    public function render()
    {
        $status = $this->arguments['status'];

        $this->templateVariableContainer->add('openedTableDefsEachSecond', $this->getOpenedTableDefinitionsEachSecond($status));
        $this->templateVariableContainer->add('openedTablesEachSecond', $this->getOpenedTablesEachSecond($status));
        $content = $this->renderChildren();
        $this->templateVariableContainer->remove('openedTableDefsEachSecond');
        $this->templateVariableContainer->remove('openedTablesEachSecond');
        return $content;
    }

    /**
     * get amount of opened table definitions each second
     *
     * @param Status $status
     * @return array
     */
    protected function getOpenedTableDefinitionsEachSecond(Status $status)
    {
        $result = [];
        $openedTableDefinitions = $status->getOpenedTableDefinitions() / $status->getUptime();
        if ($openedTableDefinitions <= 0.3) {
            $result['status'] = 'success';
        } elseif ($openedTableDefinitions <= 2) {
            $result['status'] = 'warning';
        } else {
            $result['status'] = 'danger';
        }
        $result['value'] = round($openedTableDefinitions, 4);
        return $result;
    }

    /**
     * get amount of opened tables each second
     *
     * @param Status $status
     * @return array
     */
    protected function getOpenedTablesEachSecond(Status $status)
    {
        $result = [];
        $openedTables = $status->getOpenedTables() / $status->getUptime();
        if ($openedTables <= 0.6) {
            $result['status'] = 'success';
        } elseif ($openedTables <= 4) {
            $result['status'] = 'warning';
        } else {
            $result['status'] = 'danger';
        }
        $result['value'] = round($openedTables, 4);
        return $result;
    }
}
