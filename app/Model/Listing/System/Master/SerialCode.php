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
use App\Model\Dao\System\Master\SerialCodeDao;

/**
 * Class to control the system of SerialCode.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SerialCode extends AbstractListingModel
{

    /**
     * SerialCode constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'sc');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('sc_code', $this->getStringParameter('sc_code')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('sc_active', $this->getStringParameter('sc_active')));
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
            'sc_code' => Trans::getWord('code'),
            'sc_description' => Trans::getWord('description'),
            'sc_active' => Trans::getWord('active'),
        ]);
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['sc_id']);
        $this->ListingTable->setColumnType('sc_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return SerialCodeDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return SerialCodeDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable()
        );
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

        if ($this->isValidParameter('sc_code') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('sc_code', $this->getStringParameter('sc_code'));
        }
        if ($this->isValidParameter('sc_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('sc_active', $this->getStringParameter('sc_active'));
        }
        return $wheres;
    }
}
