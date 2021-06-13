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
use App\Model\Dao\System\Access\UserGroupDao;

/**
 * Class to control the system of UserGroup.
 *
 * @package    app
 * @subpackage Model\Listing\System\Access
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class UserGroup extends AbstractListingModel
{

    /**
     * UserGroup constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'usg');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $ssField = $this->Field->getSingleSelect('ss', 'usg_system', $this->getStringParameter('usg_system'));
        $ssField->setHiddenField('usg_ss_id', $this->getStringParameter('usg_ss_id'));
        $ssField->setEnableNewButton(false);
        $this->ListingForm->addField(Trans::getWord('systemName'), $ssField);
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('usg_name', $this->getStringParameter('usg_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('usg_active', $this->getStringParameter('usg_active')));
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
            'usg_name' => Trans::getWord('name'),
            'ss_relation' => Trans::getWord('systemName'),
            'usg_active' => Trans::getWord('active')
        ]);
        # Load the data for UserGroup.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['usg_id']);
        $this->ListingTable->setColumnType('usg_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return UserGroupDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return UserGroupDao::loadData(
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
        if ($this->isValidParameter('usg_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('usg.usg_name', $this->getStringParameter('usg_name'));
        }
        if ($this->isValidParameter('usg_ss_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('usg.usg_ss_id', $this->getStringParameter('usg_ss_id'));
        } else {
            $wheres[] = SqlHelper::generateNullCondition('usg.usg_ss_id');
        }
        if ($this->isValidParameter('usg_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('usg.usg_active', $this->getStringParameter('usg_active'));
        }
        # return the where query.
        return $wheres;
    }
}
