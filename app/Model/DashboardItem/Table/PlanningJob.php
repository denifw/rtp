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
use App\Frame\Gui\Html\Buttons\HyperLink;
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
class PlanningJob extends AbstractBaseTableDashboard
{
    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id    The unique id from the chart.
     */
    public function __construct(string $id = '')
    {
        parent::__construct($id);
        $this->setHeight(300);
    }

    /**
     * Function to load addtional call back parameter.
     *
     * @return void
     */
    protected function loadAddtionalCallBackParameter(): void
    {
        if ($this->checkPageRight('ThirdPartyAccess') === true) {
            $this->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } elseif ($this->checkPageRight('AllowSeeAllOfficerJob') === false) {
            $this->addCallBackParameter('jo_manager_id', $this->User->getId());
        } elseif ($this->checkPageRight('AllowSeeAllOfficeJob') === false) {
            $this->addCallBackParameter('jo_order_of_id', $this->User->Relation->getOfficeId());
        }
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
            'wh_name' => Trans::getWord('warehouse'),
            'eta' => Trans::getWord('eta'),
            'status' => Trans::getWord('lastStatus')
        ]);
        if ($this->getIntParameter('jo_srv_id') === 2) {
            $this->Table->removeColumn('wh_name');
            $this->Table->removeColumn('eta');
            $this->Table->addColumnAfter('rel_name', 'transport_module', Trans::getWord('transportModule'));
        } else {
            $this->Table->addColumnAttribute('eta', 'style', 'text-align: center;');
        }
