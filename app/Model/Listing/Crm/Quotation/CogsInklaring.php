<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\Crm\Quotation;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Crm\Quotation\PriceDao;
use App\Model\Dao\System\Service\ServiceDao;


/**
 * Class to control the system of Price.
 *
 * @package    app
 * @subpackage Model\Listing\Crm
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class CogsInklaring extends AbstractListingModel
{

    /*
     * Property to store service id.
     *
     * @var int $SrvId
     * */
    private $SrvId;

    /**
     * Price constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'prcPrcInk');
        $this->setParameters($parameters);
        $this->loadServiceId();
    }

    /**
     * Function to load service id.
     *
     * @return void
     */
    private function loadServiceId(): void
    {
        $this->SrvId = ServiceDao::getIdByCode('inklaring');
    }


    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Add Customer
        $customerField = $this->Field->getSingleSelect('relation', 'prc_relation', $this->getStringParameter('prc_relation'));
        $customerField->setHiddenField('prc_rel_id', $this->getIntParameter('prc_rel_id'));
        $customerField->addParameter('rel_ss_id', $this->User->getSsId());
        $customerField->setEnableNewButton(false);

        # Add Service Term
        $srtField = $this->Field->getSingleSelect('serviceTerm', 'prc_srt_name', $this->getStringParameter('prc_srt_name'));
        $srtField->setHiddenField('prc_srt_id', $this->getIntParameter('prc_srt_id'));
        $srtField->addParameter('srv_code', 'trucking');
        $srtField->setEnableNewButton(false);

        # Status field
        $statusField = $this->Field->getSelect('prc_status', $this->getStringParameter('prc_status'));
        $statusField->addOption(Trans::getFinanceWord('draft'), '1');
        $statusField->addOption(Trans::getFinanceWord('submitted'), '2');
        $statusField->addOption(Trans::getFinanceWord('rejected'), '3');
        $statusField->addOption(Trans::getFinanceWord('approved'), '4');
        $statusField->addOption(Trans::getFinanceWord('expired'), '5');
        $statusField->addOption(Trans::getFinanceWord('deleted'), '6');

        # Set Transport Module
        $tmField = $this->Field->getSingleSelect('transportModule', 'prc_transport_module', $this->getStringParameter('prc_transport_module'));
        $tmField->setHiddenField('prc_tm_id', $this->getIntParameter('prc_tm_id'));
        $tmField->setEnableNewButton(false);
        $tmField->addClearField('prc_po_id');
        $tmField->addClearField('prc_port');


        # Set Port origin single select table
        $orPortField = $this->Field->getSingleSelect('port', 'prc_port', $this->getStringParameter('prc_port'), 'loadSingleSelectAutoComplete');
        $orPortField->setHiddenField('prc_po_id', $this->getIntParameter('prc_po_id'));
        $orPortField->addOptionalParameterById('po_tm_id', 'prc_tm_id');
        $orPortField->setEnableNewButton(false);

        $this->ListingForm->addField(Trans::getFinanceWord('code'), $this->Field->getText('prc_code', $this->getStringParameter('prc_code')));
        $this->ListingForm->addField(Trans::getFinanceWord('quotation'), $this->Field->getText('qt_number', $this->getStringParameter('qt_number')));
        $this->ListingForm->addField(Trans::getFinanceWord('vendor'), $customerField);
        $this->ListingForm->addField(Trans::getFinanceWord('serviceTerm'), $srtField);
        $this->ListingForm->addField(Trans::getFinanceWord('transportModule'), $tmField);
        $this->ListingForm->addField(Trans::getFinanceWord('port'), $orPortField);
        $this->ListingForm->addField(Trans::getFinanceWord('status'), $statusField);

    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        $this->loadResultTableByCode();
    }

    /**
     * Function to load result table for code view.
     *
     * @return void
     */
    private function loadResultTableByCode(): void
    {
        # Set header column table
        $this->ListingTable->setHeaderRow([
            'prc_qt_number' => Trans::getFinanceWord('quotation'),
            'prc_code' => Trans::getFinanceWord('code'),
            'prc_relation' => Trans::getFinanceWord('vendor'),
            'prc_srt_name' => Trans::getFinanceWord('serviceTerm'),
            'prc_port' => Trans::getFinanceWord('port'),
            'prc_custom_clearance_type' => Trans::getFinanceWord('customClearanceType'),
            'prc_description' => Trans::getFinanceWord('description'),
            'prc_total' => Trans::getFinanceWord('totalPrice'),
            'prc_status' => Trans::getFinanceWord('status'),
        ]);

        # Load the data for Price.
        $this->ListingTable->addRows($this->loadDataByCode());

        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['prc_id']);
        }
        $this->ListingTable->setColumnType('prc_total', 'float');
        $this->ListingTable->addColumnAttribute('prc_qt_number', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('prc_code', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('prc_status', 'style', 'text-align: center');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return PriceDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data by code.
     *
     * @return array
     */
    private function loadDataByCode(): array
    {
        $data = PriceDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        $results = [];
        $prcDao = new PriceDao();
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $row['prc_port'] = $row['prc_pol_name'] . ' - ' . $row['prc_pol_country'];
            if ($row['prc_srt_pod'] === 'Y') {
                $row['prc_port'] = $row['prc_pod_name'] . ' - ' . $row['prc_pod_country'];
            }            # Description
            $description = [
                [
                    'label' => 'TM',
                    'value' => $row['prc_transport_module'],
                ],
            ];
            if (empty($row['prc_ct_id']) === false) {
                $description[] = [
                    'label' => 'CT',
                    'value' => $row['prc_ct_code'],
                ];
            }
            $description[] = [
                'label' => 'LT',
                'value' => $number->doFormatFloat((float)$row['prc_lead_time']) . ' ' . Trans::getFinanceWord('days'),
            ];
            $row['prc_description'] = StringFormatter::generateKeyValueTableView($description);
            # Status
            $row['prc_status'] = $prcDao->getStatus($row);
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
        $wheres[] = SqlHelper::generateNumericCondition('prc.prc_ss_id', $this->User->getSsId());
        $wheres[] = SqlHelper::generateNumericCondition('prc.prc_srv_id', $this->SrvId);
        $wheres[] = SqlHelper::generateStringCondition('prc.prc_type', 'P');
        if ($this->isValidParameter('prc_code') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('prc.prc_code', $this->getStringParameter('prc_code'));
        }
        if ($this->isValidParameter('qt_number') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('qt.qt_number', $this->getStringParameter('qt_number'));
        }
        if ($this->isValidParameter('prc_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('rel.rel_id', $this->getIntParameter('prc_rel_id'));
        }
        if ($this->isValidParameter('prc_srt_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('prc.prc_srt_id', $this->getIntParameter('prc_srt_id'));
        }
        if ($this->isValidParameter('prc_tm_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('prc.prc_tm_id', $this->getIntParameter('prc_tm_id'));
        }
        if ($this->isValidParameter('prc_po_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('prc.prc_po_id', $this->getIntParameter('prc_po_id'));
        }
        # Filter Status
        if ($this->isValidParameter('prc_status') === true) {
            if ($this->getIntParameter('prc_status') === 1) {
                # Draft
                $wheres[] = '(qt.qt_approve_on IS NULL)';
                $wheres[] = '(qt.qt_qts_id IS NULL)';
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
                $wheres[] = '(prc.prc_deleted_on IS NULL)';
            } elseif ($this->getIntParameter('prc_status') === 2) {
                # Submitted
                $wheres[] = '(qt.qt_qts_id IS NOT NULL)';
                $wheres[] = '(qts.qts_deleted_on IS NULL)';
                $wheres[] = '(qt.qt_approve_on IS NULL)';
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
                $wheres[] = '(prc.prc_deleted_on IS NULL)';
            } elseif ($this->getIntParameter('prc_status') === 3) {
                # Rejected
                $wheres[] = '(qt.qt_qts_id IS NOT NULL)';
                $wheres[] = '(qts.qts_deleted_on IS NOT NULL)';
                $wheres[] = '(qt.qt_approve_on IS NULL)';
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
                $wheres[] = '(prc.prc_deleted_on IS NULL)';
            } elseif ($this->getIntParameter('prc_status') === 4) {
                # Approved
                $wheres[] = '(qt.qt_approve_on IS NOT NULL)';
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
                $wheres[] = '(prc.prc_deleted_on IS NULL)';
            } elseif ($this->getIntParameter('prc_status') === 5) {
                # Expired
                $wheres[] = "(qt.qt_end_date < '" . date('Y-m-d') . "')";
                $wheres[] = '(qt.qt_deleted_on IS NULL)';
                $wheres[] = '(prc.prc_deleted_on IS NULL)';
            } else {
                # Deleted
                $wheres[] = '(prc.prc_deleted_on IS NOT NULL)';
            }
        } else {
            $wheres[] = '(prc.prc_deleted_on IS NULL)';
        }
        # return the list where condition.
        return $wheres;
    }
}
