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
use App\Model\Dao\System\Master\StateDao;

/**
 * Class to handle the creation of detail City page
 *
 * @package    app
 * @subpackage Model\Detail\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class City extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'cty', 'cty_id');
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
            'cty_cnt_id' => $this->getStringParameter('cty_cnt_id'),
            'cty_stt_id' => $this->getStringParameter('cty_stt_id'),
            'cty_name' => $this->getStringParameter('cty_name'),
            'cty_iso' => $this->getStringParameter('cty_iso'),
            'cty_active' => $this->getStringParameter('cty_active', 'Y')
        ];
        $ctyDao = new CityDao();
        $ctyDao->doInsertTransaction($colVal);

        return $ctyDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'cty_cnt_id' => $this->getStringParameter('cty_cnt_id'),
            'cty_stt_id' => $this->getStringParameter('cty_stt_id'),
            'cty_name' => $this->getStringParameter('cty_name'),
            'cty_iso' => $this->getStringParameter('cty_iso'),
            'cty_active' => $this->getStringParameter('cty_active', 'Y')
        ];
        $ctyDao = new CityDao();
        $ctyDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return CityDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() === true) {
            if ($this->isValidParameter('cty_cnt_id') === true) {
                $cnt = CountryDao::getByReference($this->getStringParameter('cty_cnt_id'));
                if (empty($cnt) === false) {
                    $this->setParameter('cty_country', $cnt['cnt_name']);
                } else {
                    $this->setParameter('cty_cnt_id', '');
                }
            }
            if ($this->isValidParameter('cty_stt_id') === true) {
                $state = StateDao::getByReference($this->getStringParameter('cty_stt_id'));
                if (empty($state) === false) {
                    $this->setParameter('cty_state', $state['stt_name']);
                } else {
                    $this->setParameter('cty_stt_id', '');
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
        $this->Validation->checkRequire('cty_cnt_id');
        $this->Validation->checkRequire('cty_stt_id');
        $this->Validation->checkRequire('cty_name', 3, 125);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create Fields.
        $countryField = $this->Field->getSingleSelect('cnt', 'cty_country', $this->getStringParameter('cty_country'));
        $countryField->setHiddenField('cty_cnt_id', $this->getStringParameter('cty_cnt_id'));
        $countryField->setDetailReferenceCode('cnt_id');
        $countryField->addClearField('cty_stt_id');
        $countryField->addClearField('cty_state');

        $stateField = $this->Field->getSingleSelect('stt', 'cty_state', $this->getStringParameter('cty_state'));
        $stateField->setHiddenField('cty_stt_id', $this->getStringParameter('cty_stt_id'));
        $stateField->setDetailReferenceCode('stt_id');
        $stateField->addParameterById('stt_cnt_id', 'cty_cnt_id', Trans::getWord('country'));

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getWord('country'), $countryField, true);
        $fieldSet->addField(Trans::getWord('state'), $stateField, true);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('cty_name', $this->getStringParameter('cty_name')), true);
        $fieldSet->addField(Trans::getWord('isoCode'), $this->Field->getText('cty_iso', $this->getStringParameter('cty_iso')));
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('cty_active', $this->getStringParameter('cty_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('CtyGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }
}
