<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\DashboardItem\Table\Trucking;

use App\Frame\Formatter\DataParser;
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
            'jo_number' => Trans::getTruckingWord('jobNumber'),
            'rel_name' => Trans::getTruckingWord('customer'),
            'jdl_transport_module' => Trans::getTruckingWord('transportModule'),
            'eg_name' => Trans::getTruckingWord('transport'),
            'jo_vendor' => Trans::getTruckingWord('vendor'),
            'status' => Trans::getTruckingWord('lastStatus')
        ]);
        $data = $this->loadData();
        $results = [];
        $jobDao = new JobOrderDao();

        foreach ($data as $row) {
            $link = $jobDao->getJobUrl('view', $row['jo_srt_id'], $row['jo_id'], false, true);
            if (empty($link) === false) {
                $row['jo_number'] = new HyperLink('IpJoNumber' . $row['jo_id'], $row['jo_number'], $link);
            }
            $style = 'primary';
            if (empty($row['ac_style']) === false) {
                $style = $row['ac_style'];
            }
            $row['status'] = new Label(Trans::getWord($row['jac_action'] . '' . $row['jo_srt_id'] . '.description', 'action'),$style);
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
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('jo_manager_id') === true) {
            $wheres[] = '(jo.jo_manager_id = ' . $this->getIntParameter('jo_manager_id') . ')';
        }
        if ($this->isValidParameter('jo_rel_id') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('jo_rel_id') . ')';
        }
        if ($this->isValidParameter('jo_srv_id') === true) {
            $wheres[] = '(jo.jo_srv_id = ' . $this->getIntParameter('jo_srv_id') . ')';
        }
        $wheres[] = '(jo.jo_start_on IS NOT NULL)';
//        $wheres[] = '(jo.jo_finish_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT jo.jo_id, jo.jo_number, rel.rel_name, eg.eg_name, tm.tm_name as jdl_transport_module,
                    jo.jo_publish_on, jo.jo_srv_id, jo.jo_srt_id, ven.rel_name as jo_vendor,
                    jac.jac_id, ac.ac_code as jac_action, jae.jae_description, ac.ac_style, jo.jo_start_on,
                    srt.srt_route as jo_route
                FROM job_delivery as jdl
                     INNER JOIN job_order as jo ON jo.jo_id = jdl.jdl_jo_id
                     INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                     INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                     INNER JOIN equipment_group as eg ON jdl.jdl_eg_id = eg.eg_id
                     INNER JOIN transport_module as tm ON jdl.jdl_tm_id = tm.tm_id
                     INNER JOIN relation as ven ON jo.jo_vendor_id = ven.rel_id
                     LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                     LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                     LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id " . $strWhere;
        $query .= ' ORDER BY jo.jo_number, jo.jo_id';

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
        if ($this->checkPageRight('ThirdPartyAccess') === true) {
            $this->addCallBackParameter('jo_rel_id', $this->User->getRelId());
        } elseif ($this->checkPageRight('AllowSeeAllOfficerJob') === false) {
            $this->addCallBackParameter('jo_manager_id', $this->User->getId());
        } elseif ($this->checkPageRight('AllowSeeAllOfficeJob') === false) {
            $this->addCallBackParameter('jo_order_of_id', $this->User->Relation->getOfficeId());
        }
    }
}
