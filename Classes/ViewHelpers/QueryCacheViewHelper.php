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
use StefanFroemken\Mysqlreport\Domain\Model\Variables;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to show Query cache of MySQL
 */
class QueryCacheViewHelper extends AbstractViewHelper
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
        $this->registerArgument(
            'variables',
            Variables::class,
            'This argument contains all fetched variables of MySQL server',
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
        $variables = $this->arguments['variables'];

        $this->templateVariableContainer->add('hitRatio', $this->getHitRatio($status));
        $this->templateVariableContainer->add('insertRatio', $this->getInsertRatio($status));
        $this->templateVariableContainer->add('pruneRatio', $this->getPruneRatio($status));
        $this->templateVariableContainer->add('fragmentationRatio', $this->getFragmentationRatio($status));
        $this->templateVariableContainer->add('avgQuerySize', $this->getAvgQuerySize($status, $variables));
        $this->templateVariableContainer->add('avgUsedBlocks', $this->getAvgUsedBlocks($status));
        $content = $this->renderChildren();
        $this->templateVariableContainer->remove('hitRatio');
        $this->templateVariableContainer->remove('insertRatio');
        $this->templateVariableContainer->remove('pruneRatio');
        $this->templateVariableContainer->remove('fragmentationRatio');
        $this->templateVariableContainer->remove('avgQuerySize');
        $this->templateVariableContainer->remove('avgUsedBlocks');
        return $content;
    }

    /**
     * get hit ratio of query cache
     *
     * @param Status $status
     * @return array
     */
    protected function getHitRatio(Status $status)
    {
        $result = [];
        $hitRatio = ($status->getQcacheHits() / ($status->getQcacheHits() + $status->getComSelect())) * 100;
        if ($hitRatio <= 20) {
            $result['status'] = 'danger';
        } elseif ($hitRatio <= 40) {
            $result['status'] = 'warning';
        } else {
            $result['status'] = 'success';
        }
        $result['value'] = round($hitRatio, 4);
        return $result;
    }

    /**
     * get insert ratio of query cache
     *
     * @param Status $status
     * @return array
     */
    protected function getInsertRatio(Status $status)
    {
        $result = [];
        $insertRatio = ($status->getQcacheInserts() / ($status->getQcacheHits() + $status->getComSelect())) * 100;
        if ($insertRatio <= 20) {
            $result['status'] = 'success';
        } elseif ($insertRatio <= 40) {
            $result['status'] = 'warning';
        } else {
            $result['status'] = 'danger';
        }
        $result['value'] = round($insertRatio, 4);
        return $result;
    }

    /**
     * get prune ratio of query cache
     *
     * @param Status $status
     * @return array
     */
    protected function getPruneRatio(Status $status)
    {
        $result = [];
        $pruneRatio = ($status->getQcacheLowmemPrunes() / $status->getQcacheInserts()) * 100;
        if ($pruneRatio <= 10) {
            $result['status'] = 'success';
        } elseif ($pruneRatio <= 40) {
            $result['status'] = 'warning';
        } else {
            $result['status'] = 'danger';
        }
        $result['value'] = round($pruneRatio, 4);
        return $result;
    }

    /**
     * get avg query size in query cache
     *
     * @param Status $status
     * @param Variables $variables
     * @return array
     */
    protected function getAvgQuerySize(Status $status, Variables $variables)
    {
        $result = [];
        $avgQuerySize = $this->getUsedQueryCacheSize($status, $variables) / $status->getQcacheQueriesInCache();
        if ($avgQuerySize > $variables->getQueryCacheMinResUnit()) {
            $result['status'] = 'danger';
        } else {
            $result['status'] = 'success';
        }
        $result['value'] = round($avgQuerySize, 4);
        return $result;
    }

    /**
     * get used query size in bytes
     *
     * @param Status $status
     * @param Variables $variables
     * @return float
     */
    protected function getUsedQueryCacheSize(Status $status, Variables $variables)
    {
        $queryCacheSize = $variables->getQueryCacheSize() - (40 * 1024); // ~40KB are reserved by operating system
        return $queryCacheSize - $status->getQcacheFreeMemory();
    }

    /**
     * get fragmentation ratio
     *
     * @param Status $status
     * @return array
     */
    protected function getFragmentationRatio(Status $status)
    {
        $result = [];
        $fragmentation = ($status->getQcacheFreeBlocks() / ($status->getQcacheTotalBlocks() / 2)) * 100; // total blocks / 2 = maximum fragmentation
        if ($fragmentation <= 15) {
            $result['status'] = 'success';
        } elseif ($fragmentation <= 25) {
            $result['status'] = 'warning';
        } else {
            $result['status'] = 'danger';
        }
        $result['value'] = round($fragmentation, 4);
        return $result;
    }

    /**
     * get average used blocks each query
     * this can indicate if you use more small or big queries
     * Quote from link: Every cached query requires a minimum of two blocks (one for the query text and one or more for the query results).
     *
     * @link: http://dev.mysql.com/doc/refman/5.0/en/query-cache-status-and-maintenance.html
     * @param Status $status
     * @return array
     */
    protected function getAvgUsedBlocks(Status $status)
    {
        $result = [];
        $usedBlocks = $status->getQcacheTotalBlocks() - $status->getQcacheFreeBlocks();
        $minimumUsedBlocks = $status->getQcacheQueriesInCache() * 2; // see link above
        $avgUsedBlocks = $usedBlocks / $minimumUsedBlocks;
        if ($avgUsedBlocks <= 1.3) {
            $result['status'] = 'very small';
        } elseif ($avgUsedBlocks <= 2) {
            $result['status'] = 'small';
        } elseif ($avgUsedBlocks <= 5) {
            $result['status'] = 'medium';
        } else {
            $result['status'] = 'big';
        }
        $result['value'] = round($avgUsedBlocks, 4);
        return $result;
    }
}
