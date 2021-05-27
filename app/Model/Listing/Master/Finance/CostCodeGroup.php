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

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

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
        parent::__construct(get_class($this), 'costCodeGroup');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {

        $srvField = $this->Field->getSingleSelect('service', 'ccg_service', $this->getStringParameter('ccg_service'));
        $srvField->setHiddenField('ccg_srv_id', $this->getIntParameter('ccg_srv_id'));
        $srvField->addParameter('ssr_ss_id', $this->User->getSsId());
        $srvField->setEnableDetailButton(false);
        $srvField->setEnableNewButton(false);

        #Type
        $typeField = $this->Field->getSelect('ccg_type', $this->getStringParameter('ccg_type'));
        $typeField->addOption('Sales', 'S');
        $typeField->addOption('Purchase', 'P');
        $typeField->addOption('Reimburse', 'R');

        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('ccg_code', $this->getStringParameter('ccg_code')));
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('ccg_name', $this->getStringParameter('ccg_name')));
        $this->ListingForm->addField(Trans::getWord('service'), $srvField);
        $this->ListingForm->addField(Trans::getWord('type'), $typeField);
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('cc_active', $this->getStringParameter('cc_active')));
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
        $this->ListingTable->setHeaderRow(
            [
                'ccg_code' => Trans::getWord('code'),
                'ccg_name' => Trans::getWord('name'),
                'srv_name' => Trans::getWord('service'),
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
        # Set Select query;
        $query = 'SELECT count(DISTINCT (ccg.ccg_id)) AS total_rows
                   FROM cost_code_group AS ccg LEFT OUTER JOIN
                       service AS srv ON ccg.ccg_srv_id = srv.srv_id';
        # Set where condition.
        $query .= $this->getWhereCondition();

        return $this->loadTotalListingRows($query);
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        # Set Select query;
        $query = "SELECT ccg.ccg_id, ccg.ccg_ss_id, ccg.ccg_code, ccg.ccg_name, ccg.ccg_srv_id, srv.srv_name, ccg.ccg_active,
                    (CASE WHEN ccg.ccg_type = 'S' THEN 'Sales' WHEN ccg.ccg_type = 'P' THEN 'Purchase' WHEN ccg.ccg_type = 'D' THEN 'Deposit' ELSE 'Reimburse' END) AS ccg_type_name
                  FROM cost_code_group AS ccg LEFT OUTER JOIN
                       service AS srv ON ccg.ccg_srv_id = srv.srv_id ";
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY ccg.ccg_id, ccg.ccg_ss_id, ccg.ccg_code, ccg.ccg_name, ccg.ccg_srv_id, srv.srv_name, ccg.ccg_active, ccg.ccg_type';
        # Set order by query.
        $query .= ' ORDER BY ccg.ccg_code, ccg.ccg_name, ccg.ccg_id';

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
        if ($this->isValidParameter('ccg_code')) {
            $wheres[] = StringFormatter::generateLikeQuery('ccg.ccg_code', $this->getStringParameter('ccg_code'));
        }
        if ($this->isValidParameter('ccg_name')) {
            $wheres[] = StringFormatter::generateLikeQuery('ccg.ccg_name', $this->getStringParameter('ccg_name'));
        }
        if ($this->isValidParameter('ccg_type')) {
            $wheres[] = '(ccg.ccg_type = \'' . $this->getStringParameter('ccg_type') . '\')';
        }
        if ($this->isValidParameter('ccg_active')) {
            $wheres[] = "(ccg.ccg_active = '" . $this->getStringParameter('ccg_active') . "')";
        }
        if ($this->isValidParameter('ccg_srv_id')) {
            $wheres[] = '(ccg.ccg_srv_id = ' . $this->getIntParameter('ccg_srv_id') . ')';
        }
        $wheres[] = '(ccg.ccg_ss_id = ' . $this->User->getSsId() . ')';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
