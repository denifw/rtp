<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Dashboard;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Icon;
use App\Frame\Mvc\AbstractDashboardModel;
use App\Model\DashboardItem\Table\InProgressJob;
use App\Model\DashboardItem\Table\PlanningJob;
use App\Model\DashboardItem\Widget\Inklaring\TotalExport;
use App\Model\DashboardItem\Widget\Inklaring\TotalExportContainer;
use App\Model\DashboardItem\Widget\Inklaring\TotalImport;
use App\Model\DashboardItem\Widget\Inklaring\TotalImportContainer;
use App\Model\DashboardItem\Widget\TotalJobComplete;
use App\Model\DashboardItem\Widget\TotalJobInProgress;
use App\Model\DashboardItem\Widget\TotalJobPlanning;
use App\Model\DashboardItem\Widget\TotalJobPublished;

/**
 * Class to create the view for Warehouse dashboard.
 *
 * @package    app
 * @subpackage Model\Dashboard
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class Inklaring extends AbstractDashboardModel
{

    /**
     * Property to store auto reload time.
     *
     * @var int $AutoReloadTime
     */
    protected $AutoReloadTime = 12000;

    /**
     * Property to store auto reload time.
     *
     * @var bool $EnableAutoReload
     */
    protected $EnableAutoReload = false;

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'inklaringHome');
        $this->setParameters($parameters);
    }


    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadView(): void
    {
        $url = '/inklaringHome?ar=1';
        $btnClass = 'btn btn-primary pull-right btn-sm';
        $btnIcon = Icon::Repeat;
        $btnText = Trans::getWord('startAutoReload');
        $this->EnableAutoReload = false;
        if ($this->getIntParameter('ar', 0) === 1) {
            $this->EnableAutoReload = true;
            $url = '/warehouseHome';
            $btnClass = 'btn btn-danger pull-right btn-sm';
            $btnIcon = Icon::Stop;
            $btnText = Trans::getWord('stopAutoReload');
        }
        $btn = new HyperLink('btnReload', $btnText, url($url));
        $btn->addAttribute('class', $btnClass);
        $btn->setIcon($btnIcon);
        $this->View->addButton($btn);
        if ($this->PageSetting->checkPageRight('AllowCreateNewJob') === true) {
            $btnCreateJob = new HyperLink('hplNew', Trans::getWord('createJob'), url('/jobOrder/detail'));
            $btnCreateJob->viewAsButton();
            $btnCreateJob->setIcon(Icon::Plus)->btnSuccess()->btnMedium()->pullRight();
            $this->View->addButton($btnCreateJob);
        }
        $this->loadJobWidget();
        $this->loadContainersWidget();
        $this->loadJobTable();
    }


    /**
     * Function to add job widget
     *
     * @return void
     */
    private function loadJobWidget(): void
    {
        # Planing Job
        $planningJob = new TotalJobPlanning('draftJob');
        $planningJob->addCallBackParameter('title', Trans::getWord('draftJob'));
        $planningJob->addCallBackParameter('jo_srv_id', 2);
        $planningJob->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        if ($this->EnableAutoReload) {
            $planningJob->setAutoReloadTime($this->AutoReloadTime);
        }
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $planningJob->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        }
        $this->addContent($planningJob->doCreate());
        # Published Job
        $publishedJob = new TotalJobPublished('publishedJob');
        if ($this->EnableAutoReload) {
            $publishedJob->setAutoReloadTime($this->AutoReloadTime);
        }
        $publishedJob->addCallBackParameter('title', Trans::getWord('publishedJob'));
        $publishedJob->addCallBackParameter('jo_srv_id', 2);
        $publishedJob->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $publishedJob->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } elseif ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
            $publishedJob->addCallBackParameter('jo_manager_id', $this->User->getId());
        }
        $this->addContent($publishedJob->doCreate());

        # In Progress Job
        $progressJob = new TotalJobInProgress('inProgressJob');
        if ($this->EnableAutoReload) {
            $progressJob->setAutoReloadTime($this->AutoReloadTime);
        }
        $progressJob->addCallBackParameter('title', Trans::getWord('inProgressJob'));
        $progressJob->addCallBackParameter('jo_srv_id', 2);
        $progressJob->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $progressJob->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } elseif ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
            $progressJob->addCallBackParameter('jo_manager_id', $this->User->getId());
        }
        $this->addContent($progressJob->doCreate());

        # Complete Job This Month
        $month = DateTimeParser::createDateTime();
        $completeJob = new TotalJobComplete('completeJobThisMonth');
        if ($this->EnableAutoReload) {
            $completeJob->setAutoReloadTime($this->AutoReloadTime);
        }
        $completeJob->addCallBackParameter('title', Trans::getWord('completeJobThisMonth'));
        $completeJob->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        $completeJob->addCallBackParameter('jo_srv_id', 2);
        $completeJob->addCallBackParameter('jo_start_period', $month->format('Y-m') . '-01 00:01:00');
        $completeJob->addCallBackParameter('jo_end_period', $month->format('Y-m-t') . ' 23:59:00');
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $completeJob->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } elseif ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
            $completeJob->addCallBackParameter('jo_manager_id', $this->User->getId());
        }
        $this->addContent($completeJob->doCreate());
    }

    /**
     * Function to add containers widget
     *
     * @return void
     */
    private function loadContainersWidget(): void
    {
        $month = DateTimeParser::createDateTime();
        # Total import container this month
        $totalImportContainer = new TotalImportContainer('totalImportContainer');
        if ($this->EnableAutoReload) {
            $totalImportContainer->setAutoReloadTime($this->AutoReloadTime);
        }
        $totalImportContainer->addCallBackParameter('title', Trans::getWord('totalImportContainerThisMonth') . ' (' . Trans::getWord('container') . ')');
        $totalImportContainer->addCallBackParameter('jo_start_period', $month->format('Y-m') . '-01 00:01:00');
        $totalImportContainer->addCallBackParameter('jo_end_period', $month->format('Y-m-t') . ' 23:59:00');
        $totalImportContainer->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        $totalImportContainer->addCallBackParameter('jo_srt_id', 6);
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $totalImportContainer->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } elseif ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
            $totalImportContainer->addCallBackParameter('jo_manager_id', $this->User->getId());
        }
        $this->addContent($totalImportContainer->doCreate());

        # Total export container this month
        $totalExportContainer = new TotalExportContainer('totalExportContainer');
        if ($this->EnableAutoReload) {
            $totalExportContainer->setAutoReloadTime($this->AutoReloadTime);
        }
        $totalExportContainer->addCallBackParameter('title', Trans::getWord('totalExportContainerThisMonth') . ' (' . Trans::getWord('container') . ')');
        $totalExportContainer->addCallBackParameter('jo_start_period', $month->format('Y-m') . '-01 00:01:00');
        $totalExportContainer->addCallBackParameter('jo_end_period', $month->format('Y-m-t') . ' 23:59:00');
        $totalExportContainer->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        $totalExportContainer->addCallBackParameter('jo_srt_id', 7);
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $totalExportContainer->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } elseif ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
            $totalExportContainer->addCallBackParameter('jo_manager_id', $this->User->getId());
        }
        $this->addContent($totalExportContainer->doCreate());

        # Total import lcl this month
        $totalImport = new TotalImport('totalImport');
        if ($this->EnableAutoReload) {
            $totalImport->setAutoReloadTime($this->AutoReloadTime);
        }
        $totalImport->addCallBackParameter('title', Trans::getWord('totalImportLclThisMonth') . ' (' . Trans::getWord('items') . ')');
        $totalImport->addCallBackParameter('jo_start_period', $month->format('Y-m') . '-01 00:01:00');
        $totalImport->addCallBackParameter('jo_end_period', $month->format('Y-m-t') . ' 23:59:00');
        $totalImport->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        $totalImport->addCallBackParameter('jo_srt_id', 8);
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $totalImport->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } elseif ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
            $totalImport->addCallBackParameter('jo_manager_id', $this->User->getId());
        }
        $this->addContent($totalImport->doCreate());

        # Total export lcl this month
        $totalExport = new TotalExport('totalExport');
        if ($this->EnableAutoReload) {
            $totalExport->setAutoReloadTime($this->AutoReloadTime);
        }
        $totalExport->addCallBackParameter('title', Trans::getWord('totalExportLclThisMonth') . ' (' . Trans::getWord('items') . ')');
        $totalExport->addCallBackParameter('jo_start_period', $month->format('Y-m') . '-01 00:01:00');
        $totalExport->addCallBackParameter('jo_end_period', $month->format('Y-m-t') . ' 23:59:00');
        $totalExport->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        $totalExport->addCallBackParameter('jo_srt_id', 9);
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $totalExport->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } elseif ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
            $totalExport->addCallBackParameter('jo_manager_id', $this->User->getId());
        }
        $this->addContent($totalExport->doCreate());
    }


    /**
     * Function to add job table
     *
     * @return void
     */
    private function loadJobTable(): void
    {
        # Arrive soon
        $arriveJob = new PlanningJob('DraftJobTbl');
        $arriveJob->setTitlePortlet(Trans::getWord('planningJob'));
        if ($this->EnableAutoReload) {
            $arriveJob->setAutoReloadTime($this->AutoReloadTime);
        }
        $arriveJob->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        $arriveJob->addCallBackParameter('jo_srv_id', 2);
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $arriveJob->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } elseif ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
            $arriveJob->addCallBackParameter('jo_manager_id', $this->User->getId());
        }
        $arriveJob->setGridDimension(6, 6);
        $arriveJob->setHeight(300);
        $this->addContent($arriveJob->doCreate());
        # In Progress Job
        $progressJob = new InProgressJob('InProgressJobTable');
        if ($this->EnableAutoReload) {
            $progressJob->setAutoReloadTime($this->AutoReloadTime);
        }
        $progressJob->setTitlePortlet(Trans::getWord('inProgressJob'));
        $progressJob->addCallBackParameter('jo_srv_id', 2);
        $progressJob->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $progressJob->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } elseif ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
            $progressJob->addCallBackParameter('jo_manager_id', $this->User->getId());
        }
        $progressJob->setGridDimension(6, 6);
        $progressJob->setHeight(300);
        $this->addContent($progressJob->doCreate());
    }

    public function loadData(): array
    {
        // TODO: Implement loadData() method.
    }

    protected function doInsert(): ?int
    {
        // TODO: Implement doInsert() method.
    }

    protected function doUpdate(): void
    {
        // TODO: Implement doUpdate() method.
    }

    protected function loadDefaultButton(): void
    {
        // TODO: Implement loadDefaultButton() method.
    }

    public function loadDashboardItem(): void
    {
        // TODO: Implement loadDashboardItem() method.
    }
}
