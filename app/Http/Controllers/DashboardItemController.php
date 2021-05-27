<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Http\Controllers;

use App\Model\DashboardItem\Table\AutoReloadPlanningJob;
use App\Model\DashboardItem\Table\AutoReloadProgressJob;
use App\Model\DashboardItem\Table\InProgressJob;
use App\Model\DashboardItem\Table\PlanningJob;
use App\Model\DashboardItem\Table\Warehouse\ArriveSoon;
use App\Model\DashboardItem\Widget\Fms\EquipmentCost;
use App\Model\DashboardItem\Widget\Fms\EquipmentReminder;
use App\Model\DashboardItem\Widget\Fms\EquipmentStatus;
use App\Model\DashboardItem\Widget\Inklaring\TotalExport;
use App\Model\DashboardItem\Widget\Inklaring\TotalExportContainer;
use App\Model\DashboardItem\Widget\Inklaring\TotalImport;
use App\Model\DashboardItem\Widget\Inklaring\TotalImportContainer;
use App\Model\DashboardItem\Widget\TotalJobComplete;
use App\Model\DashboardItem\Widget\TotalJobInProgress;
use App\Model\DashboardItem\Widget\TotalJobPlanning;
use App\Model\DashboardItem\Widget\TotalJobPublished;
use App\Model\DashboardItem\Widget\Warehouse\TotalDamageItem;
use App\Model\DashboardItem\Widget\Warehouse\TotalGoodItem;
use App\Model\DashboardItem\Widget\Warehouse\TotalInboundItem;
use App\Model\DashboardItem\Widget\Warehouse\TotalOutboundItem;

class DashboardItemController extends AbstractBaseController
{
    /**
     * The function to load the list job that in progress
     *
     * @return mixed
     */
    public function inProgressJobTrucking()
    {
        $model = new \App\Model\DashboardItem\Table\Trucking\InProgressJob();

        return $this->doControlDashboardItem($model);
    }
    /**
     * The function to load the list job that in progress
     *
     * @return mixed
     */
    public function planningJobTrucking()
    {
        $model = new \App\Model\DashboardItem\Table\Trucking\PlanningJob();

        return $this->doControlDashboardItem($model);
    }
    /**
     * The function to load the list job that in progress
     *
     * @return mixed
     */
    public function totalPublishedJob()
    {
        $model = new TotalJobPublished();

        return $this->doControlDashboardItem($model);
    }
    /**
     * The function to load the list job that in progress
     *
     * @return mixed
     */
    public function autoReloadProgressJob()
    {
        $model = new AutoReloadProgressJob();

        return $this->doControlDashboardItem($model);
    }
    /**
     * The function to load the list job that in progress
     *
     * @return mixed
     */
    public function autoReloadPlanningJob()
    {
        $model = new AutoReloadPlanningJob();

        return $this->doControlDashboardItem($model);
    }

    /**
     * The function to load the list job that in progress
     *
     * @return mixed
     */
    public function planningJobTable()
    {
        $model = new PlanningJob();

        return $this->doControlDashboardItem($model);
    }
    /**
     * The function to load the list job that in progress
     *
     * @return mixed
     */
    public function inProgressJobTable()
    {
        $model = new InProgressJob();

        return $this->doControlDashboardItem($model);
    }
    /**
     * The function to load the list warehouse job that will arrive soon
     *
     * @return mixed
     */
    public function warehouseArriveSoon()
    {
        $model = new ArriveSoon();

        return $this->doControlDashboardItem($model);
    }
    /**
     * The function to load the total damage stock
     *
     * @return mixed
     */
    public function totalDamageItem()
    {
        $model = new TotalDamageItem();

        return $this->doControlDashboardItem($model);
    }
    /**
     * The function to load the total good stock
     *
     * @return mixed
     */
    public function totalGoodItem()
    {
        $model = new TotalGoodItem();

        return $this->doControlDashboardItem($model);
    }
    /**
     * The function to load the detail of menu.
     *
     * @return mixed
     */
    public function totalInboundItem()
    {
        $model = new TotalInboundItem();

        return $this->doControlDashboardItem($model);
    }
    /**
     * The function to load the detail of menu.
     *
     * @return mixed
     */
    public function totalOutboundItem()
    {
        $model = new TotalOutboundItem();

        return $this->doControlDashboardItem($model);
    }
    /**
     * The function to load the detail of menu.
     *
     * @return mixed
     */
    public function totalInProgressJob()
    {
        $model = new TotalJobInProgress();

        return $this->doControlDashboardItem($model);
    }

    /**
     * The function to load the detail of menu.
     *
     * @return mixed
     */
    public function totalCompleteJob()
    {
        $model = new TotalJobComplete();

        return $this->doControlDashboardItem($model);
    }

    /**
     * The function to load the detail of menu.
     *
     * @return mixed
     */
    public function totalDraftJob()
    {
        $model = new TotalJobPlanning();

        return $this->doControlDashboardItem($model);
    }

    /**
     * The function to load the detail of menu.
     *
     * @return mixed
     */
    public function totalImportContainer()
    {
        $model = new TotalImportContainer();

        return $this->doControlDashboardItem($model);
    }

    /**
     * The function to load the detail of menu.
     *
     * @return mixed
     */
    public function totalExportContainer()
    {
        $model = new TotalExportContainer();

        return $this->doControlDashboardItem($model);
    }

    /**
     * The function to load the detail of menu.
     *
     * @return mixed
     */
    public function totalImport()
    {
        $model = new TotalImport();

        return $this->doControlDashboardItem($model);
    }

    /**
     * The function to load the detail of menu.
     *
     * @return mixed
     */
    public function totalExport()
    {
        $model = new TotalExport();

        return $this->doControlDashboardItem($model);
    }

    /**
     * The function to load the detail of menu.
     *
     * @return mixed
     */
    public function equipmentStatus()
    {
        $model = new EquipmentStatus();

        return $this->doControlDashboardItem($model);
    }

    /**
     * The function to load the detail of menu.
     *
     * @return mixed
     */
    public function equipmentCost()
    {
        $model = new EquipmentCost();

        return $this->doControlDashboardItem($model);
    }

    /**
     * The function to load the detail of menu.
     *
     * @return mixed
     */
    public function equipmentReminder()
    {
        $model = new EquipmentReminder();

        return $this->doControlDashboardItem($model);
    }
}
