<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\User;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\SystemSettingDao;

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
        parent::__construct(get_class($this), 'user');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $ssField = $this->Field->getSelect('ss_id', $this->getIntParameter('ss_id'));
        $ssField->addOptions(SystemSettingDao::loadAllData(), 'ss_relation', 'ss_id');
        $this->ListingForm->addField(Trans::getWord('systemSetting'), $ssField);
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('us_name', $this->getStringParameter('us_name')));
        $this->ListingForm->addField(Trans::getWord('username'), $this->Field->getText('us_username', $this->getStringParameter('us_username')));
        $this->ListingForm->addField(Trans::getWord('confirm'), $this->Field->getYesNo('us_confirm', $this->getStringParameter('us_confirm')));
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
            'us_confirm' => Trans::getWord('confirm'),
            'us_allow_mail' => Trans::getWord('allowMail'),
            'us_active' => Trans::getWord('active'),
        ]);
        # Load the data for User.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['us_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['us_id']);
        $this->ListingTable->setColumnType('us_confirm', 'yesno');
        $this->ListingTable->setColumnType('us_system', 'yesno');
        $this->ListingTable->setColumnType('us_allow_mail', 'yesno');
        $this->ListingTable->setColumnType('us_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (us_id)) AS total_rows
                   FROM users';
        # Set where condition.
        $query .= $this->getWhereCondition();

        return $this->loadTotalListingRows($query);
    }


    /**
     * Get query to get the listing data.
     *
     * @param array $outFields To store the out field from selection data.
     *
     * @return array
     */
    private function loadData(array $outFields): array
    {
        # Set Select query;
        $query = 'SELECT us_id, us_name, us_username, us_active, us_allow_mail, us_system, us_confirm
                    FROM users';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY us_id, us_name, us_username, us_active, us_allow_mail, us_system, us_confirm';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY us_name';
        }

        return $this->loadDatabaseRow($query, $outFields);
    }

    /**
     * Function to get the where condition.
     *
     * @return string
     */
    private function getWhereCondition(): string
    {
        # Set where conditions
        $wheres = [];

        if ($this->isValidParameter('ss_id') === true) {
            $wheres[] = '(us_id IN (SELECT ump_us_id
                                    FROM user_mapping 
                                    WHERE (ump_deleted_on IS NULL) AND (ump_ss_id = ' . $this->getIntParameter('ss_id') . ')
                                    GROUP BY ump_us_id ))';
        }
        if ($this->isValidParameter('us_name') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('us_name', $this->getStringParameter('us_name'));
        }
        if ($this->isValidParameter('us_username') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('us_username', $this->getStringParameter('us_username'));
        }
        if ($this->isValidParameter('us_active') === true) {
            $wheres[] = '(us_active = \'' . $this->getStringParameter('us_active') . '\')';
        }
        if ($this->isValidParameter('us_confirm') === true) {
            $wheres[] = '(us_confirm = \'' . $this->getStringParameter('us_confirm') . '\')';
        }

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
