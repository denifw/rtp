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
use App\Model\Dao\System\Master\CityDao;

/**
 * Class to control the system of City.
 *
 * @package    app
 * @subpackage Model\Listing\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class City extends AbstractListingModel
{

    /**
     * City constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'cty');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $countryField = $this->Field->getSingleSelect('cnt', 'cty_country', $this->getStringParameter('cty_country'));
        $countryField->setHiddenField('cty_cnt_id', $this->getStringParameter('cty_cnt_id'));
        $countryField->setEnableNewButton(false);
        $countryField->setEnableDetailButton(false);
        $countryField->addClearField('cty_stt_id');
        $countryField->addClearField('cty_state');

        $stateField = $this->Field->getSingleSelect('stt', 'cty_state', $this->getStringParameter('cty_state'));
        $stateField->setHiddenField('cty_stt_id', $this->getStringParameter('cty_stt_id'));
        $stateField->setEnableDetailButton(false);
        $stateField->setEnableNewButton(false);
        $stateField->addOptionalParameterById('stt_cnt_id', 'cty_cnt_id');

        $this->ListingForm->addField(Trans::getWord('country'), $countryField);
        $this->ListingForm->addField(Trans::getWord('state'), $stateField);
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('cty_name', $this->getStringParameter('cty_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('cty_active', $this->getStringParameter('cty_active')));

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
            'cty_country' => Trans::getWord('country'),
            'cty_state' => Trans::getWord('state'),
            'cty_name' => Trans::getWord('name'),
            'cty_active' => Trans::getWord('active'),
        ]);
        # Load the data for City.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['cty_id']);
        $this->ListingTable->setColumnType('cty_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return CityDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return CityDao::loadData(
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

        if ($this->isValidParameter('cty_cnt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('cty.cty_cnt_id', $this->getStringParameter('cty_cnt_id'));
        }
        if ($this->isValidParameter('cty_stt_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('cty.cty_stt_id', $this->getStringParameter('cty_stt_id'));
        }
        if ($this->isValidParameter('cty_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('cty.cty_name', $this->getStringParameter('cty_name'));
        }
        if ($this->isValidParameter('cty_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('cty.cty_active', $this->getStringParameter('cty_active'));
        }

        # return the where query.
        return $wheres;
    }
}
