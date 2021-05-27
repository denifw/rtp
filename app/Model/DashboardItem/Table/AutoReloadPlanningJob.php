<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\DashboardItem\Table;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\Label;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractBaseTableDashboard;
use App\Model\Dao\Job\JobOrderDao;
use Illuminate\Support\Facades\DB;

/**
 *
 *
 * @package    app
 * @subpackage Model\DashboardItem\Table
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class AutoReloadPlanningJob extends AbstractBaseTableDashboard
{
    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id The unique id from the chart.
     */
    public function __construct(string $id = '')
    {
        parent::__construct($id);
        $this->setAutoReloadTime(30000);
    }

    /**
     * Function to load the chart data.
     *
     * @return void
     */
    public function loadTable(): void
    {
        $this->Table = new Table($this->Id . 'Tbl');
        $this->Table->setHeaderRow([
            'jo_number' => Trans::getWord('jobNumber'),
            'rel_name' => Trans::getWord('customer'),
            'jo_customer_ref' => Trans::getWord('reference' ),
            'wh_name' => Trans::getWord('warehouse'),
            'status' => Trans::getWord('lastStatus' ),
        ]);
        $data = $this->loadData();
        $results = [];
        $joDao = new JobOrderDao();
        foreach ($data as $row) {
            $style = 'gray';
            $label = Trans::getWord('draft');
            if (empty($row['jo_publish_on']) === false) {
                $style = 'danger';
                $label = Trans::getWord('publish');
            }
            $row['jo_customer_ref'] = $joDao->concatReference($row);
            $row['status'] = new Label($label,$style);
            $results[] = $row;
        }
        $this->Table->addRows($results);
        $this->Table->addTableAttribute('style', 'color: black; font-size: 15px;font-family: Arial Black, Arial Bold, Gadget, sans-serif;');
        $this->Table->addColumnAttribute('eta', 'style', 'text-align: center;');
        $this->Table->addColumnAttribute('status', 'style', 'text-align: center;');
    }

    /**
     * Function to load the chart data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $wheres = [];
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $orWheres = [];
        if ($this->isValidParameter('us_id') === true) {
            $orWheres[] = '(jo.jo_manager_id = ' . $this->getIntParameter('us_id') . ')';
        }
        if ($this->isValidParameter('us_rel_id') === true) {
            $orWheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('us_rel_id') . ')';
        }
        if (empty($orWheres) === false) {
            $wheres[] = '(' . implode(' OR ', $orWheres) . ')';
        }
        $wheres[] = '(jo.jo_start_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $strWhereInbound = $strWhere;
        $strWhereOutbound = $strWhere;
        $strWhereOpname = $strWhere;
        $strWhereMovement = $strWhere;
        $strWhereAdjustment = $strWhere;

        $query = "SELECT jo_id, jo_number, srt_name, rel_name, wh_name, jo_start_on, jo_srt_id, so_id, so_number, customer_ref, aju_ref, bl_ref, packing_ref, sppb_ref, jo_publish_on
                FROM (SELECT jo.jo_id, jo.jo_number, srt.srt_name, rel.rel_name, wh.wh_name, jo.jo_start_on, jo.jo_srt_id, so.so_id, so.so_number,
                        (CASE WHEN so.so_customer_ref IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                            (CASE WHEN so.so_aju_ref IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref,
                            (CASE WHEN so.so_bl_ref IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                            (CASE WHEN so.so_packing_ref IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                            (CASE WHEN so.so_sppb_ref IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                            jo.jo_publish_on
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           relation as rel ON jo.jo_rel_id = rel.rel_id INNER JOIN
                           job_inbound as ji ON jo.jo_id = ji.ji_jo_id INNER JOIN
                           warehouse as wh ON ji.ji_wh_id = wh.wh_id LEFT OUTER JOIN
                           sales_order as so ON ji.ji_so_id = so.so_id " . $strWhereInbound . "
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, srt.srt_name, rel.rel_name, wh.wh_name, jo.jo_start_on, jo.jo_srt_id, so.so_id, so.so_number,
                      (CASE WHEN so.so_customer_ref IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                          (CASE WHEN so.so_aju_ref IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref,
                          (CASE WHEN so.so_bl_ref IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                          (CASE WHEN so.so_packing_ref IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                          (CASE WHEN so.so_sppb_ref IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                          jo.jo_publish_on
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           relation as rel ON jo.jo_rel_id = rel.rel_id INNER JOIN
                           job_outbound as job ON jo.jo_id = job.job_jo_id INNER JOIN
                           warehouse as wh ON job.job_wh_id = wh.wh_id LEFT OUTER JOIN
                           sales_order as so ON job.job_so_id = so.so_id " . $strWhereOutbound . "
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, srt.srt_name, rel.rel_name, wh.wh_name, jo.jo_start_on, jo.jo_srt_id, so.so_id, so.so_number,
                          jo.jo_customer_ref as customer_ref, jo.jo_aju_ref as aju_ref, jo.jo_bl_ref as bl_ref,
                          jo.jo_packing_ref as packing_ref, jo.jo_sppb_ref as sppb_ref, jo.jo_publish_on
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           relation as rel ON jo.jo_rel_id = rel.rel_id INNER JOIN
                           stock_opname as sop ON jo.jo_id = sop.sop_jo_id INNER JOIN
                           warehouse as wh ON sop.sop_wh_id = wh.wh_id " . $strWhereOpname . "
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, srt.srt_name, null as rel_name, wh.wh_name, jo.jo_start_on, jo.jo_srt_id, so.so_id, so.so_number,
                          jo.jo_customer_ref as customer_ref, jo.jo_aju_ref as aju_ref, jo.jo_bl_ref as bl_ref,
                          jo.jo_packing_ref as packing_ref, jo.jo_sppb_ref as sppb_ref, jo.jo_publish_on
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           job_movement as jm ON jo.jo_id = jm.jm_jo_id INNER JOIN
                           warehouse as wh ON jm.jm_wh_id = wh.wh_id " . $strWhereMovement . "
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, srt.srt_name, rel.rel_name, wh.wh_name, jo.jo_start_on, jo.jo_srt_id, so.so_id, so.so_number,
                          jo.jo_customer_ref as customer_ref, jo.jo_aju_ref as aju_ref, jo.jo_bl_ref as bl_ref,
                          jo.jo_packing_ref as packing_ref, jo.jo_sppb_ref as sppb_ref, jo.jo_publish_on
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           relation as rel ON jo.jo_rel_id = rel.rel_id INNER JOIN
                           job_adjustment as jaj ON jo.jo_id = jaj.ja_jo_id INNER JOIN
                           warehouse as wh ON jaj.ja_wh_id = wh.wh_id " . $strWhereAdjustment . ') as jo';
        $query .= ' ORDER BY jo_publish_on, jo_id';
        $sqlResult = DB::select($query);
        $result = [];
        if (empty($sqlResult) === false) {
            $result = DataParser::arrayObjectToArray($sqlResult);
        }

        return $result;
    }

    /**
     * Function to load addtional call back parameter.
     *
     * @return void
     */
    protected function loadAddtionalCallBackParameter(): void
    {
        if ($this->checkPageRight('AllowSeeAllJob') === false) {
            $this->addCallBackParameter('us_id', $this->User->getId());
            $this->addCallBackParameter('us_rel_id', $this->User->getRelId());
        }
    }
}
