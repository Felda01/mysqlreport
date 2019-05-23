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
    
/**
 * Interface for mysql reports
 */
interface ReportInterface
{
    /**
     * return content to report class
     */
    public function getReport();
}