//        $this->Table->addTableAttribute('style', 'color: black; font-size: 15px;font-family: Arial Black, Arial Bold, Gadget, sans-serif;');
        $data = $this->loadData();
        $results = [];
        $jobDao = new JobOrderDao();
        foreach ($data as $row) {
            $link = $jobDao->getJobUrl('view', $row['jo_srt_id'], $row['jo_id'], false, true);
            if (empty($link) === false) {
                $row['jo_number'] = new HyperLink('IpJoNumber' . $row['jo_id'], $row['jo_number'], $link);
            }
            $eta = '';
            if (empty($row['eta_date']) === false) {
                if (empty($row['eta_time']) === false) {
                    $eta = DateTimeParser::format($row['eta_date'] . ' ' . $row['eta_time'], 'Y-m-d H:i:s', 'H.i d.M.Y');
                } else {
                    $eta = DateTimeParser::format($row['eta_date'], 'Y-m-d', 'd.M.Y');
                }
            }
            $row['eta'] = $eta;
            $row['status'] = $jobDao->generateStatus([
                'is_deleted' => false,
                'is_finish' => false,
                'is_start' => false,
                'is_hold' => false,
                'jac_id' => '',
                'jae_style' => '',
                'jac_action' => '',
                'jo_srt_id' => $row['jo_srt_id'],
                'is_publish' => !empty($row['jo_publish_on']),
            ]);
            $results[] = $row;
        }
        $this->Table->addRows($results);
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
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jo.jo_start_on IS NULL)';
        $orWheres = [];
        if ($this->isValidParameter('jo_srv_id') === true) {
            $wheres[] = '(jo.jo_srv_id = ' . $this->getIntParameter('jo_srv_id') . ')';
        }
        if ($this->isValidParameter('jo_manager_id') === true) {
            $orWheres[] = '(jo.jo_manager_id = ' . $this->getIntParameter('jo_manager_id') . ')';
        }
        if ($this->isValidParameter('jo_rel_id') === true) {
            $orWheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('jo_rel_id') . ')';
        }
        if ($this->isValidParameter('jo_rel_id') === true) {
            $wheres[] = '(jo_rel_id = ' . $this->getIntParameter('jo_rel_id') . ')';
        }
        if (empty($orWheres) === false) {
            $wheres[] = '(' . implode(' OR ', $orWheres) . ')';
        }

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $strWhereInbound = $strWhere;
        $strWhereOutbound = $strWhere;
        $strWhereOpname = $strWhere;
        $strWhereMovement = $strWhere;
        $strWhereAdjustment = $strWhere;
        $strWhereInklaring = $strWhere;

        $query = 'SELECT jo_id, jo_number, jo_aju_ref, srt_name, rel_name, wh_name, eta_date, eta_time, jo_srt_id, jo_publish_on, transport_module
                FROM (SELECT jo.jo_id, jo.jo_number, jo.jo_aju_ref, srt.srt_name, rel.rel_name, wh.wh_name, ji.ji_eta_date as eta_date, ji.ji_eta_time as eta_time, jo.jo_srt_id, jo.jo_publish_on, null as transport_module
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           relation as rel ON jo.jo_rel_id = rel.rel_id INNER JOIN
                           job_inbound as ji ON jo.jo_id = ji.ji_jo_id INNER JOIN
                           warehouse as wh ON ji.ji_wh_id = wh.wh_id ' . $strWhereInbound . '
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, jo.jo_aju_ref, srt.srt_name, rel.rel_name, wh.wh_name, job.job_eta_date as eta_date, job.job_eta_time as eta_time, jo.jo_srt_id, jo.jo_publish_on, null as transport_module
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           relation as rel ON jo.jo_rel_id = rel.rel_id INNER JOIN
                           job_outbound as job ON jo.jo_id = job.job_jo_id INNER JOIN
                           warehouse as wh ON job.job_wh_id = wh.wh_id ' . $strWhereOutbound . '
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, jo.jo_aju_ref, srt.srt_name, rel.rel_name, wh.wh_name, sop.sop_date as eta_date, sop.sop_time as eta_time, jo.jo_srt_id, jo.jo_publish_on, null as transport_module
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           relation as rel ON jo.jo_rel_id = rel.rel_id INNER JOIN
                           stock_opname as sop ON jo.jo_id = sop.sop_jo_id INNER JOIN
                           warehouse as wh ON sop.sop_wh_id = wh.wh_id ' . $strWhereOpname . '
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, jo.jo_aju_ref, srt.srt_name, null as rel_name, wh.wh_name, jm.jm_date as eta_date, jm.jm_time as eta_time, jo.jo_srt_id, jo.jo_publish_on, null as transport_module
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           job_movement as jm ON jo.jo_id = jm.jm_jo_id INNER JOIN
                           warehouse as wh ON jm.jm_wh_id = wh.wh_id' . $strWhereMovement . '
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, jo.jo_aju_ref, srt.srt_name, rel.rel_name, wh.wh_name, jo.jo_order_date as eta_date, null as eta_time, jo.jo_srt_id, jo.jo_publish_on, null as transport_module
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           relation as rel ON jo.jo_rel_id = rel.rel_id INNER JOIN
                           job_adjustment as ja ON jo.jo_id = ja.ja_jo_id INNER JOIN
                           warehouse as wh ON ja.ja_wh_id = wh.wh_id ' . $strWhereAdjustment . '
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, jo.jo_aju_ref, srt.srt_name, rel.rel_name, wh.wh_name, null as eta_date, null as eta_time, jo.jo_srt_id, jo.jo_publish_on, tm.tm_name as transport_module
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           relation as rel ON jo.jo_rel_id = rel.rel_id INNER JOIN
                           job_inklaring as jik ON jo.jo_id = jik.jik_jo_id INNER JOIN
                           sales_order as so ON jik.jik_so_id = so.so_id INNER JOIN
                            transport_module as tm ON so.so_tm_id = tm.tm_id LEFT OUTER JOIN
                           warehouse as wh ON so.so_wh_id = wh.wh_id ' . $strWhereInklaring . ') as jo
                ORDER BY eta_date, eta_time, jo_number, jo_id';
        $sqlResult = DB::select($query);
        $result = [];
        if (empty($sqlResult) === false) {
            $result = DataParser::arrayObjectToArray($sqlResult);
        }

        return $result;
    }
}
