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
class PriceDelivery extends AbstractListingModel
{

    /*
     * Property to store service id.
     *
     * @var int $SrvId
     * */
    private $SrvId;

    /*
     * Property to store header table for route view.
     *
     * @var array $TableHeaders
     * */
//    private $TableHeaders = [];

    /**
     * Price constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'prcSlsDl');
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
        $this->SrvId = ServiceDao::getIdByCode('delivery');
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

//        # Add view type field
//        if ($this->isValidParameter('view_type') === false) {
//            $this->setParameter('view_type', 'C');
//        }
//        $viewField = $this->Field->getRadioGroup('view_type', $this->getStringParameter('view_type'));
//        $viewField->addRadios([
//            'C' => Trans::getFinanceWord('code'),
//            'R' => Trans::getFinanceWord('route'),
//        ]);

        # Status field
        $statusField = $this->Field->getSelect('prc_status', $this->getStringParameter('prc_status'));
        $statusField->addOption(Trans::getFinanceWord('draft'), '1');
        $statusField->addOption(Trans::getFinanceWord('submitted'), '2');
        $statusField->addOption(Trans::getFinanceWord('rejected'), '3');
        $statusField->addOption(Trans::getFinanceWord('approved'), '4');
        $statusField->addOption(Trans::getFinanceWord('expired'), '5');
        $statusField->addOption(Trans::getFinanceWord('deleted'), '6');

        # Truck type field.
        $egField = $this->Field->getSingleSelect('eg', 'prc_eg_name', $this->getStringParameter('prc_eg_name'), 'loadSingleSelectAutoComplete');
        $egField->setHiddenField('prc_eg_id', $this->getIntParameter('prc_eg_id'));
        $egField->setEnableNewButton(false);

        # Origin District
        # district Origin
        $originDistrict = $this->Field->getSingleSelect('district', 'prc_origin_district', $this->getStringParameter('prc_origin_district'), 'loadSingleSelectAutoComplete');
        $originDistrict->setHiddenField('prc_dtc_origin', $this->getIntParameter('prc_dtc_origin'));
        $originDistrict->setEnableNewButton(false);
        # district Origin
        $desDistrict = $this->Field->getSingleSelect('district', 'prc_destination_district', $this->getStringParameter('prc_destination_district'), 'loadSingleSelectAutoComplete');
        $desDistrict->setHiddenField('prc_dtc_destination', $this->getIntParameter('prc_dtc_destination'));
        $desDistrict->setEnableNewButton(false);

        $this->ListingForm->addField(Trans::getFinanceWord('code'), $this->Field->getText('prc_code', $this->getStringParameter('prc_code')));
        $this->ListingForm->addField(Trans::getFinanceWord('quotation'), $this->Field->getText('prc_qt_number', $this->getStringParameter('prc_qt_number')));
        $this->ListingForm->addField(Trans::getFinanceWord('customer'), $customerField);
        $this->ListingForm->addField(Trans::getFinanceWord('serviceTerm'), $srtField);
        $this->ListingForm->addField(Trans::getFinanceWord('transportType'), $egField);
        $this->ListingForm->addField(Trans::getFinanceWord('originDistrict'), $originDistrict);
        $this->ListingForm->addField(Trans::getFinanceWord('destinationDistrict'), $desDistrict);
        $this->ListingForm->addField(Trans::getFinanceWord('status'), $statusField);
//        $this->ListingForm->addField(Trans::getFinanceWord('viewType'), $viewField);


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
            'prc_relation' => Trans::getFinanceWord('customer'),
            'prc_srt_name' => Trans::getFinanceWord('serviceTerm'),
            'prc_origin' => Trans::getFinanceWord('origin'),
            'prc_destination' => Trans::getFinanceWord('destination'),
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
            $origin = $row['prc_origin_district'] . ', ' . $row['prc_origin_city'] . ', ' . $row['prc_origin_state'];
            if (empty($row['prc_origin_address']) === false) {
                $origin = $row['prc_origin_address'] . ', ' . $origin;
            }
            $destination = $row['prc_destination_district'] . ', ' . $row['prc_destination_city'] . ', ' . $row['prc_destination_state'];
            if (empty($row['prc_destination_address']) === false) {
                $destination = $row['prc_destination_address'] . ', ' . $destination;
            }
            if ($row['prc_srt_pol'] === 'Y') {
                $row['prc_origin'] = $row['prc_pol_name'] . ' - ' . $row['prc_pol_country'];
            } else {
                $row['prc_origin'] = $origin;
            }

            if ($row['prc_srt_pod'] === 'Y') {
                $row['prc_destination'] = $row['prc_pod_name'] . ' - ' . $row['prc_pod_country'];
            } else {
                $row['prc_destination'] = $destination;
            }
            # Description
            $description = [
                [
                    'label' => Trans::getFinanceWord('transport'),
                    'value' => $row['prc_eg_code'],
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
        $wheres[] = SqlHelper::generateStringCondition('prc.prc_type', 'S');
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
        if ($this->isValidParameter('prc_eg_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('prc.prc_eg_id', $this->getIntParameter('prc_eg_id'));
        }
        if ($this->isValidParameter('prc_dtc_origin') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('prc.prc_dtc_origin', $this->getIntParameter('prc_dtc_origin'));
        }
        if ($this->isValidParameter('prc_dtc_destination') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('prc.prc_dtc_destination', $this->getIntParameter('prc_dtc_destination'));
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
