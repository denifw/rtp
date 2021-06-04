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

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Icon;
use App\Frame\Mvc\AbstractDashboardModel;
use App\Model\Dao\Setting\DashboardDao;
use App\Model\Dao\Setting\DashboardDetailDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to create the view for home dashboard.
 *
 * @package    app
 * @subpackage Model\Dashboard
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Home extends AbstractDashboardModel
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
    protected $EnableAutoReload = false;

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'home');
        $this->setParameters($parameters);
        if ($this->getIntParameter('ar', 0) === 1) {
            $this->EnableAutoReload = true;
        }
    }

    /**
     * Function to load dashboard item.
     *
     * @return void
     */
    public function loadDashboardItem(): void
    {
//        if ($this->isValidParameter('dsh_order') === false) {
//            $this->setParameter('dsh_order', $this->getLastOrderNumber());
//            $this->setParameter('dsh_order_number', $this->getLastOrderNumber());
//        }
//        if ($this->isValidParameter('dsh_order_new') === false) {
//            $this->setParameter('dsh_order_new', $this->getLastOrderNumber());
//            $this->setParameter('dsh_order_new_number', $this->getLastOrderNumber());
//        }
//        $dshId = $this->getReferenceValue();
//        $wheres[] = '(dsd.dsd_dsh_id = ' . $dshId . ')';
//        $wheres[] = '(dsd.dsd_deleted_on IS NULL)';
//        $wheres[] = '(dsi.dsi_deleted_on IS NULL)';
//        $orderList[] = 'dsd.dsd_order ASC';
//        $dashboardItem = DashboardDetailDao::loadData($wheres, $orderList);
//        $pageRight = $this->PageSetting->loadPageRightsByIdPage($this->PageSetting->getPageId());
//        $modalDeleteWidget = $this->getDeleteWidgetModal();
//        $this->View->addModal($modalDeleteWidget);
//        foreach ($dashboardItem AS $row) {
//            $pagePath = str_replace('/', '\\', $row['dsi_path']);
//            $model = 'App\\Model\\DashboardItem\\' . $pagePath;
//            if (class_exists($model)) {
//                $id = str_replace(' ', '', $row['dsi_code']) . $row['dsd_id'];
//                $route = $row['dsi_route'];
//                $this->Model = new $model($id);
//                $this->Model->setParameters($row);
//                $this->Model->setRoute($route);
//                $this->Model->setPageRight($pageRight);
//                $this->Model->ModalDelete = $modalDeleteWidget;
//                if ($this->EnableAutoReload) {
//                    $this->Model->setAutoReloadTime($this->AutoReloadTime);
//                }
//                if (empty($row['dsd_parameter']) === false) {
//                    $this->Model->addCallBackParameters(json_decode($row['dsd_parameter'], true));
//                }
//                $this->addContent($this->Model->doCreate());
//            } else {
//                $this->addContent('Class : ' . $model . ' Not exist');
//            }
//        }
    }

