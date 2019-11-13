<?php
namespace StefanFroemken\Mysqlreport\Controller;

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

use StefanFroemken\Mysqlreport\Domain\Repository\DatabaseRepository;
use StefanFroemken\Mysqlreport\Domain\Repository\ProfileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Controller to collect logged queries
 */
class QueryController extends AbstractController
{
    /**
     * @var ProfileRepository
     */
    protected $profileRepository;

    /**
     * ProfileController constructor.
     *
     * @param ProfileRepository|null $profileRepository
     * @param DatabaseRepository|null $databaseRepository
     */
    public function __construct(ProfileRepository $profileRepository = null, DatabaseRepository $databaseRepository = null)
    {
        parent::__construct($databaseRepository);
        $this->profileRepository = $profileRepository ?? GeneralUtility::makeInstance(ProfileRepository::class);
    }

    /**
     * filesort action
     */
    public function filesortAction()
    {
        $this->buildHeaderButtons();
        $this->view->assign('queries', $this->profileRepository->findQueriesWithFilesort());
    }

    /**
     * using full table scan action
     */
    public function fullTableScanAction()
    {
        $this->buildHeaderButtons();
        $this->view->assign('queries', $this->profileRepository->findQueriesWithFullTableScan());
    }

    /**
     * @param string $templateName
     */
    protected function initializeView(string $templateName)
    {
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setTemplate($templateName);
        $this->view->setTemplateRootPaths(['EXT:mysqlreport/Resources/Private/Templates/Query']);
        $this->view->setPartialRootPaths(['EXT:mysqlreport/Resources/Private/Partials']);
        $this->view->setLayoutRootPaths(['EXT:mysqlreport/Resources/Private/Layouts']);
    }
}
