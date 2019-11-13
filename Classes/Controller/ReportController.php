<?php
declare(strict_types = 1);
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Controller to collect data from current MySQL server
 */
class ReportController extends AbstractController
{
    public function overviewAction()
    {
        $this->buildHeaderButtons();
        $this->view->assign('status', $this->databaseRepository->getStatus());
        $this->view->assign('variables', $this->databaseRepository->getVariables());
    }

    public function queryCacheAction()
    {
        $this->buildHeaderButtons();
        $this->view->assign('status', $this->databaseRepository->getStatus());
        $this->view->assign('variables', $this->databaseRepository->getVariables());
    }

    public function innoDbBufferAction()
    {
        $this->buildHeaderButtons();
        $this->view->assign('status', $this->databaseRepository->getStatus());
        $this->view->assign('variables', $this->databaseRepository->getVariables());
    }

    public function threadCacheAction()
    {
        $this->buildHeaderButtons();
        $this->view->assign('status', $this->databaseRepository->getStatus());
        $this->view->assign('variables', $this->databaseRepository->getVariables());
    }

    public function tableCacheAction()
    {
        $this->buildHeaderButtons();
        $this->view->assign('status', $this->databaseRepository->getStatus());
        $this->view->assign('variables', $this->databaseRepository->getVariables());
    }

    /**
     * @param string $templateName
     */
    protected function initializeView(string $templateName)
    {
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setTemplate($templateName);
        $this->view->setTemplateRootPaths(['EXT:mysqlreport/Resources/Private/Templates/Report']);
        $this->view->setPartialRootPaths(['EXT:mysqlreport/Resources/Private/Partials']);
        $this->view->setLayoutRootPaths(['EXT:mysqlreport/Resources/Private/Layouts']);
    }
}
