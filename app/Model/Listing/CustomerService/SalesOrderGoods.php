<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\CustomerService;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelYesNo;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\CustomerService\SalesOrderGoodsDao;
use App\Model\Dao\Job\JobOrderDao;

/**
 * Class to control the system of SalesOrderGoods.
 *
 * @package    app
 * @subpackage Model\Listing\CustomerService
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class SalesOrderGoods extends AbstractListingModel
{

    /**
     * SalesOrderGoods constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'sog');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
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
            'so_number' => Trans::getWord('soNumber'),
            'so_customer' => Trans::getWord('customer'),
            'sog_number' => Trans::getWord('goodsId'),
            'sog_quantity' => Trans::getWord('quantity'),
            'sog_weight' => Trans::getWord('weight') . ' (KG)',
            'sog_cbm' => Trans::getWord('cbm'),
            'so_container' => Trans::getWord('container'),
            'so_service' => Trans::getWord('service'),
            'sog_status' => Trans::getWord('status'),
        ]);
        # Load the data for SalesOrderGoods.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('sog_cbm', 'float');
        $this->ListingTable->addColumnAttribute('sog_quantity', 'style', 'text-align: right;');
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['sog_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return SalesOrderGoodsDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = SalesOrderGoodsDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        $results = [];
        $number = new NumberFormatter($this->User);
        $joDao = new JobOrderDao();
        foreach ($data as $row) {
            $quantity = (float)$row['sog_quantity'];
            $row['sog_quantity'] = $number->doFormatFloat($quantity) . ' ' . $row['sog_uom'];
            $grossWight = (float)$row['sog_gross_weight'];
            $netWight = (float)$row['sog_net_weight'];
            $cbm = (float)$row['sog_cbm'];
            if ($row['sog_dimension_unit'] === 'Y') {
                $grossWight *= $quantity;
                $netWight *= $quantity;
                $cbm *= $quantity;
            }
            $row['sog_cbm'] = $cbm;
            $row['sog_weight'] = StringFormatter::generateKeyValueTableView([
                [
                    'label' => Trans::getWord('gross'),
                    'value' => $number->doFormatFloat($grossWight)
                ],
                [
                    'label' => Trans::getWord('net'),
                    'value' => $number->doFormatFloat($netWight)
                ],
            ]);
            $service = [
                [
                    'label' => Trans::getWord('inklaring'),
                    'value' => new LabelYesNo($row['so_inklaring'])
                ],
                [
                    'label' => Trans::getWord('delivery'),
                    'value' => new LabelYesNo($row['so_delivery'])
                ],
                [
                    'label' => Trans::getWord('incoTerms'),
                    'value' => $row['so_inco_terms']
                ],
                [
                    'label' => Trans::getWord('warehouse'),
                    'value' => new LabelYesNo($row['so_warehouse'])
                ]
            ];
            $row['so_service'] = StringFormatter::generateKeyValueTableView($service);
            $container = [];
            if (empty($row['sog_container_type']) === false) {
                $container[] = [
                    'label' => Trans::getWord('type'),
                    'value' => $row['sog_container_type']
                ];
            }
            if (empty($row['sog_container_number']) === false) {
                $container[] = [
                    'label' => Trans::getWord('number'),
                    'value' => $row['sog_container_number']
                ];
            }
            if (empty($row['sog_seal_number']) === false) {
                $container[] = [
                    'label' => Trans::getWord('seal'),
                    'value' => $row['sog_seal_number']
                ];
            }
            $row['so_container'] = StringFormatter::generateKeyValueTableView($container);
            $status = new LabelDanger(Trans::getWord('published'));
            if (empty($row['jo_id']) === false) {
                $statusData = [
                    $row['jo_number'],
                    $row['jo_service'],
                    $joDao->generateStatus([
                        'is_deleted' => !empty($row['jo_deleted_on']),
                        'is_hold' => !empty($row['joh_id']),
                        'is_finish' => !empty($row['jo_finish_on']),
                        'is_document' => !empty($row['jo_document_on']),
                        'is_start' => !empty($row['jo_start_on']),
                        'jac_id' => $row['jo_action_id'],
                        'jae_style' => $row['jo_action_style'],
                        'jac_action' => $row['jo_action'],
                        'jo_srt_id' => $row['jo_srt_id'],
                        'is_publish' => !empty($row['jo_publish_on']),
                    ])
                ];
                $status = StringFormatter::generateTableView($statusData);
            }
            $row['sog_status'] = $status;

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
        $wheres[] = SqlHelper::generateNumericCondition('so.so_ss_id', $this->User->getSsId());
        $wheres[] = SqlHelper::generateNullCondition('so.so_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('so.so_finish_on');
        $wheres[] = SqlHelper::generateNullCondition('so.so_publish_on', false);
        $wheres[] = SqlHelper::generateNullCondition('sog.sog_deleted_on');

        return $wheres;
    }
}
