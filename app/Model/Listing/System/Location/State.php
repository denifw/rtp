<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\System\Location;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Location\StateDao;

/**
 * Class to control the system of State.
 *
 * @package    app
 * @subpackage Model\Listing\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class State extends AbstractListingModel
{

    /**
     * State constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'state');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $countryField = $this->Field->getSingleSelect('country', 'stt_country', $this->getStringParameter('stt_country'));
        $countryField->setHiddenField('stt_cnt_id', $this->getIntParameter('stt_cnt_id'));
        $countryField->setEnableDetailButton(false);
        $countryField->setEnableNewButton(false);
        $this->ListingForm->addField(Trans::getWord('country'), $countryField);
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('stt_name', $this->getStringParameter('stt_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('stt_active', $this->getStringParameter('stt_active')));
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
            'stt_country' => Trans::getWord('country'),
            'stt_name' => Trans::getWord('name'),
            'stt_active' => Trans::getWord('active'),
        ]);
        # Load the data for State.
        $this->ListingTable->addRows($this->loadData());
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['stt_id']);
        }
        $this->ListingTable->setColumnType('stt_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return StateDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return StateDao::loadData(
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

        if ($this->isValidParameter('stt_cnt_id') === true) {
            $wheres[] = '(stt.stt_cnt_id = ' . $this->getIntParameter('stt_cnt_id') . ')';
        }
        if ($this->isValidParameter('stt_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('stt.stt_name', $this->getStringParameter('stt_name'));
        }
        if ($this->isValidParameter('stt_active') === true) {
            $wheres[] = "(stt.stt_active = '" . $this->getStringParameter('stt_active') . "')";
        }


        # return the where query.
        return $wheres;
    }
}
