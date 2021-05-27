<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\CustomerService;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\Html\Labels\LabelYesNo;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\CustomerService\SalesOrderDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo SalesOrder.
 *
 * @package    app
 * @subpackage Model\Ajax\CustomerService
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class SalesOrder extends AbstractBaseAjaxModel
{


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('so_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('so.so_number', $this->getStringParameter('search_key'));
            $wheres[] = SqlHelper::generateNumericCondition('so.so_ss_id', $this->getIntParameter('so_ss_id'));
            if ($this->isValidParameter('so_rel_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('so.so_rel_id', $this->getIntParameter('so_rel_id'));
            }
            if ($this->isValidParameter('so_number') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('so.so_number', $this->getStringParameter('so_number'));
            }
            if ($this->isValidParameter('so_customer') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('rel.rel_name', $this->getStringParameter('so_customer'));
            }
            if ($this->isValidParameter('so_customer_ref') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('so.so_customer_ref', $this->getStringParameter('so_customer_ref'));
            }

            return SalesOrderDao::loadSingleSelectData($wheres, [
                'so.so_number DESC', 'so.so_id',
            ]);
        }
        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadActiveData(): array
    {
        if ($this->isValidParameter('so_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('so.so_number', $this->getStringParameter('search_key'));
            $wheres[] = SqlHelper::generateNumericCondition('so.so_ss_id', $this->getIntParameter('so_ss_id'));
            if ($this->isValidParameter('so_rel_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('so.so_rel_id', $this->getIntParameter('so_rel_id'));
            }
            if ($this->isValidParameter('so_number') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('so.so_number', $this->getStringParameter('so_number'));
            }
            if ($this->isValidParameter('so_customer') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('rel.rel_name', $this->getStringParameter('so_customer'));
            }
            if ($this->isValidParameter('so_customer_ref') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('so.so_customer_ref', $this->getStringParameter('so_customer_ref'));
            }
            $wheres[] = SqlHelper::generateNullCondition('so.so_finish_on');
            $wheres[] = SqlHelper::generateNullCondition('so.so_deleted_on');
            $wheres[] = SqlHelper::generateNullCondition('so.so_soh_id');

            $data = SalesOrderDao::loadSingleSelectData($wheres, [
                'so.so_id DESC',
            ]);
            $results = [];
            foreach ($data as $row) {
                $cn = new LabelYesNo($row['so_container']);
                $row['so_container_text'] = $cn . '';
                $results[] = $row;
            }
            return $results;
        }
        return [];
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadUnInvoiceData(): array
    {
        if ($this->isValidParameter('so_ss_id')) {
            $wheres = [];
            $wheres[] = SqlHelper::generateNumericCondition('so.so_ss_id', $this->getIntParameter('so_ss_id'));
            if ($this->isValidParameter('so_number')) {
                $wheres[] = SqlHelper::generateLikeCondition('so.so_number', $this->getStringParameter('so_number'));
            }
            if ($this->isValidParameter('so_reference')) {
                $wheres[] = SqlHelper::generateOrLikeCondition([
                    'so.so_customer_ref', 'so.so_bl_ref', 'so.so_aju_ref', 'so.so_sppb_ref', 'so.so_packing_ref',
                ], $this->getStringParameter('so_reference'));
            }
            if ($this->isValidParameter('so_rel_id')) {
                $wheres[] = SqlHelper::generateNumericCondition('so.so_rel_id', $this->getIntParameter('so_rel_id'));
            }
            if ($this->isValidParameter('so_invoice_of_id')) {
                $wheres[] = SqlHelper::generateNumericCondition('so.so_invoice_of_id', $this->getIntParameter('so_invoice_of_id'));
            }
            $ink = '(so.so_id IN (SELECT jik.jik_so_id
                                    FROM job_inklaring as jik INNER JOIN
                                        job_order as jo ON jik.jik_jo_id = jo.jo_id INNER JOIN
                                        job_sales as jos ON jos.jos_jo_id = jo.jo_id
                                    WHERE jo.jo_deleted_on IS NULL AND jos.jos_deleted_on IS NULL
                                        AND jos.jos_sid_id IS NULL
                                    GROUP BY jik.jik_so_id))';
            $jdl = '(so.so_id IN (SELECT jdl.jdl_so_id
                                    FROM job_delivery as jdl INNER JOIN
                                        job_order as jo ON jdl.jdl_jo_id = jo.jo_id INNER JOIN
                                        job_sales as jos ON jos.jos_jo_id = jo.jo_id
                                    WHERE jo.jo_deleted_on IS NULL AND jos.jos_deleted_on IS NULL
                                        AND jos.jos_sid_id IS NULL
                                    GROUP BY jdl.jdl_so_id))';
            $wheres[] = '(' . $ink . ' OR ' . $jdl . ')';
            $wheres[] = '(so.so_deleted_on IS NULL)';
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT so.so_id, so.so_number, so.so_customer_ref, so.so_container, so.so_bl_ref,
                        so.so_aju_ref, so.so_packing_ref, so.so_sppb_ref, rel.rel_name, soc.party as so_party,
                        so.so_order_date
                    FROM sales_order as so INNER JOIN
                        relation as rel ON so.so_rel_id = rel.rel_id
                        LEFT OUTER JOIN (SELECT soc_so_id, COUNT(soc_id) as party
                                    FROM sales_order_container
                                    WHERE soc_deleted_on IS NULL
                                    GROUP BY soc_so_id) as soc ON so.so_id = soc.soc_so_id ' . $strWhere;
            $query .= ' ORDER BY so.so_id';
            $query .= ' LIMIT 30 OFFSET 0';
            $sqlResults = DB::select($query);
            $data = DataParser::arrayObjectToArray($sqlResults);
            $results = [];
            $so = new SalesOrderDao();
            $number = new NumberFormatter();
            $dt = new DateTimeParser();
            foreach ($data as $row) {
                if ($row['so_container'] === 'Y') {
                    $row['so_container'] = StringFormatter::generateYesNoLabel($row['so_container']);
                } else {
                    $row['so_container'] = StringFormatter::generateLabel('LCL', 'danger');
                }
                $row['so_reference'] = $so->concatReference($row);
                $row['so_party_number'] = $number->doFormatFloat($row['so_party']);
                $row['so_order_date'] = $dt->formatDate($row['so_order_date']);
                $results[] = $row;
            }

            return $results;
        }
        return [];
    }

}
