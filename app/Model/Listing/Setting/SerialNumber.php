<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Setting;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Relation\OfficeDao;

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
        parent::__construct(get_class($this), 'serialNumber');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('serialCode'), $this->Field->getText('sc_code', $this->getStringParameter('sc_code')));
        if ($this->User->isUserSystem() === false) {
            $officeField = $this->Field->getSelect('sn_of_id', $this->getIntParameter('sn_of_id'));
            $officeField->addOptions(OfficeDao::loadActiveDataByRelation($this->User->getRelId()), 'of_name', 'of_id');
            $this->ListingForm->addField(Trans::getWord('office'), $officeField);
        }
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
            'sc_description' => Trans::getWord('serialCode'),
            'of_name' => Trans::getWord('office'),
            'srv_name' => Trans::getWord('service'),
            'srt_name' => Trans::getWord('serviceTerm'),
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
        # Set Select query;
        $query = 'SELECT count(DISTINCT (sn.sn_id)) AS total_rows
                   FROM serial_number as sn INNER JOIN
                        serial_code as sc ON sn.sn_sc_id = sc.sc_id INNER JOIN
                        system_setting as ss ON sn.sn_ss_id = ss.ss_id LEFT OUTER JOIN
                        service as srv ON sn.sn_srv_id = srv.srv_id LEFT OUTER JOIN
                        service_term as srt ON sn.sn_srt_id = srt.srt_id  LEFT OUTER JOIN
                        office as o ON sn.sn_of_id = o.of_id ';
        # Set where condition.
        $query .= $this->getWhereCondition();

        return $this->loadTotalListingRows($query);
    }


    /**
     * Get query to get the listing data.
     *
     *
     * @return array
     */
    private function loadData(): array
    {
        # Set Select query;
        $query = "SELECT sn.sn_id, sn.sn_sc_id, sc.sc_code, sc.sc_description, sn.sn_ss_id, sn.sn_relation, sn.sn_separator,
                        sn.sn_prefix, sn.sn_yearly, sn.sn_monthly, sn.sn_length, sn.sn_increment, sn.sn_postfix,
                        sn.sn_srv_id, srv.srv_name, sn.sn_srt_id, srt.srt_name, sn.sn_of_id, o.of_name,
                        (CASE WHEN sn.sn_deleted_on is null then sn.sn_active else 'N' END) as sn_active
                  FROM serial_number as sn INNER JOIN
                        serial_code as sc ON sn.sn_sc_id = sc.sc_id INNER JOIN
                        system_setting as ss ON sn.sn_ss_id = ss.ss_id LEFT OUTER JOIN
                        service as srv ON sn.sn_srv_id = srv.srv_id LEFT OUTER JOIN
                        service_term as srt ON sn.sn_srt_id = srt.srt_id  LEFT OUTER JOIN
                        office as o ON sn.sn_of_id = o.of_id";
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY sn.sn_id, sn.sn_sc_id, sc.sc_code, sc.sc_description, sn.sn_ss_id, sn.sn_relation, sn.sn_separator,
                        sn.sn_prefix, sn.sn_yearly, sn.sn_monthly, sn.sn_length, sn.sn_increment, sn.sn_postfix, sn.sn_active,
                        sn.sn_srv_id, srv.srv_name, sn.sn_srt_id, srt.srt_name, sn.sn_of_id, o.of_name, sn.sn_deleted_on';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        }

        return $this->loadDatabaseRow($query);
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

        if ($this->isValidParameter('sc_code') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('sc.sc_code', $this->getStringParameter('sc_code'));
        }
        if ($this->isValidParameter('sn_of_id') === true) {
            $wheres[] = '(sn_of_id = ' . $this->getIntParameter('sn_of_id') . ')';
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

        $wheres[] = '(sn.sn_ss_id = ' . $this->User->getSsId() . ')';

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
