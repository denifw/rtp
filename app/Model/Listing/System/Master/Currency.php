<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Listing\System\Master;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Master\CurrencyDao;

/**
 * Class to manage the creation of the listing Currency page.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Currency extends AbstractListingModel
{

    /**
     * Currency constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'cur');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $countryField = $this->Field->getSingleSelect('cnt', 'cnt_name', $this->getStringParameter('cnt_name'));
        $countryField->setHiddenField('cur_cnt_id', $this->getStringParameter('cur_cnt_id'));
        $countryField->setEnableNewButton(false);
        $this->ListingForm->addField(Trans::getWord('country'), $countryField);
        $this->ListingForm->addField(Trans::getWord('currency'), $this->Field->getText('cur_name', $this->getStringParameter('cur_name')));
        $this->ListingForm->addField(Trans::getWord('isoCode'), $this->Field->getText('cur_iso', $this->getStringParameter('cur_iso')));
    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        # set header column table
        $this->ListingTable->setHeaderRow(
            [
                'cur_name' => Trans::getWord('currency'),
                'cur_iso' => Trans::getWord('isoCode'),
                'cnt_name' => Trans::getWord('country'),
                'cur_active' => Trans::getWord('active'),
            ]
        );
        # Load the data for Currency.
        $this->ListingTable->addRows($this->loadData());
        # Add special settings to the table
        $this->ListingTable->setColumnType('cur_active', 'yesno');
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['cur_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return CurrencyDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return CurrencyDao::loadData(
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
        if ($this->isValidParameter('cur_name')) {
            $wheres[] = SqlHelper::generateLikeCondition('cur_name', $this->getStringParameter('cur_name'));
        }
        if ($this->isValidParameter('cur_iso')) {
            $wheres[] = SqlHelper::generateStringCondition('cur.cur_iso', $this->getStringParameter('cur_iso'), '=', 'up');
        }
        if ($this->isValidParameter('cur_cnt_id')) {
            $wheres[] = SqlHelper::generateStringCondition('cur.cur_cnt_id', $this->getStringParameter('cur_cnt_id'));
        }
        return $wheres;
    }
}
