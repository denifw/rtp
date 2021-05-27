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

/**
 * Class to create the view for Warehouse dashboard.
 *
 * @package    app
 * @subpackage Model\Dashboard
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class Fms extends AbstractDashboardModel
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
        parent::__construct(get_class($this), 'fmsHome');
        $this->setParameters($parameters);
    }


    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadView(): void
    {
        $url = '/fmsHome?ar=1';
        $btnClass = 'btn btn-primary pull-right btn-sm';
        $btnIcon = Icon::Repeat;
        $btnText = Trans::getWord('startAutoReload');
        $this->EnableAutoReload = false;
        if ($this->getIntParameter('ar', 0) === 1) {
            $this->EnableAutoReload = true;
            $url = '/fmsHome';
            $btnClass = 'btn btn-danger pull-right btn-sm';
            $btnIcon = Icon::Stop;
            $btnText = Trans::getWord('stopAutoReload');
        }
        $btn = new HyperLink('btnReload', $btnText, url($url));
        $btn->addAttribute('class', $btnClass);
        $btn->setIcon($btnIcon);
        $this->View->addButton($btn);
        $this->loadJobWidget();
//        $this->loadContainersWidget();
//        $this->loadJobTable();
    }


    /**
     * Function to add job widget
     *
     * @return void
     */
    private function loadJobWidget(): void
    {
        # Equipment Reminder
        $equipmentReminder = new EquipmentReminder('equipmentReminder');
        $equipmentReminder->addCallBackParameter('title', Trans::getFmsWord('serviceReminder'));
        $equipmentReminder->addCallBackParameter('eq_ss_id', $this->User->getSsId());
        if ($this->EnableAutoReload) {
            $equipmentReminder->setAutoReloadTime($this->AutoReloadTime);
        }
        $this->addContent($equipmentReminder->doCreate());
        # Equipment Status
        $equipmentStatus = new EquipmentStatus('equipmentStatus');
        $equipmentStatus->addCallBackParameter('title', Trans::getFmsWord('equipmentStatus'));
        $equipmentStatus->addCallBackParameter('eq_ss_id', $this->User->getSsId());
        if ($this->EnableAutoReload) {
            $equipmentStatus->setAutoReloadTime($this->AutoReloadTime);
        }
        $this->addContent($equipmentStatus->doCreate());
        # Equipment Cost
        $equipmentCost = new EquipmentCost('equipmentCost');
        $equipmentCost->addCallBackParameter('title', Trans::getFmsWord('cost'));
        $equipmentCost->addCallBackParameter('eq_ss_id', $this->User->getSsId());
        if ($this->EnableAutoReload) {
            $equipmentCost->setAutoReloadTime($this->AutoReloadTime);
        }
        $this->addContent($equipmentCost->doCreate());

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
