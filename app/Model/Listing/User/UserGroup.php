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
        parent::__construct(get_class($this), 'userGroup');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $ssField = $this->Field->getSelect('usg_ss_id', $this->getIntParameter('usg_ss_id'));
        $ssField->addOptions(SystemSettingDao::loadAllData(), 'ss_relation', 'ss_id');
        $this->ListingForm->addField(Trans::getWord('systemSetting'), $ssField);
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
            'ss_relation' => Trans::getWord('systemSetting'),
            'usg_active' => Trans::getWord('active')
        ]);
        # Load the data for UserGroup.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['usg_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
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
        # Set Select query;
        $query = 'SELECT count(DISTINCT (usg.usg_id)) AS total_rows
                   FROM user_group as usg LEFT OUTER JOIN
                    system_setting as ss ON usg.usg_ss_id = ss.ss_id';
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
        $query = 'SELECT usg.usg_id, usg.usg_name, usg.usg_active, usg.usg_ss_id, ss.ss_relation 
                    FROM user_group as usg LEFT OUTER JOIN
                    system_setting as ss ON usg.usg_ss_id = ss.ss_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY usg.usg_id, usg.usg_name, usg.usg_active, usg.usg_ss_id, ss.ss_relation';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY usg.usg_ss_id, usg.usg_name, usg.usg_id';
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
        if ($this->isValidParameter('usg_name') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('usg.usg_name', $this->getStringParameter('usg_name'));
        }
        if ($this->isValidParameter('usg_ss_id') === true) {
            $wheres[] = '(usg.usg_ss_id = ' . $this->getIntParameter('usg_ss_id') . ')';
        } else {
            $wheres[] = '(usg.usg_ss_id IS NULL)';
        }
        if ($this->isValidParameter('usg_active') === true) {
            $wheres[] = '(usg.usg_active = \'' . $this->getStringParameter('usg_active') . '\')';
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
