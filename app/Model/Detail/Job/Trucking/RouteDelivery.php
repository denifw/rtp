<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\Job\Trucking;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Job\Trucking\RouteDeliveryDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\DateTimeParser;

/**
 * Class to handle the creation of detail RouteDelivery page
 *
 * @package    app
 * @subpackage Model\Detail\Trucking
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class RouteDelivery extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'rd', 'rd_id');
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
            'rd_ss_id' => $this->User->getSsId(),
            'rd_code' => $this->getStringParameter('rd_code'),
            'rd_dtc_or_id' => $this->getFloatParameter('rd_dtc_or_id'),
            'rd_dtc_des_id' => $this->getFloatParameter('rd_dtc_des_id'),
            'rd_distance' => $this->getFloatParameter('rd_distance'),
            'rd_drive_time' => $this->getFloatParameter('rd_drive_time'),
            'rd_toll_1' => $this->getFloatParameter('rd_toll_1'),
            'rd_toll_2' => $this->getFloatParameter('rd_toll_2'),
            'rd_toll_3' => $this->getFloatParameter('rd_toll_3'),
            'rd_toll_4' => $this->getFloatParameter('rd_toll_4'),
            'rd_toll_5' => $this->getFloatParameter('rd_toll_5'),
            'rd_toll_6' => $this->getFloatParameter('rd_toll_6'),
            'rd_active' => $this->getStringParameter('rd_active', 'Y'),
        ];
        $rdDao = new RouteDeliveryDao();
        $rdDao->doInsertTransaction($colVal);
        return $rdDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $colVal = [
                'rd_code' => $this->getStringParameter('rd_code'),
                'rd_dtc_or_id' => $this->getFloatParameter('rd_dtc_or_id'),
                'rd_dtc_des_id' => $this->getFloatParameter('rd_dtc_des_id'),
                'rd_distance' => $this->getFloatParameter('rd_distance'),
                'rd_drive_time' => $this->getFloatParameter('rd_drive_time'),
                'rd_toll_1' => $this->getFloatParameter('rd_toll_1'),
                'rd_toll_2' => $this->getFloatParameter('rd_toll_2'),
                'rd_toll_3' => $this->getFloatParameter('rd_toll_3'),
                'rd_toll_4' => $this->getFloatParameter('rd_toll_4'),
                'rd_toll_5' => $this->getFloatParameter('rd_toll_5'),
                'rd_toll_6' => $this->getFloatParameter('rd_toll_6'),
                'rd_active' => $this->getStringParameter('rd_active'),
            ];
            $rdDao = new RouteDeliveryDao();
            $rdDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->isDeleteAction()) {
            $colVal = [
                'rd_deleted_reason' => $this->getReasonDeleteAction(),
                'rd_deleted_on' => date('Y-m-d H:i:s'),
                'rd_deleted_by' => $this->User->getId(),
            ];
            $delDao = new RouteDeliveryDao();
            $delDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);

        }

    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return RouteDeliveryDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        # Call Portlet you want
        $this->Tab->addPortlet('general', $this->getOriginFieldSet());
        $this->Tab->addPortlet('general', $this->getDestinationFieldSet());
        $this->Tab->addPortlet('general', $this->getDetailFieldSet());
        if ($this->isUpdate()) {
            if ($this->PageSetting->checkPageRight('AllowDelete')) {
                $this->setEnableDeleteButton(true);
            }
            if ($this->isValidParameter('rd_deleted_reason')) {
                # Set delete button, disable update button when reason not null
                $this->setEnableDeleteButton(false);
                $this->setDisableUpdate();
                $message = $this->getStringParameter('rd_deleted_reason');
                $deletedBy = $this->getStringParameter('rd_us_name');
                $deletedOn = $this->getStringParameter('rd_deleted_on');
                $this->View->addErrorMessage(Trans::getWord('deletedData', 'message', '', [
                    'user' => $deletedBy,
                    'time' => DateTimeParser::format($deletedOn),
                    'reason' => $message,
                ]));
            }
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
            $this->Validation->checkRequire('rd_code', 3, 125);
            $this->Validation->checkRequire('rd_dtc_or_id');
            $this->Validation->checkRequire('rd_dtc_des_id');
            $this->Validation->checkRequire('rd_distance');
            $this->Validation->checkRequire('rd_drive_time');

            $this->Validation->checkUnique('rd_code', 'route_delivery', [
                'rd_id' => $this->getDetailReferenceValue(),
            ]);

            $this->Validation->checkUnique('rd_dtc_des_id', 'route_delivery', [
                'rd_id' => $this->getDetailReferenceValue(),
            ], [
                'rd_dtc_or_id' => $this->getIntParameter('rd_dtc_or_id'),
            ]);

            # for make sure that origin and destination must deferent
            $this->Validation->checkDifferent('rd_dtc_des_id', 'rd_dtc_or_id');
        } else {
            parent::loadValidationRole();
        }
    }

    /**
     * Function to get the Origin Field Set.
     *
     * @return Portlet
     */
    private function getOriginFieldSet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('GnlOrPlt', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(6, 6, 12);
        $portlet->setTitle(Trans::getTruckingWord('origin'));

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 12);

        # set input using getSingleSelectTable
        $distField = $this->Field->getSingleSelectTable('district', 'rd_dtc_or_name', $this->getStringParameter('rd_dtc_or_name'), 'loadSingleSelectTableData');
        $distField->setHiddenField('rd_dtc_or_id', $this->getIntParameter('rd_dtc_or_id'));
        $distField->setTableColumns([
            'dtc_country' => Trans::getTruckingWord('country'),
            'dtc_state' => Trans::getTruckingWord('state'),
            'dtc_city' => Trans::getTruckingWord('city'),
            'dtc_name' => Trans::getTruckingWord('district'),
        ]);
        $distField->setFilters([
            'dtc_country' => Trans::getTruckingWord('country'),
            'dtc_state' => Trans::getTruckingWord('state'),
            'dtc_city' => Trans::getTruckingWord('city'),
            'dtc_name' => Trans::getTruckingWord('district'),
        ]);
        $distField->setAutoCompleteFields([
            'rd_cnt_or_name' => 'dtc_country',
            'rd_stt_or_name' => 'dtc_state',
            'rd_cty_or_name' => 'dtc_city',
        ]);
        $distField->setValueCode('dtc_id');
        $distField->setLabelCode('dtc_name');

        # Create country field
        $countryField = $this->Field->getText('rd_cnt_or_name', $this->getStringParameter('rd_cnt_or_name'));
        $countryField->setReadOnly();

        # Create state field
        $stateField = $this->Field->getText('rd_stt_or_name', $this->getStringParameter('rd_stt_or_name'));
        $stateField->setReadOnly();

        # Create city field
        $cityField = $this->Field->getText('rd_cty_or_name', $this->getStringParameter('rd_cty_or_name'));
        $cityField->setReadOnly();


        $fieldSet->addField(Trans::getTruckingWord('country'), $countryField);
        $fieldSet->addField(Trans::getTruckingWord('state'), $stateField);
        $fieldSet->addField(Trans::getTruckingWord('city'), $cityField);
        $fieldSet->addField(Trans::getTruckingWord('district'), $distField, true);

        # Create district field
        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to get the Destination Field Set.
     *
     * @return Portlet
     */
    private function getDestinationFieldSet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('GnlDesPlt', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(6, 6, 12);
        $portlet->setTitle(Trans::getTruckingWord('destination'));

        # Instantiate FieldSet Object
        $fieldSetDes = new FieldSet($this->Validation);
        $fieldSetDes->setGridDimension(6, 12);
        $distFieldDes = $this->Field->getSingleSelectTable('district', 'rd_dtc_des_name', $this->getStringParameter('rd_dtc_des_name'), 'loadSingleSelectTableData');
        $distFieldDes->setHiddenField('rd_dtc_des_id', $this->getIntParameter('rd_dtc_des_id'));

        # set table district
        $distFieldDes->setTableColumns([
            'dtc_country' => Trans::getTruckingWord('country'),
            'dtc_state' => Trans::getTruckingWord('state'),
            'dtc_city' => Trans::getTruckingWord('city'),
            'dtc_name' => Trans::getTruckingWord('district'),
        ]);
        $distFieldDes->setFilters([
            'dtc_country' => Trans::getTruckingWord('country'),
            'dtc_state' => Trans::getTruckingWord('state'),
            'dtc_city' => Trans::getTruckingWord('city'),
            'dtc_name' => Trans::getTruckingWord('district'),
        ]);
        $distFieldDes->setAutoCompleteFields([
            'rd_cnt_des_name' => 'dtc_country',
            'rd_stt_des_name' => 'dtc_state',
            'rd_cty_des_name' => 'dtc_city',
        ]);
        $distFieldDes->setValueCode('dtc_id');
        $distFieldDes->setLabelCode('dtc_name');

        # Create Country Destination field
        $countryField = $this->Field->getText('rd_cnt_des_name', $this->getStringParameter('rd_cnt_des_name'));
        $countryField->setReadOnly();

        # Create State Destination field
        $stateField = $this->Field->getText('rd_stt_des_name', $this->getStringParameter('rd_stt_des_name'));
        $stateField->setReadOnly();

        # Create City Destination field
        $cityField = $this->Field->getText('rd_cty_des_name', $this->getStringParameter('rd_cty_des_name'));
        $cityField->setReadOnly();

        $fieldSetDes->addField(Trans::getTruckingWord('country'), $countryField);
        $fieldSetDes->addField(Trans::getTruckingWord('state'), $stateField);
        $fieldSetDes->addField(Trans::getTruckingWord('city'), $cityField);
        $fieldSetDes->addField(Trans::getTruckingWord('district'), $distFieldDes, true);

        $portlet->addFieldSet($fieldSetDes);

        return $portlet;
    }

    /**
     * Function to get the Destination Field Set.
     *
     * @return Portlet
     */
    private function getDetailFieldSet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('GnlDetPlt', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(12, 12, 12);
        $portlet->setTitle(Trans::getTruckingWord('details'));

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension();

        # Create field set
        $codeField = $this->Field->getText('rd_code', $this->getStringParameter('rd_code'));
        $fieldSet->addField(Trans::getTruckingWord('code'), $codeField, true);
        $fieldSet->addField(Trans::getTruckingWord('distance'), $this->Field->getNumber('rd_distance', $this->getStringParameter('rd_distance')), true);
        $fieldSet->addField(Trans::getTruckingWord('driveTime'), $this->Field->getNumber('rd_drive_time', $this->getStringParameter('rd_drive_time')), true);
        $fieldSet->addField(Trans::getTruckingWord('toll1'), $this->Field->getNumber('rd_toll_1', $this->getFloatParameter('rd_toll_1')));
        $fieldSet->addField(Trans::getTruckingWord('toll2'), $this->Field->getNumber('rd_toll_2', $this->getFloatParameter('rd_toll_2')));
        $fieldSet->addField(Trans::getTruckingWord('toll3'), $this->Field->getNumber('rd_toll_3', $this->getFloatParameter('rd_toll_3')));
        $fieldSet->addField(Trans::getTruckingWord('toll4'), $this->Field->getNumber('rd_toll_4', $this->getFloatParameter('rd_toll_4')));
        $fieldSet->addField(Trans::getTruckingWord('toll5'), $this->Field->getNumber('rd_toll_5', $this->getFloatParameter('rd_toll_5')));
        $fieldSet->addField(Trans::getTruckingWord('toll6'), $this->Field->getNumber('rd_toll_6', $this->getFloatParameter('rd_toll_6')));
        if ($this->isUpdate()) {
            $fieldSet->addField(Trans::getTruckingWord('active'), $this->Field->getYesNo('rd_active', $this->getStringParameter('rd_active')));
        }

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

}
