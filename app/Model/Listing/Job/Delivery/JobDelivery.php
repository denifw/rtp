<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\Job\Delivery;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\Job\Delivery\JobDeliveryDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Listing\Job\BaseJobOrder;

/**
 * Class to control the system of JobDelivery.
 *
 * @package    app
 * @subpackage Model\Listing\Job\Delivery
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobDelivery extends BaseJobOrder
{

    /**
     * JobDelivery constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'jdl', $parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'jo_customer', $this->getStringParameter('jo_customer'), 'loadGoodsOwnerData');
        $relField->setHiddenField('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);

        $statusField = $this->Field->getSelect('jo_status', $this->getStringParameter('jo_status'));
        $statusField->addOption(Trans::getWord('draft'), '1');
        $statusField->addOption(Trans::getWord('publish'), '2');
        $statusField->addOption(Trans::getWord('inProgress'), '3');
        $statusField->addOption(Trans::getWord('complete'), '4');
        $statusField->addOption(Trans::getWord('canceled'), '5');

        $vendorField = $this->Field->getSingleSelect('relation', 'jo_vendor', $this->getStringParameter('jo_vendor'));
        $vendorField->setHiddenField('jo_vendor_id', $this->getIntParameter('jo_vendor_id'));
        $vendorField->addParameter('rel_ss_id', $this->User->getSsId());
        $vendorField->setEnableDetailButton(false);
        $vendorField->setEnableNewButton(false);

        # Transport Module
        $tmField = $this->Field->getSingleSelect('transportModule', 'jdl_transport_module', $this->getStringParameter('jdl_transport_module'));
        $tmField->setHiddenField('jdl_tm_id', $this->getIntParameter('jdl_tm_id'));
        $tmField->setEnableDetailButton(false);
        $tmField->setEnableNewButton(false);
        $tmField->addClearField('jdl_equipment_group');
        $tmField->addClearField('jdl_eg_id');
        $tmField->addClearField('jdl_equipment');
        $tmField->addClearField('jdl_eq_id');
        # Equipment Group
        $egField = $this->Field->getSingleSelect('eg', 'jdl_equipment_group', $this->getStringParameter('jdl_equipment_group'));
        $egField->setHiddenField('jdl_eg_id', $this->getIntParameter('jdl_eg_id'));
        $egField->addOptionalParameterById('eg_tm_id', 'jdl_tm_id');
        $egField->setEnableDetailButton(false);
        $egField->setEnableNewButton(false);
        $egField->addClearField('jdl_equipment');
        $egField->addClearField('jdl_eq_id');
        # Equipment
        $eqField = $this->Field->getSingleSelect('eq', 'jdl_equipment', $this->getStringParameter('jdl_equipment'));
        $eqField->setHiddenField('jdl_eq_id', $this->getIntParameter('jdl_eq_id'));
        $eqField->addOptionalParameterById('eq_eg_id', 'jdl_eg_id');
        $eqField->addOptionalParameterById('eq_rel_id', 'jo_vendor_id');
        $eqField->addParameter('eq_ss_id', $this->User->getSsId());
        $eqField->setEnableDetailButton(false);
        $eqField->setEnableNewButton(false);


        $this->ListingForm->addField(Trans::getTruckingWord('jobNumber'), $this->Field->getText('jo_number', $this->getStringParameter('jo_number')));
        $this->ListingForm->addField(Trans::getTruckingWord('serviceTerm'), $this->getJobServiceTermField('delivery'));
        $this->ListingForm->addField(Trans::getTruckingWord('customer'), $relField);
        $this->ListingForm->addField(Trans::getTruckingWord('vendor'), $vendorField);

        $this->ListingForm->addField(Trans::getTruckingWord('customerRef'), $this->Field->getText('reference', $this->getStringParameter('reference')));
        $this->ListingForm->addField(Trans::getTruckingWord('transportModule'), $tmField);
        $this->ListingForm->addField(Trans::getTruckingWord('transportType'), $egField);
        $this->ListingForm->addField(Trans::getTruckingWord('transport'), $eqField);

//        $this->ListingForm->addField(Trans::getTruckingWord('consolidate'), $this->Field->getYesNo('jdl_consolidate', $this->getStringParameter('jdl_consolidate')));
        $this->ListingForm->addField(Trans::getTruckingWord('status'), $statusField);
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
            'jo_number' => Trans::getWord('jobNumber'),
            'jo_service_term' => Trans::getWord('serviceTerm'),
            'jdl_transport_module' => Trans::getWord('transportModule'),
            'jdl_equipment_group' => Trans::getWord('transportType'),
            'jdl_transport_number' => Trans::getWord('transportNumber'),
            'jdl_so_customer' => Trans::getWord('customer'),
            'so_customer_ref' => Trans::getWord('reference'),
            'jo_vendor' => Trans::getWord('vendor'),
            'jo_manager' => Trans::getWord('jobManager'),
            'jo_status' => Trans::getWord('lastStatus'),
        ]);
        # Load the data for JobDelivery.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->addColumnAttribute('jo_status', 'style', 'text-align:center;');
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['jo_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['jo_id']);
        }
        $this->disableNewButton(true);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return JobDeliveryDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = JobDeliveryDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        $results = [];
        $joDao = new JobOrderDao();
        foreach ($data as $row) {
            if ($row['jdl_tm_code'] === 'road') {
                $row['jdl_transport_number'] = $row['jdl_equipment'];
            }
            $row['so_customer_ref'] = $joDao->concatReference($row);
            $row['jo_status'] = $joDao->generateStatus([
                'is_hold' => !empty($row['joh_id']),
                'is_deleted' => !empty($row['jo_deleted_on']),
                'is_finish' => !empty($row['jo_finish_on']),
                'is_document' => !empty($row['jo_document_on']),
                'is_start' => !empty($row['jo_start_on']),
                'jac_id' => $row['jo_action_id'],
                'jae_style' => $row['jo_action_style'],
                'jac_action' => $row['jo_action'],
                'jo_srt_id' => $row['jo_srt_id'],
                'is_publish' => !empty($row['jo_publish_on']),
            ]);
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
        $wheres = $this->getJoConditions();
        if ($this->isValidParameter('jo_vendor_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('jo.jo_vendor_id', $this->getIntParameter('jo_vendor_id'));
        }
        if ($this->isValidParameter('jdl_tm_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('jdl.jdl_tm_id', $this->getIntParameter('jdl_tm_id'));
        }
        if ($this->isValidParameter('jdl_eg_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('jdl.jdl_eg_id', $this->getIntParameter('jdl_eg_id'));
        }
        if ($this->isValidParameter('jdl_eq_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('jdl.jdl_eq_id', $this->getIntParameter('jdl_eq_id'));
        }
        if ($this->isValidParameter('jdl_consolidate') === true) {
            $wheres[] = SqlHelper::generateStringCondition('jdl.jdl_consolidate', $this->getStringParameter('jdl_consolidate'));
        }

        return $wheres;
    }
}
