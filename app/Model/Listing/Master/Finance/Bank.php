<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\Master\Finance;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Master\Finance\BankDao;

/**
 * Class to control the system of Bank.
 *
 * @package    app
 * @subpackage Model\Listing\Master\Finance
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Bank extends AbstractListingModel
{

    /**
     * Bank constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'bank');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('shortName'),$this->Field->getText('bn_short_name',$this->getStringParameter('bn_short_name')));
        $this->ListingForm->addField(Trans::getWord('name'),$this->Field->getText('bn_name',$this->getStringParameter('bn_name')));
        $this->ListingForm->addField(Trans::getWord('active'),$this->Field->getYesNo('bn_active',$this->getStringParameter('bn_active')));
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
            'bn_short_name' => Trans::getWord('shortName'),
            'bn_name' => Trans::getWord('name'),
            'bn_active' => Trans::getWord('active'),
        ]);
        # Load the data for Bank.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('bn_active', 'yesno');
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['bn_id']);

    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return BankDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return BankDao::loadData(
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

        if ($this->isValidParameter('bn_short_name')) {
            $wheres[] = SqlHelper::generateLikeCondition('bn_short_name', $this->getStringParameter('bn_short_name'));
        }
        if ($this->isValidParameter('bn_name')) {
            $wheres[] = SqlHelper::generateLikeCondition('bn_name', $this->getStringParameter('bn_name'));
        }
        if ($this->isValidParameter('bn_active')) {
            $wheres[] = '(bn_active = \'' . $this->getStringParameter('bn_active') . '\')';
        }

        # return the list where condition.
        return $wheres;
    }
}
