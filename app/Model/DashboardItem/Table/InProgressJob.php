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
class InProgressJob extends AbstractBaseTableDashboard
{
    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id The unique id from the chart.
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
            'srt_name' => Trans::getWord('serviceTerm'),
            'rel_name' => Trans::getWord('customer'),
            'wh_name' => Trans::getWord('warehouse'),
            'jo_start_on' => Trans::getWord('startOn'),
            'status' => Trans::getWord('lastStatus')
        ]);
        $data = $this->loadData();
        $results = [];
        $jobDao = new JobOrderDao();

        foreach ($data as $row) {
            $link = $jobDao->getJobUrl('view', $row['jo_srt_id'], $row['jo_id'], false, true);
            if (empty($link) === false) {
                $row['jo_number'] = new HyperLink('IpJoNumber' . $row['jo_id'], $row['jo_number'], $link);
            }
            $row['jo_start_on'] = DateTimeParser::format($row['jo_start_on'], 'Y-m-d H:i:s', 'd.M.Y H.i');
            $style = 'primary';
            if (empty($row['ac_style']) === false) {
                $style = $row['ac_style'];
            }
            $row['status'] = new Label(Trans::getWord($row['jac_action'] . '' . $row['jo_srt_id'] . '.description', 'action'), $style);
            $results[] = $row;
        }
        $this->Table->addRows($results);
        if ($this->getIntParameter('jo_srv_id') === 2) {
            $this->Table->removeColumn('wh_name');
            $this->Table->addColumnAfter('rel_name', 'transport_module', Trans::getWord('transportModule'));
        } else {
            $this->Table->addColumnAttribute('eta', 'style', 'text-align: center;');
        }
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
        if ($this->isValidParameter('jo_srv_id') === true) {
            $wheres[] = '(jo.jo_srv_id = ' . $this->getIntParameter('jo_srv_id') . ')';
        }
        if ($this->isValidParameter('jo_manager_id') === true) {
            $wheres[] = '(jo.jo_manager_id = ' . $this->getIntParameter('jo_manager_id') . ')';
        }
        if ($this->isValidParameter('jo_rel_id') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('jo_rel_id') . ')';
        }
        if ($this->isValidParameter('jo_rel_id') === true) {
            $wheres[] = '(jo_rel_id = ' . $this->getIntParameter('jo_rel_id') . ')';
        }
        $wheres[] = '(jo.jo_start_on IS NOT NULL)';
        $wheres[] = '(jo.jo_finish_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $strWhereInbound = $strWhere;
        $strWhereOutbound = $strWhere;
        $strWhereOpname = $strWhere;
        $strWhereMovement = $strWhere;
        $strWhereAdjustment = $strWhere;
        $strWhereInklaring = $strWhere;

        $query = "SELECT jo_id, jo_number, srt_name, rel_name, wh_name, jo_start_on, jo_srt_id, jac_id, jac_action, jae_description, ac_style, jo_route, transport_module
                FROM (SELECT jo.jo_id, jo.jo_number, srt.srt_name, rel.rel_name, wh.wh_name, jo.jo_start_on, jo.jo_srt_id,
                            jac.jac_id, ac.ac_code as jac_action, jae.jae_description, ac.ac_style, srt.srt_route as jo_route,
                            null as transport_module
                      FROM job_order as jo
                        INNER JOIN service as srv on jo.jo_srv_id = srv.srv_id
                        INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                        INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                        INNER JOIN job_inbound as ji ON jo.jo_id = ji.ji_jo_id
                        INNER JOIN warehouse as wh ON ji.ji_wh_id = wh.wh_id
                        LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                        LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                        LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id " . $strWhereInbound . "
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, srt.srt_name, rel.rel_name, wh.wh_name, jo.jo_start_on, jo.jo_srt_id,
                            jac.jac_id, ac.ac_code as jac_action, jae.jae_description, ac.ac_style, srt.srt_route as jo_route,
                            null as transport_module
                      FROM job_order as jo
                        INNER JOIN service as srv on jo.jo_srv_id = srv.srv_id
                        INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                        INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                        INNER JOIN job_outbound as job ON jo.jo_id = job.job_jo_id
                        INNER JOIN warehouse as wh ON job.job_wh_id = wh.wh_id
                        LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                        LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                        LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id " . $strWhereOutbound . "
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, srt.srt_name, rel.rel_name, wh.wh_name, jo.jo_start_on, jo.jo_srt_id,
                            jac.jac_id, ac.ac_code as jac_action, jae.jae_description, ac.ac_style, srt.srt_route as jo_route,
                            null as transport_module
                      FROM job_order as jo
                        INNER JOIN service as srv on jo.jo_srv_id = srv.srv_id
                        INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                        INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                        INNER JOIN stock_opname as sop ON jo.jo_id = sop.sop_jo_id
                        INNER JOIN warehouse as wh ON sop.sop_wh_id = wh.wh_id
                        LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                        LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                        LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id " . $strWhereOpname . "
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, srt.srt_name, null as rel_name, wh.wh_name, jo.jo_start_on, jo.jo_srt_id,
                            jac.jac_id, ac.ac_code as jac_action, jae.jae_description, ac.ac_style, srt.srt_route as jo_route,
                            null as transport_module
                      FROM job_order as jo
                        INNER JOIN service as srv on jo.jo_srv_id = srv.srv_id
                        INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                        INNER JOIN job_movement as jm ON jo.jo_id = jm.jm_jo_id
                        INNER JOIN warehouse as wh ON jm.jm_wh_id = wh.wh_id
                        LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                        LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                        LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id " . $strWhereMovement . "
                      UNION ALL
                      SELECT jo.jo_id, jo.jo_number, srt.srt_name, rel.rel_name, wh.wh_name, jo.jo_start_on, jo.jo_srt_id,
                            jac.jac_id, ac.ac_code as jac_action, jae.jae_description, ac.ac_style, srt.srt_route as jo_route,
                            null as transport_module
                      FROM job_order as jo
                        INNER JOIN service as srv on jo.jo_srv_id = srv.srv_id
                        INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                        INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                        INNER JOIN job_adjustment as jaj ON jo.jo_id = jaj.ja_jo_id
                        INNER JOIN warehouse as wh ON jaj.ja_wh_id = wh.wh_id
                        LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                        LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                        LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id " . $strWhereAdjustment . "
                       UNION ALL
                       SELECT jo.jo_id, jo.jo_number, srt.srt_name, rel.rel_name, wh.wh_name, jo.jo_start_on, jo.jo_srt_id,
                            jac.jac_id, ac.ac_code as jac_action, jae.jae_description, ac.ac_style, srt.srt_route as jo_route,
                            tm.tm_name as transport_module
                       FROM job_order as jo
                        INNER JOIN service as srv on jo.jo_srv_id = srv.srv_id
                        INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                        INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                        INNER JOIN job_inklaring as jik ON jo.jo_id = jik.jik_jo_id
                        INNER JOIN sales_order as so ON jik.jik_so_id = so.so_id
                        INNER JOIN transport_module as tm ON so.so_tm_id = tm.tm_id
                        LEFT OUTER JOIN warehouse as wh ON so.so_wh_id = wh.wh_id
                        LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                        LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                        LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id " . $strWhereInklaring . ') as jo';
        $sqlResult = DB::select($query);
        $result = [];
        if (empty($sqlResult) === false) {
            $result = DataParser::arrayObjectToArray($sqlResult);
        }

        return $result;
    }
}
