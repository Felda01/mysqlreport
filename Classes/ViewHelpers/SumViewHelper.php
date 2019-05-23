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
 * With this ViewHelper you can sum various values
 */
class SumViewHelper extends AbstractViewHelper
{
    /**
     * Initialize all arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument(
            'profiles',
            'array',
            'Profile records to sum',
            true
        );
        $this->registerArgument(
            'field',
            'string',
            'The field of the profiles array to sum',
            false,
            'summed_duration'
        );
    }

    /**
     * Sum a field of profile records
     *
     * @return string
     */
    public function render()
    {
        $sum = 0;
        foreach ($this->arguments['profiles'] as $profile) {
            $sum += $profile[$this->arguments['field']];
        }

        return $sum;
    }
}
