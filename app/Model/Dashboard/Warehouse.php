<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dashboard;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Icon;
use App\Frame\Mvc\AbstractDashboardModel;
use App\Model\DashboardItem\Table\InProgressJob;
use App\Model\DashboardItem\Table\PlanningJob;
use App\Model\DashboardItem\Widget\TotalJobComplete;
use App\Model\DashboardItem\Widget\TotalJobInProgress;
use App\Model\DashboardItem\Widget\TotalJobPlanning;
use App\Model\DashboardItem\Widget\TotalJobPublished;
use App\Model\DashboardItem\Widget\Warehouse\TotalDamageItem;
use App\Model\DashboardItem\Widget\Warehouse\TotalGoodItem;
use App\Model\DashboardItem\Widget\Warehouse\TotalInboundItem;
use App\Model\DashboardItem\Widget\Warehouse\TotalOutboundItem;

/**
 * Class to create the view for Warehouse dashboard.
 *
 * @package    app
 * @subpackage Model\Dashboard
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Warehouse extends AbstractDashboardModel
{

    /**
     * Property to store auto reload time.
     *
     * @var int $AutoReloadTime
     */
    protected  $AutoReloadTime = 12000;

    /**
     * Property to store auto reload time.
     *
     * @var bool $EnableAutoReload
     */
    protected  $EnableAutoReload = false;
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'warehouseHome');
        $this->setParameters($parameters);
    }


    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadView(): void
    {
        $url = '/warehouseHome?ar=1';
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
        $this->loadGoodsWidget();
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
        $planningJob->addCallBackParameter('jo_srv_id', 1);
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
        $publishedJob->addCallBackParameter('jo_srv_id', 1);
        $publishedJob->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $publishedJob->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } else {
            if ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
                $publishedJob->addCallBackParameter('jo_manager_id', $this->User->getId());
            }
        }
        $this->addContent($publishedJob->doCreate());

        # In Progress Job
        $progressJob = new TotalJobInProgress('inProgressJob');
        if ($this->EnableAutoReload) {
            $progressJob->setAutoReloadTime($this->AutoReloadTime);
        }
        $progressJob->addCallBackParameter('title', Trans::getWord('inProgressJob'));
        $progressJob->addCallBackParameter('jo_srv_id', 1);
        $progressJob->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $progressJob->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } else {
            if ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
                $progressJob->addCallBackParameter('jo_manager_id', $this->User->getId());
            }
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
        $completeJob->addCallBackParameter('jo_srv_id', 1);
        $completeJob->addCallBackParameter('jo_start_period', $month->format('Y-m') . '-01 00:01:00');
        $completeJob->addCallBackParameter('jo_end_period', $month->format('Y-m-t') . ' 23:59:00');
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $completeJob->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } else {
            if ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
                $completeJob->addCallBackParameter('jo_manager_id', $this->User->getId());
            }
        }
        $this->addContent($completeJob->doCreate());
    }

    /**
     * Function to add goods widget
     *
     * @return void
     */
    private function loadGoodsWidget(): void
    {
        $month = DateTimeParser::createDateTime();

        # Total inbound items this month
        $totalInbound = new TotalInboundItem('inboundItems');
        if ($this->EnableAutoReload) {
            $totalInbound->setAutoReloadTime($this->AutoReloadTime);
        }
        $totalInbound->addCallBackParameter('title', Trans::getWord('totalInboundThisMonth') . ' (' . Trans::getWord('items') . ')');
        $totalInbound->addCallBackParameter('jo_start_period', $month->format('Y-m') . '-01 00:01:00');
        $totalInbound->addCallBackParameter('jo_end_period', $month->format('Y-m-t') . ' 23:59:00');
        $totalInbound->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $totalInbound->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        }
        $this->addContent($totalInbound->doCreate());

        # Total inbound items this month
        $totalOutbound = new TotalOutboundItem('outboundItems');
        if ($this->EnableAutoReload) {
            $totalOutbound->setAutoReloadTime($this->AutoReloadTime);
        }
        $totalOutbound->addCallBackParameter('title', Trans::getWord('totalOutboundThisMonth') . ' (' . Trans::getWord('items') . ')');
        $totalOutbound->addCallBackParameter('jo_start_period', $month->format('Y-m') . '-01 00:01:00');
        $totalOutbound->addCallBackParameter('jo_end_period', $month->format('Y-m-t') . ' 23:59:00');
        $totalOutbound->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $totalOutbound->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        }
        $this->addContent($totalOutbound->doCreate());

        # Total good stock
        $totalGoodStock = new TotalGoodItem('goodItems');
        if ($this->EnableAutoReload) {
            $totalGoodStock->setAutoReloadTime($this->AutoReloadTime);
        }
        $totalGoodStock->addCallBackParameter('title', Trans::getWord('totalGoodStock') . ' (' . Trans::getWord('items') . ')');
        $totalGoodStock->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $totalGoodStock->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        }
        $this->addContent($totalGoodStock->doCreate());

        # Total damage stock
        $totalGoodStock = new TotalDamageItem('DamageItems');
        if ($this->EnableAutoReload) {
            $totalGoodStock->setAutoReloadTime($this->AutoReloadTime);
        }
        $totalGoodStock->addCallBackParameter('title', Trans::getWord('totalDamageStock') . ' (' . Trans::getWord('items') . ')');
        $totalGoodStock->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $totalGoodStock->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        }
        $this->addContent($totalGoodStock->doCreate());
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
        $arriveJob->addCallBackParameter('jo_srv_id', 1);
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $arriveJob->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } else {
            if ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
                $arriveJob->addCallBackParameter('jo_manager_id', $this->User->getId());
            }
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
        $progressJob->addCallBackParameter('jo_srv_id', 1);
        $progressJob->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        if ($this->PageSetting->checkPageRight('ThirdPartyAccess') === true) {
            $progressJob->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } else {
            if ($this->PageSetting->checkPageRight('AllowSeeAllOfficerJob') === false) {
                $progressJob->addCallBackParameter('jo_manager_id', $this->User->getId());
            }
        }
        $progressJob->setGridDimension(6, 6);
        $progressJob->setHeight(300);
        $this->addContent($progressJob->doCreate());
    }

    public function loadData(): array
    {
        // TODO: Implement loadData() method.
        return [];
    }

    protected function doInsert(): ?int
    {
        // TODO: Implement doInsert() method.
        return null;
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
