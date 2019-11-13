<?php
declare(strict_types = 1);
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to show InnoDB buffer of MySQL
 */
class InnoDbBufferViewHelper extends AbstractViewHelper
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
            'array',
            'This argument contains all fetched status values of MySQL server',
            true
        );
        $this->registerArgument(
            'variables',
            'array',
            'This argument contains all fetched variables of MySQL server',
            true
        );
    }

    /**
     * Analyze QueryCache parameters
     *
     * @return string
     */
    public function render()
    {
        $status = $this->arguments['status'];
        $variables = $this->arguments['variables'];

        $this->templateVariableContainer->add('hitRatio', $this->getHitRatio($status));
        $this->templateVariableContainer->add('hitRatioBySF', $this->getHitRatioBySF($status));
        $this->templateVariableContainer->add('writeRatio', $this->getWriteRatio($status));
        $this->templateVariableContainer->add('load', $this->getLoad($status));
        $this->templateVariableContainer->add('logFile', $this->getLogFileSize($status, $variables));
        $this->templateVariableContainer->add('instances', $this->getInstances($variables));
        $content = $this->renderChildren();
        $this->templateVariableContainer->remove('hitRatio');
        $this->templateVariableContainer->remove('hitRatioBySF');
        $this->templateVariableContainer->remove('writeRatio');
        $this->templateVariableContainer->remove('load');
        $this->templateVariableContainer->remove('logFile');
        $this->templateVariableContainer->remove('instances');
        return $content;
    }

    /**
     * get hit ratio of innoDb Buffer
     * A ratio of 99.9 equals 1/1000
     *
     * @param array $status
     * @return array
     */
    protected function getHitRatio(array $status)
    {
        $result = [];
        $hitRatio = ($status['innodb_buffer_pool_read_requests'] / ($status['innodb_buffer_pool_read_requests'] + $status['innodb_buffer_pool_reads'])) * 100;
        if ($hitRatio <= 90) {
            $result['status'] = 'danger';
        } elseif ($hitRatio <= 99.7) {
            $result['status'] = 'warning';
        } else {
            $result['status'] = 'success';
        }
        $result['value'] = round($hitRatio, 2);
        return $result;
    }

    /**
     * get hit ratio of innoDb Buffer by SF
     *
     * @param array $status
     * @return array
     */
    protected function getHitRatioBySF(array $status)
    {
        $result = [];

        // we always want a factor of 1/1000.
        $niceToHave = $status['innodb_buffer_pool_reads'] * 1000;
        $hitRatio = 100 / $niceToHave * $status['innodb_buffer_pool_read_requests'];
        if ($hitRatio <= 70) {
            $result['status'] = 'danger';
        } elseif ($hitRatio <= 90) {
            $result['status'] = 'warning';
        } else {
            $result['status'] = 'success';
        }
        $result['value'] = round($hitRatio, 2);
        return $result;
    }

    /**
     * get write ratio of innoDb Buffer
     * A value more higher than 1 is good
     *
     * @param array $status
     * @return array
     */
    protected function getWriteRatio(array $status)
    {
        $result = [];
        $writeRatio = $status['innodb_buffer_pool_write_requests'] / $status['innodb_buffer_pool_pages_flushed'];
        if ($writeRatio <= 2) {
            $result['status'] = 'danger';
        } elseif ($writeRatio <= 7) {
            $result['status'] = 'warning';
        } else {
            $result['status'] = 'success';
        }
        $result['value'] = round($writeRatio, 2);
        return $result;
    }

    /**
     * get load of InnoDB Buffer
     *
     * @param array $status
     * @return array
     */
    protected function getLoad(array $status)
    {
        $load = [];

        // in Bytes
        $total = $status['innodb_buffer_pool_pages_total'] * $status['innodb_page_size'];
        $data = $status['innodb_buffer_pool_pages_data'] * $status['innodb_page_size'];
        $misc = $status['innodb_buffer_pool_pages_misc'] * $status['innodb_page_size'];
        $free = $status['innodb_buffer_pool_pages_free'] * $status['innodb_page_size'];

        // in MB
        $load['total'] = GeneralUtility::formatSize($total);
        $load['data'] = GeneralUtility::formatSize($data);
        $load['misc'] = GeneralUtility::formatSize($misc);
        $load['free'] = GeneralUtility::formatSize($free);

        // in percent
        $load['dataPercent'] = round(100 / $total * $data, 1);
        $load['miscPercent'] = round(100 / $total * $misc, 1);
        $load['freePercent'] = round(100 / $total * $free, 1);

        return $load;
    }

    /**
     * find a good size for log files
     *
     * @link http://www.psce.com/blog/2012/04/10/what-is-the-proper-size-of-innodb-logs/
     *
     * @param array $status
     * @param array $variables
     * @return array
     */
    protected function getLogFileSize(array $status, array $variables)
    {
        $result = [];

        $bytesWrittenEachSecond = $status['innodb_os_log_written'] / $status['uptime'];
        $bytesWrittenEachHour = $bytesWrittenEachSecond * 60 * 60;
        $sizeOfEachLogFile = (int)($bytesWrittenEachHour / $variables['innodb_log_files_in_group']);

        if ($sizeOfEachLogFile < 5242880 || $sizeOfEachLogFile < $variables['innodb_log_file_size']) {
            $result['status'] = 'success';
        } else {
            $result['status'] = 'danger';
        }
        $result['value'] = $variables['innodb_log_file_size'];
        $result['niceToHave'] = $sizeOfEachLogFile;
        return $result;
    }

    /**
     * check if instances are set correct
     *
     * @param array $variables
     * @return array
     */
    protected function getInstances(array $variables)
    {
        $result = [];
        $innodbBufferShouldBe = $variables['innodb_buffer_pool_instances'] * (1 * 1024 * 1024 * 1024); // Instances * 1 GB
        if ($variables['innodb_buffer_pool_size'] < (1 * 1024 * 1024 * 1024) && $variables['innodb_buffer_pool_instances'] === 1) {
            $result['status'] = 'success';
        }	elseif ($innodbBufferShouldBe !== $variables['innodb_buffer_pool_size']) {
                $result['status'] = 'danger';
        } else {
            $result['status'] = 'success';
        }
        $result['value'] = $variables['innodb_buffer_pool_instances'];
        return $result;
    }
}
