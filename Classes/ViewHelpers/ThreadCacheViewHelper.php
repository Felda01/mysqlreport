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
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to show Thread cache of MySQL
 */
class ThreadCacheViewHelper extends AbstractViewHelper
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
    }

    /**
     * analyze QueryCache parameters
     *
     * @return string
     */
    public function render()
    {
        $status = $this->arguments['status'];

        $this->templateVariableContainer->add('hitRatio', $this->getHitRatio($status));
        $content = $this->renderChildren();
        $this->templateVariableContainer->remove('hitRatio');
        return $content;
    }

    /**
     * get hit ratio of threads cache
     * A ratio nearly 100 would be cool
     *
     * @param array $status
     * @return array
     */
    protected function getHitRatio(array $status)
    {
        $result = [];
        $hitRatio = 100 - (($status['threads_created'] / $status['connections']) * 100);
        if ($hitRatio <= 80) {
            $result['status'] = 'danger';
        } elseif ($hitRatio <= 95) {
            $result['status'] = 'warning';
        } else {
            $result['status'] = 'success';
        }
        $result['value'] = round($hitRatio, 2);
        return $result;
    }
}
