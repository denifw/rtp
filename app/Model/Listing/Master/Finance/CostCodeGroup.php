<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author     Deni Firdaus Waruwu<deni.fw@spada-informatika.com>
 * @copyright  2020 spada-informatika.com
 */

namespace App\Model\Listing\Master\Finance;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Master\Finance\CostCodeGroupDao;

/**
 * Class to manage the creation of the listing CostCode page.
 *
 * @package    app
 * @subpackage Model\Listing\Master\Finance
 * @author     Deni Firdaus Waruwu<deni.fw@spada-informatika.com>
 * @copyright  2020 spada-informatika.com
 */
class CostCodeGroup extends AbstractListingModel
{

    /**
     * CostCode constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ccg');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {

        #Type
        $typeField = $this->Field->getSelect('ccg_type', $this->getStringParameter('ccg_type'));
        $typeField->addOption('Sales', 'S');
        $typeField->addOption('Purchase', 'P');
        $typeField->addOption('Reimburse', 'R');

        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('ccg_code', $this->getStringParameter('ccg_code')));
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('ccg_name', $this->getStringParameter('ccg_name')));
        $this->ListingForm->addField(Trans::getWord('type'), $typeField);
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('cc_active', $this->getStringParameter('cc_active')));
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
                'ccg_code' => Trans::getWord('code'),
                'ccg_name' => Trans::getWord('name'),
                'ccg_type_name' => Trans::getWord('type'),
                'ccg_active' => Trans::getWord('active'),
            ]
        );
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        # Add special settings to the table
        $this->ListingTable->setColumnType('ccg_active', 'yesno');
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['ccg_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return CostCodeGroupDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = CostCodeGroupDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable()
        );
        $results = [];
        foreach ($data as $row) {
            if ($row['ccg.ccg_type'] === 'S') {
                $row['ccg.ccg_type_name'] = Trans::getWord('sales');
            } elseif ($row['ccg.ccg_type'] === 'P') {
                $row['ccg.ccg_type_name'] = Trans::getWord('purchase');
            } elseif ($row['ccg.ccg_type'] === 'D') {
                $row['ccg.ccg_type_name'] = Trans::getWord('deposit');
            } else {
                $row['ccg.ccg_type_name'] = Trans::getWord('reimburse');
            }
            $results[] = $row;
        }
        return $results;
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
        if ($this->isValidParameter('ccg_code')) {
            $wheres[] = SqlHelper::generateLikeCondition('ccg_code', $this->getStringParameter('ccg_code'));
        }
        if ($this->isValidParameter('ccg_name')) {
            $wheres[] = SqlHelper::generateLikeCondition('ccg_name', $this->getStringParameter('ccg_name'));
        }
        if ($this->isValidParameter('ccg_type')) {
            $wheres[] = SqlHelper::generateStringCondition('ccg_type', $this->getStringParameter('ccg_type'));
        }
        if ($this->isValidParameter('ccg_active')) {
            $wheres[] = SqlHelper::generateStringCondition('ccg_active', $this->getStringParameter('ccg_active'));
        }
        $wheres[] = SqlHelper::generateStringCondition('ccg_ss_id', $this->User->getSsId());
        return $wheres;
    }
}
