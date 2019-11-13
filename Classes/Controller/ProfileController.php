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

use Psr\Http\Message\ServerRequestInterface;
use StefanFroemken\Mysqlreport\Domain\Repository\DatabaseRepository;
use StefanFroemken\Mysqlreport\Domain\Repository\ProfileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Controller to collect records with query profile information
 */
class ProfileController extends AbstractController
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
     * list action
     */
    public function listAction()
    {
        $this->buildHeaderButtons();
        $this->view->assign('profiles', $this->profileRepository->findProfilingsForCall());
    }

    /**
     * show action
     */
    public function showAction()
    {
        $this->buildHeaderButtons();
        $uniqueIdentifier = $this->request->getQueryParams()['uniqueIdentifier'] ?? '';
        $this->view->assign('profileTypes', $this->profileRepository->getProfilingByUniqueIdentifier($uniqueIdentifier));
    }

    /**
     * query type action
     */
    public function queryTypeAction()
    {
        $this->buildHeaderButtons();
        $uniqueIdentifier = $this->request->getQueryParams()['uniqueIdentifier'] ?? '';
        $queryType = $this->request->getQueryParams()['queryType'] ?? '';
        $this->view->assign('uniqueIdentifier', $uniqueIdentifier);
        $this->view->assign('queryType', $queryType);
        $this->view->assign('profilings', $this->profileRepository->getProfilingsByQueryType($uniqueIdentifier, $queryType));
    }

    /**
     * profileInfo action
     */
    public function profileInfoAction()
    {
        $this->buildHeaderButtons();
        $uniqueIdentifier = $this->request->getQueryParams()['uniqueIdentifier'] ?? '';
        $queryType = $this->request->getQueryParams()['queryType'] ?? '';
        $uid = $this->request->getQueryParams()['uid'] ?? '';

        $profiling = $this->profileRepository->getProfilingByUid($uid);
        $profiling['profile'] = unserialize($profiling['profile']);
        $profiling['explain'] = unserialize($profiling['explain_query']);

        $this->view->assign('uniqueIdentifier', $uniqueIdentifier);
        $this->view->assign('queryType', $queryType);
        $this->view->assign('profiling', $profiling);
    }

    /**
     * @param string $templateName
     */
    protected function initializeView(string $templateName)
    {
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setTemplate($templateName);
        $this->view->setTemplateRootPaths(['EXT:mysqlreport/Resources/Private/Templates/Profile']);
        $this->view->setPartialRootPaths(['EXT:mysqlreport/Resources/Private/Partials']);
        $this->view->setLayoutRootPaths(['EXT:mysqlreport/Resources/Private/Layouts']);
    }
}
