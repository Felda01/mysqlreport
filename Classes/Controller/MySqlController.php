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
use StefanFroemken\Mysqlreport\Domain\Repository\StatusRepository;
use StefanFroemken\Mysqlreport\Domain\Repository\VariablesRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller to collect data from current MySQL server
 */
class MySqlController extends ActionController
{
    /**
     * @var \StefanFroemken\Mysqlreport\Domain\Repository\StatusRepository
     */
    protected $statusRepository;

    /**
     * @var \StefanFroemken\Mysqlreport\Domain\Repository\VariablesRepository
     */
    protected $variablesRepository;

    /**
     * inject statusRepository
     *
     * @param StatusRepository $statusRepository
     */
    public function injectStatusRepository(StatusRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }

    /**
     * inject variablesRepository
     *
     * @param VariablesRepository $variablesRepository
     */
    public function injectVariablesRepository(VariablesRepository $variablesRepository)
    {
        $this->variablesRepository = $variablesRepository;
    }

    /**
     * introduction page
     */
    public function indexAction()
    {
        $this->view->assign('status', $this->statusRepository->findAll());
        $this->view->assign('variables', $this->variablesRepository->findAll());
    }

    /**
     * query cache action
     */
    public function queryCacheAction()
    {
        $this->view->assign('status', $this->statusRepository->findAll());
        $this->view->assign('variables', $this->variablesRepository->findAll());
    }

    /**
     * innoDb Buffer action
     */
    public function innoDbBufferAction()
    {
        $this->view->assign('status', $this->statusRepository->findAll());
        $this->view->assign('variables', $this->variablesRepository->findAll());
    }

    /**
     * thread cache action
     */
    public function threadCacheAction()
    {
        $this->view->assign('status', $this->statusRepository->findAll());
        $this->view->assign('variables', $this->variablesRepository->findAll());
    }

    /**
     * table cache action
     */
    public function tableCacheAction()
    {
        $this->view->assign('status', $this->statusRepository->findAll());
        $this->view->assign('variables', $this->variablesRepository->findAll());
    }
}
