<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\DashboardItem\Table\Warehouse;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractBaseTableDashboard;
use Illuminate\Support\Facades\DB;

/**
 *
 *
 * @package    app
 * @subpackage Model\DashboardItem\Table
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class ArriveSoon extends AbstractBaseTableDashboard
{
    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id The unique id from the chart.
     */
    public function __construct(string $id = '')
    {
        parent::__construct($id);
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
            'srt_name' => Trans::getWord('serviceTerm'),
            'rel_name' => Trans::getWord('customer'),
            'wh_name' => Trans::getWord('warehouse'),
            'eta' => Trans::getWord('eta' )
        ]);
        $data = $this->loadData();
        $results = [];
        foreach ($data as $row) {
            $link = '';
            switch ($row['jo_srt_id']) {
                case 1:
                    $link = url('joWhInbound/view?jo_id=' . $row['jo_id']);
                    break;
                case 2:
                    $link = url('joWhOutbound/view?jo_id=' . $row['jo_id']);
                    break;
                case 3:
                    $link = url('joWhOpname/view?jo_id=' . $row['jo_id']);
                    break;
                case 4:
                    $link = url('joWhStockAdjustment/view?jo_id=' . $row['jo_id']);
                    break;
                case 5:
                    $link = url('joWhStockMovement/view?jo_id=' . $row['jo_id']);
                    break;
            }
            if(empty($link) === false) {
                $row['jo_number'] = new HyperLink('AsjJoNumber' . $row['jo_id'], $row['jo_number'], $link);
            }
            $eta = '';
            if (empty($row['eta_date']) === false) {
                if (empty($row['eta_time']) === false) {
                    $eta = DateTimeParser::format($row['eta_date'] . ' ' . $row['eta_time'], 'Y-m-d H:i:s', 'd.M.Y H.i');
                } else {
                    $eta = DateTimeParser::format($row['eta_date'], 'Y-m-d', 'd.M.Y');
                }
            }
            $row['eta'] = $eta;
            $results[] = $row;
        }
        $this->Table->addRows($results);
        $this->Table->addColumnAttribute('eta', 'style', 'text-align: center;');
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
        }
    }

    /**
     * Function to load the chart data.
     *
     * @return array
     */
    private function loadData(): array
    {
//        $month = DateTimeParser::createDateTime();
//        $month->modify('+30 day');
//        $date = $month->format('Y-m-d');
        $wheres = [];
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_srv_id = 1)';
        $wheres[] = '(jo.jo_start_on IS NULL)';
        $wheres[] = '(jo.jo_publish_on IS NOT NULL)';
        if ($this->isValidParameter('jo_manager_id') === true) {
            $wheres[] = '(jo.jo_manager_id = ' . $this->getIntParameter('jo_manager_id') . ')';
        }
        if ($this->isValidParameter('jo_rel_id') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('jo_rel_id') . ')';
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $strWhereInbound = $strWhere;
        $strWhereOutbound = $strWhere;
        $strWhereOpname = $strWhere;
        $strWhereMovement = $strWhere;
        $strWhereAdjustment = $strWhere;
//        $strWhereInbound = $strWhere . " AND (ji.ji_eta_date <= '" . $date . "')";
//        $strWhereOutbound = $strWhere . " AND (job.job_eta_date <= '" . $date . "')";
//        $strWhereOpname = $strWhere . " AND (sop.sop_date <= '" . $date . "')";
//        $strWhereMovement = $strWhere . " AND (jm.jm_date <= '" . $date . "')";
//        $strWhereAdjustment = $strWhere . " AND (jo.jo_order_date <= '" . $date . "')";

        $query = 'SELECT jo_id, jo_number, srt_name, rel_name, wh_name, eta_date, eta_time, jo_srt_id
                FROM (SELECT jo.jo_id, jo.jo_number, srt.srt_name, rel.rel_name, wh.wh_name, ji.ji_eta_date as eta_date, ji.ji_eta_time as eta_time, jo.jo_srt_id
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           relation as rel ON jo.jo_rel_id = rel.rel_id INNER JOIN
                           job_inbound as ji ON jo.jo_id = ji.ji_jo_id INNER JOIN
                           warehouse as wh ON ji.ji_wh_id = wh.wh_id ' . $strWhereInbound . '
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, srt.srt_name, rel.rel_name, wh.wh_name, job.job_eta_date as eta_date, job.job_eta_time as eta_time, jo.jo_srt_id
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           relation as rel ON jo.jo_rel_id = rel.rel_id INNER JOIN
                           job_outbound as job ON jo.jo_id = job.job_jo_id INNER JOIN
                           warehouse as wh ON job.job_wh_id = wh.wh_id ' . $strWhereOutbound . '
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, srt.srt_name, rel.rel_name, wh.wh_name, sop.sop_date as eta_date, sop.sop_time as eta_time, jo.jo_srt_id
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           relation as rel ON jo.jo_rel_id = rel.rel_id INNER JOIN
                           stock_opname as sop ON jo.jo_id = sop.sop_jo_id INNER JOIN
                           warehouse as wh ON sop.sop_wh_id = wh.wh_id ' . $strWhereOpname . '
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, srt.srt_name, null as rel_name, wh.wh_name, jm.jm_date as eta_date, jm.jm_time as eta_time, jo.jo_srt_id
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           job_movement as jm ON jo.jo_id = jm.jm_jo_id INNER JOIN
                           warehouse as wh ON jm.jm_wh_id = wh.wh_id' . $strWhereMovement . '
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, srt.srt_name, rel.rel_name, wh.wh_name, jo.jo_order_date as eta_date, null as eta_time, jo.jo_srt_id
                      FROM job_order as jo INNER JOIN
                           service as srv on jo.jo_srv_id = srv.srv_id INNER JOIN
                           service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                           relation as rel ON jo.jo_rel_id = rel.rel_id INNER JOIN
                           job_adjustment as ja ON jo.jo_id = ja.ja_jo_id INNER JOIN
                           warehouse as wh ON ja.ja_wh_id = wh.wh_id ' . $strWhereAdjustment . ') as jo
                ORDER BY eta_date DESC, eta_time DESC, jo_id';
        $sqlResult = DB::select($query);
        $result = [];
        if (empty($sqlResult) === false) {
            $result = DataParser::arrayObjectToArray($sqlResult, [
                'jo_id',
                'jo_srt_id',
                'jo_number',
                'srt_name',
                'rel_name',
                'wh_name',
                'eta_date',
                'eta_time',
            ]);
        }

        return $result;
    }
}
