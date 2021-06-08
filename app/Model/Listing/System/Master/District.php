<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\System\Master;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Master\DistrictDao;

/**
 * Class to control the system of District.
 *
 * @package    app
 * @subpackage Model\Listing\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class District extends AbstractListingModel
{

    /**
     * District constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'dtc');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create Fields.
        $countryField = $this->Field->getSingleSelect('cnt', 'dtc_country', $this->getStringParameter('dtc_country'));
        $countryField->setHiddenField('dtc_cnt_id', $this->getStringParameter('dtc_cnt_id'));
        $countryField->setEnableNewButton(false);
        $countryField->setEnableDetailButton(false);
        $countryField->addClearField('dtc_stt_id');
        $countryField->addClearField('dtc_state');
        $countryField->addClearField('dtc_cty_id');
        $countryField->addClearField('dtc_city');

        $stateField = $this->Field->getSingleSelect('stt', 'dtc_state', $this->getStringParameter('dtc_state'));
        $stateField->setHiddenField('dtc_stt_id', $this->getStringParameter('dtc_stt_id'));
        $stateField->setEnableDetailButton(false);
        $stateField->setEnableNewButton(false);
        $stateField->addOptionalParameterById('stt_cnt_id', 'dtc_cnt_id');
        $stateField->addClearField('dtc_cty_id');
        $stateField->addClearField('dtc_city');

        $cityField = $this->Field->getSingleSelect('cty', 'dtc_city', $this->getStringParameter('dtc_city'));
        $cityField->setHiddenField('dtc_cty_id', $this->getStringParameter('dtc_cty_id'));
        $cityField->setEnableNewButton(false);
        $cityField->setEnableDetailButton(false);
        $cityField->addOptionalParameterById('cty_cnt_id', 'dtc_cnt_id');
        $cityField->addOptionalParameterById('cty_stt_id', 'dtc_stt_id');

        $this->ListingForm->addField(Trans::getWord('country'), $countryField);
        $this->ListingForm->addField(Trans::getWord('state'), $stateField);
        $this->ListingForm->addField(Trans::getWord('city'), $cityField);
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('dtc_name', $this->getStringParameter('dtc_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('dtc_active', $this->getStringParameter('dtc_active')));
        $this->ListingForm->setGridDimension(4);

    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        # set header column table
        $this->ListingTable->setHeaderRow([
            'dtc_country' => Trans::getWord('country'),
            'dtc_state' => Trans::getWord('state'),
            'dtc_city' => Trans::getWord('city'),
            'dtc_name' => Trans::getWord('name'),
            'dtcc_code' => Trans::getWord('code'),
            'dtc_active' => Trans::getWord('active'),
        ]);
        # Load the data for District.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['dtc_id']);
        $this->ListingTable->setColumnType('dtc_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return DistrictDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return DistrictDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
    }

    /**
     * Function to get the where condition.
     *
     * @return array
     */
    private function getWhereCondition(): array
    {
        # Set where conditions
        $wheres = [];

        if ($this->isValidParameter('dtc_cnt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_cnt_id', $this->getStringParameter('dtc_cnt_id'));
        }
        if ($this->isValidParameter('dtc_stt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_stt_id', $this->getStringParameter('dtc_stt_id'));
        }
        if ($this->isValidParameter('dtc_cty_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_cty_id', $this->getStringParameter('dtc_cty_id'));
        }
        if ($this->isValidParameter('dtc_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dtc.dtc_name', $this->getStringParameter('dtc_name'));
        }
        if ($this->isValidParameter('dtc_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dtc.dtc_active', $this->getStringParameter('dtc_active'));
        }

        return $wheres;
    }
}
