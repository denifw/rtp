<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\System\Master;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\System\Master\CityDao;
use App\Model\Dao\System\Master\CountryDao;
use App\Model\Dao\System\Master\DistrictDao;
use App\Model\Dao\System\Master\StateDao;

/**
 * Class to handle the creation of detail District page
 *
 * @package    app
 * @subpackage Model\Detail\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class District extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'dtc', 'dtc_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $colVal = [
            'dtc_cnt_id' => $this->getStringParameter('dtc_cnt_id'),
            'dtc_stt_id' => $this->getStringParameter('dtc_stt_id'),
            'dtc_cty_id' => $this->getStringParameter('dtc_cty_id'),
            'dtc_name' => $this->getStringParameter('dtc_name'),
            'dtc_iso' => $this->getStringParameter('dtc_iso'),
            'dtc_active' => $this->getStringParameter('dtc_active', 'Y'),
        ];
        $dtcDao = new DistrictDao();
        $dtcDao->doInsertTransaction($colVal);

        return $dtcDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'dtc_cnt_id' => $this->getStringParameter('dtc_cnt_id'),
            'dtc_stt_id' => $this->getStringParameter('dtc_stt_id'),
            'dtc_cty_id' => $this->getStringParameter('dtc_cty_id'),
            'dtc_name' => $this->getStringParameter('dtc_name'),
            'dtc_iso' => $this->getStringParameter('dtc_iso'),
            'dtc_active' => $this->getStringParameter('dtc_active', 'Y'),
        ];
        $dtcDao = new DistrictDao();
        $dtcDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return DistrictDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() === true) {
            if ($this->isValidParameter('dtc_cnt_id') === true) {
                $country = CountryDao::getByReference($this->getStringParameter('dtc_cnt_id'));
                if (empty($country) === false) {
                    $this->setParameter('dtc_country', $country['cnt_name']);
                } else {
                    $this->setParameter('dtc_cnt_id', '');
                }
            }
            if ($this->isValidParameter('dtc_stt_id') === true) {
                $state = StateDao::getByReference($this->getStringParameter('dtc_stt_id'));
                if (empty($state) === false) {
                    $this->setParameter('dtc_state', $state['stt_name']);
                } else {
                    $this->setParameter('dtc_stt_id', '');
                }
            }
            if ($this->isValidParameter('dtc_cty_id') === true) {
                $city = CityDao::getByReference($this->getStringParameter('dtc_cty_id'));
                if (empty($city) === false) {
                    $this->setParameter('dtc_city', $city['cty_name']);
                } else {
                    $this->setParameter('dtc_cty_id', '');
                }
            }
        }

        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('dtc_cnt_id');
        $this->Validation->checkRequire('dtc_stt_id');
        $this->Validation->checkRequire('dtc_cty_id');
        $this->Validation->checkRequire('dtc_name', 3, 125);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create Fields.
        $countryField = $this->Field->getSingleSelect('cnt', 'dtc_country', $this->getStringParameter('dtc_country'));
        $countryField->setHiddenField('dtc_cnt_id', $this->getStringParameter('dtc_cnt_id'));
        $countryField->setDetailReferenceCode('cnt_id');
        $countryField->addClearField('dtc_stt_id');
        $countryField->addClearField('dtc_state');
        $countryField->addClearField('dtc_cty_id');
        $countryField->addClearField('dtc_city');

        $stateField = $this->Field->getSingleSelect('stt', 'dtc_state', $this->getStringParameter('dtc_state'));
        $stateField->setHiddenField('dtc_stt_id', $this->getStringParameter('dtc_stt_id'));
        $stateField->setDetailReferenceCode('stt_id');
        $stateField->addParameterById('stt_cnt_id', 'dtc_cnt_id', Trans::getWord('country'));
        $stateField->addClearField('dtc_cty_id');
        $stateField->addClearField('dtc_city');

        $cityField = $this->Field->getSingleSelect('cty', 'dtc_city', $this->getStringParameter('dtc_city'));
        $cityField->setHiddenField('dtc_cty_id', $this->getStringParameter('dtc_cty_id'));
        $cityField->setDetailReferenceCode('cty_id');
        $cityField->addParameterById('cty_cnt_id', 'dtc_cnt_id', Trans::getWord('country'));
        $cityField->addParameterById('cty_stt_id', 'dtc_stt_id', Trans::getWord('state'));

        $nameField = $this->Field->getText('dtc_name', $this->getStringParameter('dtc_name'));

        $activeField = $this->Field->getYesNo('dtc_active', $this->getStringParameter('dtc_active'));
        if ($this->User->isUserSystem() === false) {
            $countryField->setReadOnly();
            $countryField->setEnableDetailButton(false);
            $stateField->setReadOnly();
            $stateField->setEnableDetailButton(false);
            $cityField->setReadOnly();
            $cityField->setEnableDetailButton(false);
            $nameField->setReadOnly();
            $activeField->setReadOnly();
        }


        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getWord('country'), $countryField, true);
        $fieldSet->addField(Trans::getWord('state'), $stateField, true);
        $fieldSet->addField(Trans::getWord('city'), $cityField, true);
        $fieldSet->addField(Trans::getWord('name'), $nameField, true);
        $fieldSet->addField(Trans::getWord('isoCode'), $this->Field->getText('dtc_iso', $this->getStringParameter('dtc_iso')));
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $activeField);
        }
        # Create a portlet box.
        $portlet = new Portlet('DtcGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }
}
