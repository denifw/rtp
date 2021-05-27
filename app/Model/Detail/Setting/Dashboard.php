<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Detail\Setting;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Setting\DashboardDao;
use App\Model\Dao\Setting\DashboardDetailDao;

/**
 * Class to handle the creation of detail Dashboard page
 *
 * @package    app
 * @subpackage Model\Detail\Setting
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class Dashboard extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'dashboard', 'dsh_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $colVal = [
            'dsh_ss_id' => $this->User->getSsId(),
            'dsh_us_id' => $this->User->getId(),
            'dsh_name' => $this->getStringParameter('dsh_name'),
            'dsh_description' => $this->getStringParameter('dsh_description')
        ];
        $dshDao = new DashboardDao();
        $dshDao->doInsertTransaction($colVal);

        return $dshDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateDashboardItem') {
            $colVal = [
                'dsi_dsh_id' => $this->getDetailReferenceValue(),
                'dsi_pg_id' => $this->getIntParameter('dsi_pg_id'),
                'dsi_order' => $this->getIntParameter('dsi_order')
            ];
            $dsiDao = new DashboardDetailDao();
            if ($this->isValidParameter('dsi_id')) {
                $dsiDao->doUpdateTransaction($this->getIntParameter('dsi_id'), $colVal);
            } else {
                $dsiDao->doInsertTransaction($colVal);
            }
        } else {
            $colVal = [
                'dsh_name' => $this->getStringParameter('dsh_name'),
                'dsh_description' => $this->getStringParameter('dsh_description')
            ];
            $dshDao = new DashboardDao();

            $dshDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return DashboardDao::getByReferenceAndSystemAndUser($this->getDetailReferenceValue(), $this->User->getSsId(), $this->User->getId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate()) {
            $this->Tab->addPortlet('general', $this->getDashboardItemFieldSet());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateDashboardItem') {
            $this->Validation->checkInt('dsi_order', 1);
            $this->Validation->checkRequire('dsi_pg_id');
            $this->Validation->checkUnique('dsi_pg_id', 'dashboard_item', [
                'dsi_id' => $this->getIntParameter('dsi_id')
            ], [
               'dsi_dsh_id' => $this->getDetailReferenceValue()
            ]);
        } else {
            $this->Validation->checkRequire('dsh_name', 3, 255);
            $this->Validation->checkRequire('dsh_description', 3, 255);
        }
    }

    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);
        # Add field to field set
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('dsh_name', $this->getStringParameter('dsh_name')), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getTextArea('dsh_description', $this->getStringParameter('dsh_description')), true);
        # Create a portlet box.
        $portlet = new Portlet('GnrPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the dashboard item Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getDashboardItemFieldSet(): Portlet
    {
        # Create a form.
        $modal = $this->getDashboardItemModal();
        $this->View->addModal($modal);
//        $modalDelete = $this->getDashboardItemDeleteModal();
//        $this->View->addModal($modalDelete);
        $table = new Table('DsiTbl');
        $table->setHeaderRow([
            'dsi_pg_title' => Trans::getWord('page'),
            'dsi_order' => Trans::getWord('order'),
        ]);
        $wheres[] = '(dsi.dsi_dsh_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(dsi.dsi_deleted_on IS NULL)';
        $orderList[] = 'dsi.dsi_order';
        $data = DashboardDetailDao::loadData($wheres, $orderList);
        $table->addRows($data);
        $table->setUpdateActionByModal($modal, 'dashboardItem', 'getByReference', ['dsi_id']);
        $btnAdd = new ModalButton('btnAddDsiMdl', Trans::getWord('addItem'), $modal->getModalId());
        $btnAdd->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        # Create a portlet box.
        $portlet = new Portlet('DsiPtl', Trans::getWord('dashboardItem'));
        $portlet->addTable($table);
        $portlet->addButton($btnAdd);
        $portlet->setGridDimension(12, 12, 12);

        return $portlet;
    }

    /**
     * Function to get storage modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    protected function getDashboardItemModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('DsiMdl', Trans::getWord('dashboardItem'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateDashboardItem');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateDashboardItem' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create Unit Field
        $pgField = $this->Field->getSingleSelect('page', 'dsi_pg_title', $this->getParameterForModal('dsi_pg_title', $showModal), 'loadPageDashboardItem');
        $pgField->setHiddenField('dsi_pg_id', $this->getParameterForModal('dsi_pg_id', $showModal));
        $pgField->addParameter('ump_ss_id', $this->User->getSsId());
        $pgField->addParameter('ump_id', $this->User->getMappingId());
        $pgField->setEnableNewButton(false);
        $pgField->setEnableDetailButton(false);
        $orderField = $this->Field->getNumber('dsi_order', $this->getParameterForModal('dsi_order', $showModal));
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('page'), $pgField, true);
        $fieldSet->addField(Trans::getWord('order'), $orderField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('dsi_id', $this->getParameterForModal('dsi_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

}
