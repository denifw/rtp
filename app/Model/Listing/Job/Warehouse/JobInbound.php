<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Job\Warehouse;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\JobInboundDao;
use App\Model\Listing\Job\BaseJobOrder;

/**
 * Class to control the system of JobInbound.
 *
 * @package    app
 * @subpackage Model\Listing\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobInbound extends BaseJobOrder
{

    /**
     * JobInbound constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhInbound', $parameters);
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
        # Warehouse Field
        $whField = $this->Field->getSingleSelect('warehouse', 'ji_warehouse', $this->getStringParameter('ji_warehouse'));
        $whField->setHiddenField('ji_wh_id', $this->getIntParameter('ji_wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);

        $statusField = $this->Field->getSelect('jo_status', $this->getStringParameter('jo_status'));
        $statusField->addOption(Trans::getWord('draft'), '1');
        $statusField->addOption(Trans::getWord('publish'), '2');
        $statusField->addOption(Trans::getWord('inProgress'), '3');
        $statusField->addOption(Trans::getWord('complete'), '4');
        $statusField->addOption(Trans::getWord('canceled'), '5');
        $statusField->addOption(Trans::getWord('hold'), '6');

        $shipperField = $this->Field->getSingleSelect('relation', 'ji_shipper', $this->getStringParameter('ji_shipper'));
        $shipperField->setHiddenField('ji_rel_id', $this->getIntParameter('ji_rel_id'));
        $shipperField->addParameter('rel_ss_id', $this->User->getSsId());
        $shipperField->setEnableDetailButton(false);
        $shipperField->setEnableNewButton(false);


        $this->ListingForm->addField(Trans::getWord('jobNumber'), $this->Field->getText('jo_number', $this->getStringParameter('jo_number')));
        $this->ListingForm->addField(Trans::getWord('arriveDateFrom'), $this->Field->getCalendar('arrive_date_from', $this->getStringParameter('arrive_date_from')));
        $this->ListingForm->addField(Trans::getWord('unloadDateFrom'), $this->Field->getCalendar('unload_date_from', $this->getStringParameter('unload_date_from')));
        $this->ListingForm->addField(Trans::getWord('completeDateFrom'), $this->Field->getCalendar('complete_date_from', $this->getStringParameter('complete_date_from')));
        $this->ListingForm->addField(Trans::getWord('warehouse'), $whField);
        $this->ListingForm->addField(Trans::getWord('arriveDateUntil'), $this->Field->getCalendar('arrive_date_until', $this->getStringParameter('arrive_date_until')));
        $this->ListingForm->addField(Trans::getWord('unloadDateUntil'), $this->Field->getCalendar('unload_date_until', $this->getStringParameter('unload_date_until')));
        $this->ListingForm->addField(Trans::getWord('completeDateUntil'), $this->Field->getCalendar('complete_date_until', $this->getStringParameter('complete_date_until')));
        $this->ListingForm->addField(Trans::getWord('customer'), $relField);
        $this->ListingForm->addField(Trans::getWord('reference'), $this->Field->getText('jo_reference', $this->getStringParameter('jo_reference')));
        $this->ListingForm->addField(Trans::getWord('shipper'), $shipperField);
        $this->ListingForm->addField(Trans::getWord('status'), $statusField);
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
            'jo_customer' => Trans::getWord('customer'),
            'jo_customer_ref' => Trans::getWord('customerRef'),
            'jo_order_date' => Trans::getWord('orderDate'),
            'ji_warehouse' => Trans::getWord('warehouse'),
            'ji_shipper' => Trans::getWord('shipper'),
            'ji_ata' => Trans::getWord('ata'),
            'jo_status' => Trans::getWord('lastStatus'),
        ]);
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setColumnType('jo_order_date', 'date');
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['jo_id']);

        $this->ListingTable->addColumnAttribute('jo_status', 'style', 'text-align: center');
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['jo_id']);
        }

    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return JobInboundDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = JobInboundDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());

        return $this->doPrepareData($data);
    }

    /**
     * Function to do prepare data.
     *
     * @param array $data To store the data.
     *
     * @return array
     */
    private function doPrepareData(array $data): array
    {
        $result = [];
        $joDao = new JobOrderDao();
        foreach ($data as $row) {
            $ata = '';
            if (empty($row['ji_ata_date']) === false) {
                if (empty($row['ji_ata_time']) === false) {
                    $ata = DateTimeParser::format($row['ji_ata_date'] . ' ' . $row['ji_ata_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
                } else {
                    $ata = DateTimeParser::format($row['ji_ata_date'], 'Y-m-d', 'd M Y');
                }
            }
            $row['ji_ata'] = $ata;
            $references = [
                [
                    'label' => 'Truck Number',
                    'value' => $row['ji_truck_number'],
                ],
                [
                    'label' => 'Container',
                    'value' => $row['ji_container_number'],
                ],
                [
                    'label' => 'Seal',
                    'value' => $row['ji_seal_number'],
                ]
            ];
            $row['jo_customer_ref'] = $joDao->concatReference($row, 'jo', $references);

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
            $result[] = $row;
        }

        return $result;

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
        if ($this->isValidParameter('ji_wh_id') === true) {
            $wheres[] = '(ji.ji_wh_id = ' . $this->getIntParameter('ji_wh_id') . ')';
        }
        if ($this->isValidParameter('ji_rel_id') === true) {
            $wheres[] = '(ji.ji_rel_id = ' . $this->getIntParameter('ji_rel_id') . ')';
        }
        if ($this->isValidParameter('arrive_date_from') === true) {
            if ($this->isValidParameter('arrive_date_until') === true) {
                $wheres[] = "(ji.ji_ata_date >= '" . $this->getStringParameter('arrive_date_from') . "')";
            } else {
                $wheres[] = "(ji.ji_ata_date = '" . $this->getStringParameter('arrive_date_from') . "')";
            }
        }
        if ($this->isValidParameter('arrive_date_until') === true) {
            if ($this->isValidParameter('arrive_date_from') === true) {
                $wheres[] = "(ji.ji_ata_date <= '" . $this->getStringParameter('arrive_date_until') . "')";
            } else {
                $wheres[] = "(ji.ji_ata_date = '" . $this->getStringParameter('arrive_date_until') . "')";
            }
        }
        if ($this->isValidParameter('unload_date_from') === true) {
            if ($this->isValidParameter('unload_date_until') === true) {
                $wheres[] = "(ji.ji_start_load_on >= '" . $this->getStringParameter('unload_date_from') . " 00:01:00')";
            } else {
                $wheres[] = "(ji.ji_start_load_on >= '" . $this->getStringParameter('unload_date_from') . " 00:01:00')";
                $wheres[] = "(ji.ji_start_load_on <= '" . $this->getStringParameter('unload_date_from') . " 23:59:00')";
            }
        }
        if ($this->isValidParameter('unload_date_until') === true) {
            if ($this->isValidParameter('unload_date_from') === true) {
                $wheres[] = "(ji.ji_start_load_on <= '" . $this->getStringParameter('unload_date_until') . " 23:59:00')";
            } else {
                $wheres[] = "(ji.ji_start_load_on >= '" . $this->getStringParameter('unload_date_until') . " 00:01:00')";
                $wheres[] = "(ji.ji_start_load_on <= '" . $this->getStringParameter('unload_date_until') . " 23:59:00')";
            }
        }
        # return the where query.
        return $wheres;
    }
}