//    /**
//     * Function to load the dashboard menu.
//     *
//     * @return void
//     */
//    public function loadDashboardMenu(): void
//    {
//        # Load all dashboard list by user id
//        $wheres[] = '(dsh.dsh_ss_id = ' . $this->User->getSsId() . ')';
//        $wheres[] = '(dsh.dsh_us_id = ' . $this->User->getId() . ')';
//        $wheres[] = '(dsh.dsh_deleted_on IS NULL)';
//        $listDashboard = DashboardDao::loadData($wheres);
//        foreach ($listDashboard AS $row) {
//            $portlet = new Portlet('dshPtl_' . $row['dsh_id']);
//            $portlet->addText($row['dsh_name']);
//            $portlet->setTitle($row['dsh_name']);
//            $portlet->setIcon('fa fa-dashboard');
//            $portlet->setGridDimension();
//            $btnDsi = new HyperLink('btnDsi_' . $row['dsh_id'], '', url('home?dsh_id=' . $row['dsh_id']));
//            $btnDsi->setIcon('fa fa-dashboard');
//            $btnDsi->addAttribute('class', 'btn-primary btn-icon-only');
//            $portlet->addButton($btnDsi);
//            $this->addContent($portlet);
//        }
//    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return DashboardDao::getByReferenceAndSystemAndUser($this->getReferenceValue(), $this->User->getSsId(), $this->User->getId());
    }

    /**
     * Abstract function to insert data into database.
     *
     * @return null|int
     */
    protected function doInsert(): ?int
    {
        $colVal = [
            'dsh_ss_id' => $this->User->getSsId(),
            'dsh_us_id' => $this->User->getId(),
            'dsh_name' => $this->getStringParameter('dsh_name_new'),
            'dsh_description' => $this->getStringParameter('dsh_description_new'),
            'dsh_order' => $this->getIntParameter('dsh_order_new'),
        ];
        $dshDao = new DashboardDao();
        $dshDao->doInsertTransaction($colVal);

        return $dshDao->getLastInsertId();
    }

    /**
     * Abstract function to update data in database.
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doInsertDashboard') {
            $colVal = [
                'dsh_ss_id' => $this->User->getSsId(),
                'dsh_us_id' => $this->User->getId(),
                'dsh_name' => $this->getStringParameter('dsh_name_new'),
                'dsh_description' => $this->getStringParameter('dsh_description_new'),
                'dsh_order' => $this->getIntParameter('dsh_order_new'),
            ];
            $dshDao = new DashboardDao();
            $dshDao->doInsertTransaction($colVal);
            $this->setReferenceValue($dshDao->getLastInsertId());
        } elseif ($this->getFormAction() === 'doUpdateDashboard') {
            $colVal = [
                'dsh_ss_id' => $this->User->getSsId(),
                'dsh_us_id' => $this->User->getId(),
                'dsh_name' => $this->getStringParameter('dsh_name'),
                'dsh_description' => $this->getStringParameter('dsh_description'),
                'dsh_order' => $this->getIntParameter('dsh_order'),
            ];
            $dshDao = new DashboardDao();
            $dshDao->doUpdateTransaction($this->getIntParameter('dsh_id'), $colVal);
        } elseif ($this->getFormAction() === 'doDeleteDashboard') {
            $dshDao = new DashboardDao();
            $dshDao->doDeleteTransaction($this->getIntParameter('dsh_id_del'));
            $dshId = $this->getfirstDashboardId();
            if (empty($dshId) === false) {
                $this->setReferenceValue($dshId);
            }
        } elseif ($this->getFormAction() === 'doDeleteWidget') {
            $dsdDao = new DashboardDetailDao();
            $dsdDao->doHardDeleteTransaction($this->getIntParameter('dsd_id_del'));
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doInsertDashboard') {
            $this->Validation->checkRequire('dsh_name_new', 3, 255);
            $this->Validation->checkRequire('dsh_description_new', 3, 255);
            $this->Validation->checkInt('dsh_order_new');
        } elseif ($this->getFormAction() === 'doUpdateDashboard') {
            $this->Validation->checkRequire('dsh_name', 3, 255);
            $this->Validation->checkRequire('dsh_description', 3, 255);
            $this->Validation->checkInt('dsh_order');
            $this->Validation->checkRequire('dsh_id');
        } elseif ($this->getFormAction() === 'doDeleteDashboard') {
            $this->Validation->checkRequire('dsh_id_del');
        } elseif ($this->getFormAction() === 'doDeleteWidget') {
            $this->Validation->checkRequire('dsd_id_del');
        }
    }

    /**
     * Abstract function to load the default button of the page.
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isUpdate() === true) {
//            $btnItem = new HyperLink('BtnNewItem', Trans::getWord('addWidget'), url('dashboardDetail/detail?dsh_id=' . $this->getReferenceValue() . '&pv=1'), false);
//            $btnItem->viewAsButton();
//            $btnItem->setIcon(Icon::Plus)->btnMedium()->btnPrimary()->pullRight();
//            $this->View->addButton($btnItem);
//            # Modal edit dashboard
//            $editDashboardModal = $this->getEditDashboardModal();
//            $this->View->addModal($editDashboardModal);
//            $btnEditDsh = new ModalButton('BtnEditDsh', Trans::getWord('edit'), $editDashboardModal->getModalId());
//            $btnEditDsh->setEnableCallBack('dashboard', 'getByReference');
//            $btnEditDsh->addParameter('dsh_id', $this->getReferenceValue());
//            $btnEditDsh->setIcon(Icon::Pencil)->btnWarning()->pullRight();
//            $this->View->addButton($btnEditDsh);
            # Modal delete dashboard
//            if ($this->getTotalDashboard() > 1) {
//                $deleteDashboardModal = $this->getDeleteDashboardModal();
//                $this->View->addModal($deleteDashboardModal);
//                $btnDeleteDsh = new ModalButton('BtnDeleteDsh', Trans::getWord('delete'), $deleteDashboardModal->getModalId());
//                $btnDeleteDsh->setEnableCallBack('dashboard', 'getByReferenceForDelete');
//                $btnDeleteDsh->addParameter('dsh_id', $this->getReferenceValue());
//                $btnDeleteDsh->setIcon(Icon::Trash)->btnDanger()->pullRight();
//                $this->View->addButton($btnDeleteDsh);
//            }
            # Button Stop/Play auto reload
            $url = url($this->getDefaultRoute() . '?dsh_id=' . $this->getReferenceValue() . '&ar=1');
            $btnClass = 'btn btn-success pull-right btn-sm';
            $btnIcon = Icon::Repeat;
            $btnText = Trans::getWord('startAutoReload');
            if ($this->EnableAutoReload) {
                $url = url($this->getDefaultRoute() . '?dsh_id=' . $this->getReferenceValue());
                $btnClass = 'btn btn-danger pull-right btn-sm';
                $btnIcon = Icon::Stop;
                $btnText = Trans::getWord('stopAutoReload');
            }
            $btn = new HyperLink('btnReload', $btnText, $url);
            $btn->addAttribute('class', $btnClass);
            $btn->setIcon($btnIcon);
            $this->View->addButtonAtTheBeginning($btn);

        }
//        $newDashboardModal = $this->getNewDashboardModal();
//        $this->View->addModal($newDashboardModal);
//        $btnNewDsh = new ModalButton('BtnNewDsh', Trans::getWord('new'), $newDashboardModal->getModalId());
//        $btnNewDsh->setIcon(Icon::Plus)->btnSuccess()->pullRight();
//        $this->View->addButton($btnNewDsh);
        $btnReload = new Button('BtnReloadDsh', Trans::getWord('reload'));
        $btnReload->setIcon(Icon::Refresh)->btnPrimary()->pullRight();
        $btnReload->addAttribute('onclick', 'App.reloadWindow()');
        $this->View->addButton($btnReload);
    }

    /**
     * Function to get first dashboard
     *
     * @return int
     */
    private function getfirstDashboardId(): int
    {
        $dshId = 0;
        $query = 'SELECT dsh_id
                  FROM dashboard AS dsh
                  WHERE dsh.dsh_deleted_on IS NULL AND dsh_ss_id =' . $this->User->getSsId() . ' AND dsh_us_id =' . $this->User->getId() .
            ' ORDER BY dsh.dsh_order ASC
                  LIMIT 1 OFFSET 0 ';
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {
            $result = DataParser::objectToArray($sqlResult[0]);
            $dshId = $result['dsh_id'];
        }

        return $dshId;
    }

    /**
     * Function to get total dashboard user.
     *
     * @return int
     */
    private function getTotalDashboard(): int
    {
        $total = 0;
        $query = 'SELECT COUNT(dsh_id) AS total
                  FROM dashboard AS dsh
                  WHERE dsh.dsh_deleted_on IS NULL AND dsh_ss_id =' . $this->User->getSsId() . ' AND dsh_us_id =' . $this->User->getId() .
                  ' LIMIT 1 OFFSET 0 ';
        $sqlResult = DB::select($query);
        if (\count($sqlResult) === 1) {
            $result = DataParser::objectToArray($sqlResult[0]);
            $total = $result['total'];
        }

        return $total;
    }

}
