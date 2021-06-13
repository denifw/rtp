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
use App\Model\Dao\System\Access\UsersDao;

/**
 * Class to control the system of User.
 *
 * @package    app
 * @subpackage Model\Listing\System\Access
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class User extends AbstractListingModel
{

    /**
     * User constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'us');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $ssField = $this->Field->getSingleSelect('ss', 'us_system_settings', $this->getStringParameter('us_system_settings'));
        $ssField->setHiddenField('us_ss_id', $this->getStringParameter('us_ss_id'));
        $ssField->setEnableNewButton(false);
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('us_name', $this->getStringParameter('us_name')));
        $this->ListingForm->addField(Trans::getWord('username'), $this->Field->getText('us_username', $this->getStringParameter('us_username')));
        $this->ListingForm->addField(Trans::getWord('systemName'), $ssField);
        $this->ListingForm->addField(Trans::getWord('verified'), $this->Field->getYesNo('us_confirm', $this->getStringParameter('us_confirm')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('us_active', $this->getStringParameter('us_active')));
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
            'us_name' => Trans::getWord('name'),
            'us_username' => Trans::getWord('email'),
            'us_system' => Trans::getWord('system'),
            'us_confirm' => Trans::getWord('verified'),
            'us_active' => Trans::getWord('active'),
        ]);
        # Load the data for User.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['us_id']);
        $this->ListingTable->setColumnType('us_confirm', 'yesno');
        $this->ListingTable->setColumnType('us_system', 'yesno');
        $this->ListingTable->setColumnType('us_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return UsersDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return UsersDao::loadData(
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
        if ($this->isValidParameter('us_ss_id') === true) {
            $wheres[] = '(us_id IN (SELECT ump_us_id
                                    FROM user_mapping
                                    WHERE ' . SqlHelper::generateNullCondition('ump_deleted_on') .
                ' AND ' . SqlHelper::generateStringCondition('ump_ss_id', $this->getStringParameter('us_ss_id')) .
                ' GROUP BY ump_us_id ))';
        }
        if ($this->isValidParameter('us_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('us_name', $this->getStringParameter('us_name'));
        }
        if ($this->isValidParameter('us_username') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('us_username', $this->getStringParameter('us_username'));
        }
        if ($this->isValidParameter('us_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('us_active', $this->getStringParameter('us_active'));
        }
        if ($this->isValidParameter('us_confirm') === true) {
            $wheres[] = SqlHelper::generateStringCondition('us_confirm', $this->getStringParameter('us_confirm'));
        }
        return $wheres;
    }
}
