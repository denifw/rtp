<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Viewer\Setting\Action;

use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractViewerModel;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Setting\Action\SystemActionDao;
use App\Model\Dao\Setting\Action\SystemActionEventDao;

/**
 * Class to handle the creation of detail SystemAction page
 *
 * @package    app
 * @subpackage Model\Viewer\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemAction extends AbstractViewerModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'systemAction', 'sac_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateEvent') {
            if ($this->isValidParameter('sae_order') === true) {
                $order = $this->getIntParameter('sae_order');
            } else {
                $order = SystemActionEventDao::getLastOrderData($this->getDetailReferenceValue()) + 1;
            }
            $saeColVal = [
                'sae_sac_id' => $this->getDetailReferenceValue(),
                'sae_description' => $this->getStringParameter('sae_description'),
                'sae_order' => $order,
                'sae_active' => $this->getStringParameter('sae_active', 'Y'),
            ];
            $saeDao = new SystemActionEventDao();
            if ($this->isValidParameter('sae_id') === true) {
                $saeDao->doUpdateTransaction($this->getIntParameter('sae_id'), $saeColVal);
            } else {
                $saeDao->doInsertTransaction($saeColVal);
            }
        }

    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return SystemActionDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getEventFieldSet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateEvent') {
            $this->Validation->checkRequire('sae_description', 2, 255);
            if ($this->isValidParameter('sae_id') === true) {
                $this->Validation->checkRequire('sae_order');
            }
            if ($this->isValidParameter('sae_order') === true) {
                $this->Validation->checkInt('sae_order', 1);
            }

        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        $content = $this->generateTableView([
            [
                'label' => Trans::getWord('service'),
                'value' => $this->getStringParameter('sac_service'),
            ],
            [
                'label' => Trans::getWord('serviceTerm'),
                'value' => $this->getStringParameter('sac_service_term'),
            ],
            [
                'label' => Trans::getWord('action'),
                'value' => $this->getStringParameter('sac_action'),
            ],
            [
                'label' => Trans::getWord('orderNumber'),
                'value' => $this->getStringParameter('sac_order'),
            ],
        ]);

        # Create a portlet box.
        $portlet = new Portlet('SacGeneralPtl', Trans::getWord('actionSystem'));
        $portlet->addText($content);
        $portlet->setGridDimension(4);

        return $portlet;
    }

    /**
     * Function to get the event Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getEventFieldSet(): Portlet
    {
        $modal = $this->getEventModal();
        $this->View->addModal($modal);
        $table = new Table('SacSaeTbl');
        $table->setHeaderRow([
            'sae_description' => Trans::getWord('description'),
            'sae_order' => Trans::getWord('orderNumber'),
            'sae_active' => Trans::getWord('active'),
        ]);
        $data = SystemActionEventDao::getBySystemActionId($this->getDetailReferenceValue());
        $table->addRows($data);
        $table->setColumnType('sae_active', 'yesno');
        $table->setColumnType('sae_order', 'integer');
        $table->setUpdateActionByModal($modal, 'systemActionEvent', 'getByReference', ['sae_id']);
        # Create a portlet box.
        $portlet = new Portlet('SacSaePtl', Trans::getWord('events'));
        $btnCpMdl = new ModalButton('btnSaeMdl', Trans::getWord('addEvent'), $modal->getModalId());
        $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnCpMdl);
        $portlet->addTable($table);
        $portlet->setGridDimension(8, 8);

        return $portlet;
    }


    /**
     * Function to get Event modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getEventModal(): Modal
    {
        # Create Fields.

        $modal = new Modal('SacSaeMdl', Trans::getWord('event'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateEvent');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateEvent' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('sae_description', $this->getParameterForModal('sae_description', $showModal)), true);
        $fieldSet->addField(Trans::getWord('orderNumber'), $this->Field->getText('sae_order', $this->getParameterForModal('sae_order', $showModal)));
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('sae_active', $this->getParameterForModal('sae_active', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('sae_id', $this->getParameterForModal('sae_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

}
