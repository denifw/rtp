<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\System\Access;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Access\SerialNumberDao;

/**
 * Class to control the system of SerialNumber.
 *
 * @package    app
 * @subpackage Model\Listing\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SerialNumber extends AbstractListingModel
{

    /**
     * SerialNumber constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'sn');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # System Field
        $ssField = $this->Field->getSingleSelect('ss', 'sn_system', $this->getStringParameter('ss_system'));
        $ssField->setHiddenField('sn_ss_id', $this->getStringParameter('sn_ss_id'));
        $ssField->setEnableNewButton(false);
        $this->ListingForm->addField(Trans::getWord('systemName'), $ssField);
        $this->ListingForm->addField(Trans::getWord('serialCode'), $this->Field->getText('sc_code', $this->getStringParameter('sc_code')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('sn_active', $this->getStringParameter('sn_active')));

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
            'sn_system' => Trans::getWord('systemName'),
            'sn_sc_description' => Trans::getWord('serialCode'),
            'of_name' => Trans::getWord('office'),
            'sn_relation' => Trans::getWord('relation'),
            'sn_prefix' => Trans::getWord('prefix'),
            'sn_length' => Trans::getWord('length'),
            'sn_increment' => Trans::getWord('increment'),
            'sn_postfix' => Trans::getWord('postfix'),
            'sn_yearly' => Trans::getWord('yearly'),
            'sn_monthly' => Trans::getWord('monthly'),
            'sn_active' => Trans::getWord('active'),
        ]);
        # Load the data for SerialNumber.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setColumnType('sn_relation', 'yesno');
        $this->ListingTable->setColumnType('sn_yearly', 'yesno');
        $this->ListingTable->setColumnType('sn_monthly', 'yesno');
        $this->ListingTable->setColumnType('sn_active', 'yesno');
        $this->ListingTable->setColumnType('sn_increment', 'integer');
        $this->ListingTable->setColumnType('sn_length', 'integer');
        $this->ListingTable->addColumnAttribute('sn_prefix', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('sn_postfix', 'style', 'text-align: center;');
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['sn_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return SerialNumberDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     *
     * @return array
     */
    private function loadData(): array
    {
        return SerialNumberDao::loadData(
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

        if ($this->isValidParameter('sc_code') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('sc.sc_code', $this->getStringParameter('sc_code'));
        }
        if ($this->isValidParameter('sn_ss_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('sn.sn_ss_id', $this->getStringParameter('sn_ss_id'));
        }
        if ($this->isValidParameter('sn_active') === true) {
            $value = $this->getStringParameter('sn_active');
            $active = SqlHelper::generateStringCondition('sn.sn_active', $value);
            if ($value === 'Y') {
                $wheres[] = $active;
                $wheres[] = SqlHelper::generateNullCondition('sn.sn_deleted_on');
            } else {
                $wheres[] = '(' . $active . ' OR ' . SqlHelper::generateNullCondition('sn.sn_deleted_on', false) . ')';

            }
        }
        return $wheres;
    }
}
