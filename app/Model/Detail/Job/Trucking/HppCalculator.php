<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\Job\Trucking;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail HppCalculator page
 *
 * @package    app
 * @subpackage Model\Detail\Job\test
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class HppCalculator extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'hpp', 'ss_id');
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

    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return [];

    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        $this->Tab->addPortlet('general', $this->getGeneralPortletRoute());
        $this->Tab->addPortlet('general', $this->getGeneralPortletEquipment());
        $this->Tab->addPortlet('general', $this->getGeneralPortletResult());

        $this->setEnableCloseButton(false);
        $this->setDisableInsert();

    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {

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
        $portlet = new Portlet('HppCalPtl', "Parameters");
        $portlet->setGridDimension(12, 12);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(3, 3);

        #Create Route Field
        $rtField = $this->Field->getSingleSelectTable('rd', 'route_code', $this->getStringParameter('route_code'), 'loadSingleSelectTableData');
        $rtField->setHiddenField('rd_dtc_or_id', $this->getIntParameter('rd_dtc_or_id'));
        $rtField->setTableColumns([
            'rd_code' => Trans::getWord('route'),
            'rd_dtc_name' => Trans::getWord('origin'),
            'dtc_des_rd_name' => Trans::getWord('destination'),
            'rd_distance' => Trans::getWord('distance'),
            'rd_drive_time' => Trans::getWord('drivingTime'),
        ]);
        $rtField->setFilters([
            'rd_code' => Trans::getWord('route')
        ]);
        $rtField->setAutoCompleteFields([
            'origin' => 'rd_dtc_name',
            'destination' => 'dtc_des_rd_name',
            'distance' => 'rd_distance',
            'test' => 'rd_distance',
            'driving_time' => 'rd_drive_time'
        ]);
        $rtField->setValueCode('rd_id');
        $rtField->setLabelCode('rd_code');

        #Create Equipment Field
        $eqField = $this->Field->getSingleSelectTable('equipment', 'eq_code', $this->getStringParameter('eq_code'), 'loadSingleSelectTableData');
        $eqField->setHiddenField('eq_id', $this->getIntParameter('eq_id'));
        $eqField->setTableColumns([
            'eq_number' => Trans::getWord('number'),
            'eq_eg_name' => Trans::getWord('group'),
            'eq_sty_name' => Trans::getWord('brand'),
            'eq_license_plate' => Trans::getWord('truckPlate'),
            'eq_fuel_consume' => Trans::getWord('fuelKmLtr'),
        ]);
        $eqField->setFilters([
            'eq_number' => Trans::getWord('number'),
            'eq_eg_name' => Trans::getWord('group'),
            'eq_sty_name' => Trans::getWord('brand'),
            'eq_license_plate' => Trans::getWord('truckPlate'),
        ]);
        $eqField->setAutoCompleteFields([
            'group' => 'eq_eg_name',
            'brand' => 'eq_sty_name',
            'truckPlate' => 'eq_license_plate',
            'fuelConsume' => 'eq_fuel_consume',
        ]);
        $eqField->setValueCode('eq_id');
        $eqField->setLabelCode('eq_number');

        # Add field to field set
        $fieldSet->addField(Trans::getWord('route'), $rtField, true);
        $fieldSet->addField(Trans::getWord('equipment'), $eqField, true);
        $fieldSet->addField(Trans::getWord('bbmLtr'), $this->Field->getText('bbm_ltr', $this->getStringParameter('bbm_ltr')));
        $fieldSet->addField(Trans::getWord('driverFee'), $this->Field->getText('driver_fee', $this->getStringParameter('driver_fee')));
        $fieldSet->addHiddenField($this->Field->getHidden('pemisahRibuan',$this->User->Settings->getThousandSeparator()));

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortletRoute(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('RoutePtl', "Route");
        $portlet->setGridDimension(6, 6);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        #Create Origin Field
        $orgField = $this->Field->getText('origin', $this->getStringParameter('origin'));
        $orgField->setReadOnly();

        #Create Destination Field
        $dsttField = $this->Field->getText('destination', $this->getStringParameter('destination'));
        $dsttField->setReadOnly();

        #Create Distance Field
        $dstField = $this->Field->getText('distance', $this->getStringParameter('distance'));
        $dstField->setReadOnly();

        #Create Driving Time Field
        $dtField = $this->Field->getText('driving_time', $this->getStringParameter('driving_time'));
        $dtField->setReadOnly();

        # Add field to field set
        $fieldSet->addField(Trans::getWord('origin'), $orgField);
        $fieldSet->addField(Trans::getWord('destination'), $dsttField);
        $fieldSet->addField(Trans::getWord('distance'), $dstField);
        $fieldSet->addField(Trans::getWord('drivingTime'), $dtField);


        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortletEquipment(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('EqPtl', "Equipment");
        $portlet->setGridDimension(6, 6);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        #Create group Field
        $grField = $this->Field->getText('group', $this->getStringParameter('group'));
        $grField->setReadOnly();

        #Create brand Field
        $brField = $this->Field->getText('brand', $this->getStringParameter('brand'));
        $brField->setReadOnly();

        #Create Truck Plate Field
        $tpField = $this->Field->getText('truckPlate', $this->getStringParameter('truckPlate'));
        $tpField->setReadOnly();

        #Create Fuel Km/Ltr Field
        $fkmField = $this->Field->getText('fuelConsume', $this->getStringParameter('fuelConsume'));
        $fkmField->setReadOnly();

        # Add field to field set
        $fieldSet->addField(Trans::getWord('group'), $grField);
        $fieldSet->addField(Trans::getWord('brand'), $brField);
        $fieldSet->addField(Trans::getWord('truckPlate'), $tpField);
        $fieldSet->addField(Trans::getWord('fuelKmLtr'), $fkmField);


        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortletResult(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('RstPtl', "Result");
        $portlet->setGridDimension(12, 12);
        $portlet->addText('<h3 style="text-align: center;">IDR <label id="rsl"></label></h3>');

        return $portlet;
    }

}
