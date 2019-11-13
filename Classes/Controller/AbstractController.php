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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use StefanFroemken\Mysqlreport\Domain\Repository\DatabaseRepository;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * Abstract controller with usefull methods for all other controllers
 */
abstract class AbstractController
{
    /**
     * @var ModuleTemplate
     */
    protected $moduleTemplate;

    /**
     * @var ViewInterface
     */
    protected $view;

    /**
     * @var DatabaseRepository
     */
    protected $databaseRepository;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * ReportController constructor.
     *
     * @param DatabaseRepository|null $databaseRepository
     */
    public function __construct(DatabaseRepository $databaseRepository = null)
    {
        $this->databaseRepository = $databaseRepository ?? GeneralUtility::makeInstance(DatabaseRepository::class);
        $this->moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
        // $this->moduleTemplate->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Backend/Modal');
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this
            ->getLanguageService()
            ->includeLLFile('EXT:mysqlreport/Resources/Private/Language/locallang_module.xml');
    }

    /**
     * Injects the request object for the current request, and renders the overview of all redirects
     *
     * @param ServerRequestInterface $request the current request
     * @return ResponseInterface the response with the content
     */
    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $action = $request->getQueryParams()['action'] ?? 'overview';
        $this->initializeView($action);
        $result = call_user_func_array([$this, $action . 'Action'], [$request]);
        if ($result instanceof ResponseInterface) {
            return $result;
        }
        $this->moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    abstract protected function initializeView(string $action);

    /**
     * Create document header buttons
     */
    protected function buildHeaderButtons()
    {
        $this->buildHeaderMenu();
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        // Shortcut
        $mayMakeShortcut = $this->getBackendUserAuthentication()->mayMakeShortcut();
        if ($mayMakeShortcut) {
            $getVars = ['id', 'route'];

            $shortcutButton = $buttonBar->makeShortcutButton()
                ->setModuleName('site_redirects')
                ->setGetVariables($getVars);
            $buttonBar->addButton($shortcutButton, ButtonBar::BUTTON_POSITION_RIGHT);
        }
    }

    protected function buildHeaderMenu()
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $currentController = $this->request->getQueryParams()['controller'] ?? 'Report';
        $currentAction = $this->request->getQueryParams()['action'] ?? 'overview';
        $items = [
            'Report' => [
                'overview' => 'Overview',
                'queryCache' => 'Query Cache',
                'innoDbBuffer' => 'InnoDB Buffer',
                'threadCache' => 'Thread Cache',
                'tableCache' => 'Table Cache',
            ],
            'Profile' => [
                'list' => 'Profiles'
            ],
            'Query' => [
                'filesort' => 'Filesort',
                'fullTableScan' => 'Full Table Scan',
            ],
        ];
        $menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('MySqlReportJumpMenu');

        foreach ($items as $route => $actions) {
            foreach ($actions as $action => $title) {
                $menuItem = $menu
                    ->makeMenuItem()
                    ->setHref((string)$uriBuilder->buildUriFromRoute(
                        $route,
                        [
                            'action' => $action
                        ]
                    ))
                    ->setTitle($title);

                if ($route === $currentController && $action === $currentAction) {
                    $menuItem->setActive(true);
                }

                $menu->addMenuItem($menuItem);
            }
        }
        $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
