<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Job;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\JobOrderDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo JobOrder.
 *
 * @package    app
 * @subpackage Model\Ajax\Job
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobOrder extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('jo_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('jo_number', $this->getStringParameter('search_key'));
            $wheres[] = SqlHelper::generateNumericCondition('jo_ss_id', $this->getIntParameter('jo_ss_id'));
            if ($this->isValidParameter('so_id')) {
                $inklaring = '(jo_id IN (SELECT jik_jo_id
                                            FROM job_inklaring
                                            WHERE (jik_so_id = ' . $this->getIntParameter('so_id') . ')))';
                $delivery = '(jo_id IN (SELECT jdl_jo_id
                                            FROM job_delivery
                                            WHERE (jdl_so_id = ' . $this->getIntParameter('so_id') . ')))';
                $inbound = '(jo_id IN (SELECT ji_jo_id
                                            FROM job_inbound
                                            WHERE (ji_so_id = ' . $this->getIntParameter('so_id') . ')))';
                $outbound = '(jo_id IN (SELECT job_jo_id
                                            FROM job_outbound
                                            WHERE (job_so_id = ' . $this->getIntParameter('so_id') . ')))';
                $wheres[] = '(' . $inklaring . ' OR ' . $delivery . ' OR ' . $inbound . ' OR ' . $outbound . ')';
            }
            if ($this->isValidParameter('jo_srv_id')) {
                $wheres[] = SqlHelper::generateNumericCondition('jo_srv_id', $this->getIntParameter('jo_srv_id'));
            }
            return JobOrderDao::loadSingleSelectData($wheres);
        }
        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadInklaringJob(): array
    {
        $wheres = [];
        $wheresOr = [];
        $wheresOr[] = SqlHelper::generateLikeCondition('rel_name', $this->getStringParameter('search_key'));
        $wheresOr[] = SqlHelper::generateLikeCondition('jo_customer_ref', $this->getStringParameter('search_key'));
        $wheresOr[] = SqlHelper::generateLikeCondition('jo_aju_ref', $this->getStringParameter('search_key'));
        $wheresOr[] = SqlHelper::generateLikeCondition('jo_bl_ref', $this->getStringParameter('search_key'));
        $wheres[] = '(' . implode(' OR ', $wheresOr) . ')';
        if ($this->isValidParameter('jo_srv_id') === true) {
            $wheres[] = '(jo_srv_id = ' . $this->getIntParameter('jo_srv_id') . ')';
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jo.jo_id, jo.jo_number, jo.jo_customer_ref, jo.jo_aju_ref, jo.jo_bl_ref,
                         rel.rel_name AS customer
                    FROM job_order AS jo INNER JOIN
                         relation AS rel ON rel.rel_id = jo.jo_rel_id' . $strWhere;
        $query .= ' ORDER BY jo_customer_ref';
        $query .= ' LIMIT 30 OFFSET 0';
        $data = DB::select($query);
        $results = [];
        if (empty($data) === false) {
            $tempResults = DataParser::arrayObjectToArray($data);
            foreach ($tempResults as $row) {
                $cusRef = 'Ref: ' . $row['jo_customer_ref'] . ' | ';
                if (empty($row['jo_aju_ref']) === false) {
                    $cusRef .= Trans::getWord('ajuRef') . ' ' . $row['jo_aju_ref'] . ' | ';
                }
                if (empty($row['jo_bl_ref']) === false) {
                    $cusRef .= Trans::getWord('blRef') . ' ' . $row['jo_bl_ref'];
                }
                $row['jo_customer_ref'] = $cusRef;
                $results[] = $row;
            }
        }

        return $this->doPrepareSingleSelectData($results, 'jo_customer_ref', 'jo_id');
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadDataForCashAdvance(): array
    {
        if ($this->isValidParameter('jo_ss_id') === true && $this->isValidParameter('jo_srv_id') === true) {
            $wheres = [];
            $wheres[] = '(jo.jo_ss_id = ' . $this->getIntParameter('jo_ss_id') . ')';
            $wheres[] = '(jo.jo_srv_id = ' . $this->getIntParameter('jo_srv_id') . ')';
            $wheres[] = '(jo.jo_id NOT IN (SELECT ca_jo_id
                                            FROM cash_advance
                                            where ca_deleted_on IS NULL))';
            if ($this->isValidParameter('jo_number') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('jo.jo_number', $this->getStringParameter('jo_number'));
            }
            if ($this->isValidParameter('jo_manager_id') === true) {
                $wheres[] = '(jo.jo_manager_id = ' . $this->getIntParameter('jo_manager_id') . ')';
            }
            if ($this->isValidParameter('jo_vendor_id') === true) {
                $wheres[] = '(jo.jo_vendor_id = ' . $this->getIntParameter('jo_vendor_id') . ')';
            }
            if ($this->isValidParameter('jo_plate_number') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('jo.jo_number', $this->getStringParameter('jo_plate_number'));
            }
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jo.jo_id, jo.jo_number, rel.rel_name as jo_customer,
                                    ven.rel_name as jo_vendor, eq.eq_license_plate as jo_plate_number,
                                    jop.jop_rate, jop.jop_quantity, jop.jop_exchange_rate, td.tax_percent
                    FROM job_order AS jo INNER JOIN
                         relation AS rel ON rel.rel_id = jo.jo_rel_id INNER JOIN
                         relation as ven ON jo.jo_vendor_id = ven.rel_id INNER JOIN
                         equipment as eq ON jt.jt_eq_id = eq.eq_id INNER JOIN
                        job_purchase as jop ON jo.jo_id = jop.jop_jo_id INNER JOIN
                        (SELECT td_tax_id, SUM(td_percent) as tax_percent
                         FROM tax_detail
                         where td_deleted_on IS NULL
                         GROUP BY td_tax_id) as td ON jop.jop_tax_id = td.td_tax_id ' . $strWhere;
            $query .= ' ORDER BY jo.jo_number, jo.jo_id';
            $sqlResults = DB::select($query);
            $results = [];
            if (empty($sqlResults) === false) {
                $data = DataParser::arrayObjectToArray($sqlResults);
                $number = new NumberFormatter();
                $tempJoIds = [];
                foreach ($data as $row) {
                    $rate = (float)$row['jop_rate'] * (float)$row['jop_quantity'] * (float)$row['jop_exchange_rate'];
                    $tax = ($rate * (float)$row['tax_percent']) / 100;
                    $purchase = $rate + $tax;
                    if (in_array($row['jo_id'], $tempJoIds, true) === false) {
                        $jo = [
                            'jo_id' => $row['jo_id'],
                            'jo_number' => $row['jo_number'],
                            'jo_customer' => $row['jo_customer'],
                            'jo_vendor' => $row['jo_vendor'],
                            'jo_plate_number' => $row['jo_plate_number'],
                            'jo_cash_required' => $purchase,
                            'jo_cash_required_number' => $number->doFormatFloat($purchase),
                        ];
                        $tempJoIds[] = $row['jo_id'];
                        $results[] = $jo;
                    } else {
                        $index = array_search($row['jo_id'], $tempJoIds, true);
                        $results[$index]['jo_cash_required'] += $purchase;
                        $results[$index]['jo_cash_required_number'] = $number->doFormatFloat($results[$index]['jo_cash_required']);
                    }
                }
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
    public function loadTableSelectData(): array
    {
        if ($this->isValidParameter('jo_ss_id') === true) {
            $wheres = [];
            $wheres[] = '(jo.jo_ss_id = ' . $this->getIntParameter('jo_ss_id') . ')';
            if ($this->isValidParameter('jo_number') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('jo.jo_number', $this->getStringParameter('jo_number'));
            }
            if ($this->isValidParameter('jo_service') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('srv.srv_name', $this->getStringParameter('jo_service'));
            }
            if ($this->isValidParameter('jo_service_term') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('srt.srt_name', $this->getStringParameter('jo_service_term'));
            }
            $wheres[] = '(jo.jo_deleted_on IS NULL)';
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jo.jo_id, jo.jo_number, rel.rel_name as jo_customer,
                              srv.srv_name as jo_service, jo.jo_srv_id, jo.jo_srt_id, srt.srt_name as jo_service_term,
                              jo.jo_invoice_of_id, io.of_name as jo_of_invoice
                    FROM job_order AS jo INNER JOIN
                         relation AS rel ON rel.rel_id = jo.jo_rel_id INNER JOIN
                         service as srv ON srv.srv_id = jo.jo_srv_id INNER JOIN
                          service_term as srt ON srt.srt_id = jo.jo_srt_id LEFT OUTER JOIN
                          office as io ON jo.jo_invoice_of_id = io.of_id ' . $strWhere;
            $query .= ' ORDER BY jo.jo_number, jo.jo_id';
            $sqlResults = DB::select($query);
            return DataParser::arrayObjectToArray($sqlResults);
        }

        return [];
    }
}
