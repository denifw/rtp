<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\CustomerService;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\CustomerService\LoadUnloadDeliveryDao;
use App\Model\Dao\CustomerService\SalesOrderDeliveryDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail SalesOrderDelivery page
 *
 * @package    app
 * @subpackage Model\Detail\CustomerService
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class SalesOrderDelivery extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'sdl', 'sdl_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        return 0;
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $sdlColVal = [
                'sdl_tm_id' => $this->getIntParameter('sdl_tm_id'),
                'sdl_eg_id' => $this->getIntParameter('sdl_eg_id'),
                'sdl_dp_id' => $this->getIntParameter('sdl_dp_id'),
                'sdl_dr_id' => $this->getIntParameter('sdl_dr_id'),
                'sdl_pick_date' => $this->getStringParameter('sdl_pick_date'),
                'sdl_pick_time' => $this->getStringParameter('sdl_pick_time'),
                'sdl_return_date' => $this->getStringParameter('sdl_return_date'),
                'sdl_return_time' => $this->getStringParameter('sdl_return_time'),
            ];
            $sdlDao = new SalesOrderDeliveryDao();
            $sdlDao->doUpdateTransaction($this->getDetailReferenceValue(), $sdlColVal);
        } elseif ($this->getFormAction() === 'doUpdateLoadUnload') {
            $ludColVal = [
                'lud_sdl_id' => $this->getDetailReferenceValue(),
                'lud_rel_id' => $this->getIntParameter('lud_rel_id'),
                'lud_of_id' => $this->getIntParameter('lud_of_id'),
                'lud_pic_id' => $this->getIntParameter('lud_pic_id'),
                'lud_reference' => $this->getStringParameter('lud_reference'),
                'lud_sog_id' => $this->getIntParameter('lud_sog_id'),
                'lud_quantity' => $this->getFloatParameter('lud_quantity'),
                'lud_planning_date' => $this->getStringParameter('lud_planning_date'),
                'lud_planning_time' => $this->getStringParameter('lud_planning_time'),
                'lud_type' => $this->getStringParameter('lud_type'),
            ];
            $ludDao = new LoadUnloadDeliveryDao();
            if ($this->isValidParameter('lud_id') === true) {
                $ludDao->doUpdateTransaction($this->getIntParameter('lud_id'), $ludColVal);
            } else {
                $ludDao->doInsertTransaction($ludColVal);
            }
        } elseif ($this->getFormAction() === 'doDeleteLoadUnload') {
            $ludDao = new LoadUnloadDeliveryDao();
            $ludDao->doDeleteTransaction($this->getIntParameter('lud_id_del'));
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return SalesOrderDeliveryDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        $addModal = $this->getLoadUnloadModal();
        $this->View->addModal($addModal);
        $deleteModal = $this->getLoadUnloadDeleteModal();
        $this->View->addModal($deleteModal);
        if ($this->getStringParameter('sdl_load', 'N') === 'Y') {
            $this->Tab->addPortlet('general', $this->getLoadUnloadPortlet('O', $addModal, $deleteModal));
        }
        if ($this->getStringParameter('sdl_unload', 'N') === 'Y') {
            $this->Tab->addPortlet('general', $this->getLoadUnloadPortlet('D', $addModal, $deleteModal));
        }

    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {
            if ($this->getStringParameter('so_consolidate', 'N') === 'N') {
                if ($this->getStringParameter('so_container', 'N') === 'Y') {
                    $this->Validation->checkRequire('sdl_dp_rel_id');
                    $this->Validation->checkRequire('sdl_dp_id');
                    if ($this->isValidParameter('sdl_pick_date') === true) {
                        $this->Validation->checkDate('sdl_pick_date');
                    }
                    if ($this->isValidParameter('sdl_return_time') === true) {
                        $this->Validation->checkTime('sdl_return_time');
                    }
                    if ($this->isValidParameter('sdl_return_date') === true) {
                        $this->Validation->checkDate('sdl_return_date');
                    }
                    if ($this->isValidParameter('sdl_pick_time') === true) {
                        $this->Validation->checkTime('sdl_pick_time');
                    }
                } else {
                    $this->Validation->checkRequire('sdl_tm_id');
                    $this->Validation->checkRequire('sdl_eg_id');
                }
            }
        } elseif ($this->getFormAction() === 'doUpdateLoadUnload') {
            $this->Validation->checkRequire('lud_type');
            $this->Validation->checkRequire('lud_rel_id');
            $this->Validation->checkRequire('lud_of_id');
            $this->Validation->checkRequire('lud_sog_id');
            if ($this->isValidParameter('lud_quantity') === true) {
                $this->Validation->checkFloat('lud_quantity', 1);
            }
            if ($this->isValidParameter('lud_planning_date') === true) {
                $this->Validation->checkDate('lud_planning_date');
            }
            if ($this->isValidParameter('lud_planning_time') === true) {
                $this->Validation->checkTime('lud_planning_time');
            }
        } elseif ($this->getFormAction() === 'doDeleteLoadUnload') {
            $this->Validation->checkRequire('lud_id_del');
        } else {
            parent::loadValidationRole();
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('SdlGeneralPtl', $this->getDefaultPortletTitle());

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);


        # Create Transport Module Field
        $tmField = $this->Field->getSingleSelect('transportModule', 'sdl_transport_module', $this->getStringParameter('sdl_transport_module'));
        $tmField->setHiddenField('sdl_tm_id', $this->getIntParameter('sdl_tm_id'));
        $tmField->setEnableNewButton(false);
        $tmField->addClearField('sdl_eg_id');
        $tmField->addClearField('sdl_equipment_group');

        # Create Transport Module Field
        $egField = $this->Field->getSingleSelect('eg', 'sdl_equipment_group', $this->getStringParameter('sdl_equipment_group'));
        $egField->setHiddenField('sdl_eg_id', $this->getIntParameter('sdl_eg_id'));
        $egField->addParameterById('eg_tm_id', 'sdl_tm_id', Trans::getWord('transportModule'));
        $egField->setEnableNewButton(false);
        # Create Unit Field

        $srtField = $this->Field->getText('sdl_service_term', $this->getStringParameter('sdl_service_term'));
        $srtField->setReadOnly();
        $ctField = $this->Field->getText('sdl_container_type', $this->getStringParameter('sdl_container_type'));
        $ctField->setReadOnly();
        $containerNumberField = $this->Field->getText('sdl_container_number', $this->getStringParameter('sdl_container_number'));
        $containerNumberField->setReadOnly();
        $sealNumberField = $this->Field->getText('sdl_seal_number', $this->getStringParameter('sdl_seal_number'));
        $sealNumberField->setReadOnly();

        # Create Depo Pickup Owner
        $dpOwnerField = $this->Field->getSingleSelect('relation', 'sdl_dp_owner', $this->getStringParameter('sdl_dp_owner'));
        $dpOwnerField->setHiddenField('sdl_dp_rel_id', $this->getIntParameter('sdl_dp_rel_id'));
        $dpOwnerField->addParameter('rel_ss_id', $this->User->getSsId());
        $dpOwnerField->setDetailReferenceCode('rel_id');
        $dpOwnerField->addClearField('sdl_dp_id');
        $dpOwnerField->addClearField('sdl_dp_name');
        # Create depo pickup
        $dpField = $this->Field->getSingleSelect('office', 'sdl_dp_name', $this->getStringParameter('sdl_dp_name'));
        $dpField->setHiddenField('sdl_dp_id', $this->getIntParameter('sdl_dp_id'));
        $dpField->addParameterById('of_rel_id', 'sdl_dp_rel_id', Trans::getWord('ownerDepoPickUp'));
        $dpField->setDetailReferenceCode('of_id');

        # Create Depo Return Owner
        $drOwnerField = $this->Field->getSingleSelect('relation', 'sdl_dr_owner', $this->getStringParameter('sdl_dr_owner'));
        $drOwnerField->setHiddenField('sdl_dr_rel_id', $this->getIntParameter('sdl_dr_rel_id'));
        $drOwnerField->addParameter('rel_ss_id', $this->User->getSsId());
        $drOwnerField->setDetailReferenceCode('rel_id');
        $drOwnerField->addClearField('sdl_dr_id');
        $drOwnerField->addClearField('sdl_dr_name');
        # Create depo return
        $drField = $this->Field->getSingleSelect('office', 'sdl_dr_name', $this->getStringParameter('sdl_dr_name'));
        $drField->setHiddenField('sdl_dr_id', $this->getIntParameter('sdl_dr_id'));
        $drField->addParameterById('of_rel_id', 'sdl_dr_rel_id', Trans::getWord('ownerDepoReturn'));
        $drField->setDetailReferenceCode('of_id');

        # Add field to field set
        $fieldSet->addField(Trans::getWord('serviceTerm'), $srtField, true);
        if ($this->getStringParameter('so_consolidate', 'N') === 'N') {
            if ($this->getStringParameter('so_container', 'N') === 'Y') {
                $fieldSet->addField(Trans::getWord('containerType'), $ctField, true);
                $fieldSet->addField(Trans::getWord('containerNumber'), $containerNumberField);
                $fieldSet->addField(Trans::getWord('sealNumber'), $sealNumberField);
                $fieldSet->addField(Trans::getWord('ownerDepoPickUp'), $dpOwnerField, true);
                $fieldSet->addField(Trans::getWord('depoPickUp'), $dpField, true);
                $fieldSet->addField(Trans::getWord('pickUpDate'), $this->Field->getCalendar('sdl_pick_date', $this->getStringParameter('sdl_pick_date')));
                $fieldSet->addField(Trans::getWord('pickUpTime'), $this->Field->getTime('sdl_pick_time', $this->getStringParameter('sdl_pick_time')));
                $fieldSet->addField(Trans::getWord('ownerDepoReturn'), $drOwnerField);
                $fieldSet->addField(Trans::getWord('depoReturn'), $drField);
                $fieldSet->addField(Trans::getWord('returnDate'), $this->Field->getCalendar('sdl_return_date', $this->getStringParameter('sdl_return_date')));
                $fieldSet->addField(Trans::getWord('returnTime'), $this->Field->getTime('sdl_return_time', $this->getStringParameter('sdl_return_time')));
            } else {
                $fieldSet->addField(Trans::getWord('transportModule'), $tmField, true);
                $fieldSet->addField(Trans::getWord('transportType'), $egField, true);
            }
        }
        $fieldSet->addHiddenField($this->Field->getHidden('so_consolidate', $this->getStringParameter('so_consolidate')));
        $fieldSet->addHiddenField($this->Field->getHidden('so_container', $this->getStringParameter('so_container')));

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }


    /**
     * Function to get the Load unload portlet.
     *
     * @param string $type To store the type location, is it O or D.
     * @param Modal $addModal To store the id modal for adding address.
     * @param Modal $deleteModal To store the id modal for adding address.
     *
     *
     * @return Portlet
     */
    protected function getLoadUnloadPortlet(string $type, Modal $addModal, Modal $deleteModal): Portlet
    {
        $table = new Table('SdlJtdTbl' . $type);
        $table->setHeaderRow([
            'lud_relation' => Trans::getWord('relation'),
            'lud_address' => Trans::getWord('address'),
            'lud_pic' => Trans::getWord('pic'),
            'lud_reference' => Trans::getWord('reference'),
            'lud_goods' => Trans::getWord('goods'),
            'lud_quantity' => Trans::getWord('quantity'),
            'lud_time' => Trans::getWord('planningTime'),
        ]);

        $data = LoadUnloadDeliveryDao::getBySdlIdAndType($this->getDetailReferenceValue(), $type);
        $rows = [];
        $formatter = new StringFormatter();
        $dt = new DateTimeParser();
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $row['lud_address'] = $formatter->doFormatAddress($row);
            $eta = '';
            if (empty($row['lud_planning_date']) === false) {
                if (empty($row['lud_planning_time']) === false) {
                    $eta = $dt->formatDateTime($row['lud_planning_date'] . ' ' . $row['lud_planning_time']);
                } else {
                    $eta = $dt->formatDate($row['lud_planning_date']);
                }
            }
            $row['lud_time'] = $eta;
            $quantity = '';
            if (empty($row['lud_quantity']) === false) {
                $quantity = $number->doFormatFloat($row['lud_quantity']);
                if (empty($row['lud_uom_code']) === false) {
                    $quantity = ' ' . $row['lud_uom_code'];
                }
            }
            $row['lud_quantity'] = $quantity;
            $rows[] = $row;
        }

        $table->addRows($rows);
        $title = Trans::getWord('originAddress');
        if ($type === 'D') {
            $title = Trans::getWord('destinationAddress');
        }
        # Create a portlet box.
        $portlet = new Portlet('SdlLudPtl' . $type, $title);

        $table->setUpdateActionByModal($addModal, 'lud', 'getById', ['lud_id']);
        $table->setDeleteActionByModal($deleteModal, 'lud', 'getByIdForDelete', ['lud_id']);
        # add new button
        $btnCpMdl = new ModalButton('btnSdlLudMdl' . $type, Trans::getWord('addAddress'), $addModal->getModalId());
        $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnCpMdl);
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get Goods modal.
     *
     * @return Modal
     */
    private function getLoadUnloadModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SdlLudMdl', Trans::getWord('loadUnloadAddress'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateLoadUnload');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateLoadUnload' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $typeField = $this->Field->getSelect('lud_type', $this->getParameterForModal('lud_type', $showModal));
        if ($this->getStringParameter('sdl_load', 'N') === 'Y') {
            $typeField->addOption(Trans::getWord('origin'), 'O');
        }
        if ($this->getStringParameter('sdl_unload', 'N') === 'Y') {
            $typeField->addOption(Trans::getWord('destination'), 'D');
        }
        # Create Relation
        $relField = $this->Field->getSingleSelect('relation', 'lud_relation', $this->getParameterForModal('lud_relation', $showModal));
        $relField->setHiddenField('lud_rel_id', $this->getParameterForModal('lud_rel_id', $showModal));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setDetailReferenceCode('rel_id');
        $relField->addClearField('lud_address');
        $relField->addClearField('lud_of_id');
        $relField->addClearField('lud_pic');
        $relField->addClearField('lud_pic_id');

        # Create office
        $ofField = $this->Field->getSingleSelect('office', 'lud_address', $this->getParameterForModal('lud_address', $showModal), 'loadOfficeAddress');
        $ofField->setHiddenField('lud_of_id', $this->getParameterForModal('lud_of_id', $showModal));
        $ofField->addParameterById('of_rel_id', 'lud_rel_id', Trans::getWord('relation'));
        $ofField->setDetailReferenceCode('of_id');
        $ofField->addClearField('lud_pic');
        $ofField->addClearField('lud_pic_id');

        # Create pic
        $picField = $this->Field->getSingleSelect('contactPerson', 'lud_pic', $this->getParameterForModal('lud_pic', $showModal));
        $picField->setHiddenField('lud_pic_id', $this->getParameterForModal('lud_pic_id', $showModal));
        $picField->addParameterById('cp_of_id', 'lud_of_id', Trans::getWord('address'));
        $picField->setDetailReferenceCode('cp_id');

        # Create Goods Field
        $sogField = $this->Field->getSingleSelectTable('sog', 'lud_goods', $this->getParameterForModal('lud_goods', $showModal));
        $sogField->setHiddenField('lud_sog_id', $this->getParameterForModal('lud_sog_id', $showModal));
        $sogField->setTableColumns([
            'sog_hs_code' => Trans::getWord('hsCode'),
            'sog_name' => Trans::getWord('description'),
            'sog_packing_ref' => Trans::getWord('packingRef'),
            'sog_quantity_number' => Trans::getWord('quantity'),
            'sog_uom' => Trans::getWord('uom')
        ]);
        $sogField->setAutoCompleteFields([
            'lud_quantity' => 'sog_quantity',
            'lud_quantity_number' => 'sog_quantity_number',
            'lud_uom_code' => 'sog_uom',
        ]);
        $sogField->setValueCode('sog_id');
        $sogField->setLabelCode('sog_name');
        $sogField->addParameter('sog_so_id', $this->getIntParameter('sdl_so_id'));
        if ($this->getStringParameter('so_container', 'N') === 'Y') {
            $sogField->addParameter('sog_soc_id', $this->getIntParameter('sdl_soc_id'));
        }
        $sogField->setParentModal($modal->getModalId());
        $this->View->addModal($sogField->getModal());

        $uomField = $this->Field->getText('lud_uom_code', $this->getParameterForModal('lud_uom_code', $showModal));
        $uomField->setReadOnly();

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('type'), $typeField, true);
        $fieldSet->addField(Trans::getWord('relation'), $relField, true);
        $fieldSet->addField(Trans::getWord('address'), $ofField, true);
        $fieldSet->addField(Trans::getWord('pic'), $picField);
        $fieldSet->addField(Trans::getWord('reference'), $this->Field->getText('lud_reference', $this->getParameterForModal('lud_reference', $showModal)));
        $fieldSet->addField(Trans::getWord('goods'), $sogField, true);
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('lud_quantity', $this->getParameterForModal('lud_quantity', $showModal)));
        $fieldSet->addField(Trans::getWord('uom'), $uomField);
        $fieldSet->addField(Trans::getWord('etaDate'), $this->Field->getCalendar('lud_planning_date', $this->getParameterForModal('lud_planning_date', $showModal)));
        $fieldSet->addField(Trans::getWord('etaTime'), $this->Field->getTime('lud_planning_time', $this->getParameterForModal('lud_planning_time', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('lud_id', $this->getParameterForModal('lud_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get Goods delete modal.
     *
     * @return Modal
     */
    protected function getLoadUnloadDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JtdLDelMdl', Trans::getWord('deleteAddress'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteLoadUnload');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteLoadUnload' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('relation'), $this->Field->getText('lud_relation_del', $this->getParameterForModal('lud_relation_del', $showModal)));
        $fieldSet->addField(Trans::getWord('address'), $this->Field->getText('lud_address_del', $this->getParameterForModal('lud_address_del', $showModal)));
        $fieldSet->addField(Trans::getWord('pic'), $this->Field->getText('lud_pic_del', $this->getParameterForModal('lud_pic_del', $showModal)));
        $fieldSet->addField(Trans::getWord('reference'), $this->Field->getText('lud_reference_del', $this->getParameterForModal('lud_reference_del', $showModal)));
        $fieldSet->addField(Trans::getWord('goods'), $this->Field->getText('lud_goods_del', $this->getParameterForModal('lud_goods_del', $showModal)));
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('lud_quantity_del', $this->getParameterForModal('lud_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getWord('etaDate'), $this->Field->getText('lud_planning_date_del', $this->getParameterForModal('lud_planning_date_del', $showModal)));
        $fieldSet->addField(Trans::getWord('etaTime'), $this->Field->getText('lud_planning_time_del', $this->getParameterForModal('lud_planning_time_del', $showModal)));

        $fieldSet->addHiddenField($this->Field->getHidden('lud_id_del', $this->getParameterForModal('lud_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


}
