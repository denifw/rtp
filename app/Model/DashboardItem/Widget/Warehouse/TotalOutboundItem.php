<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Spada
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2018 spada
 */

namespace App\Model\DashboardItem\Widget\Warehouse;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractBaseWidgetDashboard;
use Illuminate\Support\Facades\DB;

/**
 * Total outbound items
 *
 * @package    app
 * @subpackage Model\Chart\Widget\Project\Applicator
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2018 Spada
 */
class TotalOutboundItem extends AbstractBaseWidgetDashboard
{
    /**w
     * Constructor to load when there is a new object created.
     *
     * @param string $id The unique id from the chart.
     */
    public function __construct(string $id = '')
    {
        parent::__construct($id);
        $this->Template = new NumberGeneral($id);
    }


    /**
     * Function to load the template data.
     *
     * @return void
     */
    public function loadTemplate(): void
    {
        $number = new NumberFormatter();
        $data = [
            'title' => $this->getStringParameter('title'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-blue-fourth',
            'amount' => $number->doFormatFloat($this->loadData()),
            'url' => '',
        ];
        $this->Template->setData($data);
    }

    /**
     * Function to load total number of draft project.
     *
     * @return float
     */
    public function loadData(): float
    {
        $wheres = [];
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jod.jod_deleted_on IS NULL)';
        if ($this->isValidParameter('jo_rel_id') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('jo_rel_id') . ')';
        }
        if ($this->isValidParameter('jo_start_period') === true) {
            $startFrom = "(job.job_end_load_on = '" . $this->getStringParameter('jo_start_period') . "')";
            if ($this->isValidParameter('jo_end_period') === true) {
                $startFrom = "(job.job_end_load_on >= '" . $this->getStringParameter('jo_start_period') . "')";
            }
            $wheres[] = $startFrom;
        }
        if ($this->isValidParameter('jo_end_period') === true) {
            $startUntil = "(job.job_end_load_on = '" . $this->getStringParameter('jo_end_period') . "')";
            if ($this->isValidParameter('jo_start_period') === true) {
                $startUntil = "(job.job_end_load_on <= '" . $this->getStringParameter('jo_end_period') . "')";
            }
            $wheres[] = $startUntil;
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sum(jod.jod_qty_loaded) as total
                        FROM job_outbound_detail as jod INNER JOIN
                          job_outbound as job on jod.jod_job_id = job.job_id INNER JOIN
                          job_order as jo on job.job_jo_id = jo.jo_id' . $strWhere;
        $sqlResult = DB::select($query);
        $result = 0;
        if (empty($sqlResult) === false) {
            $result = (float)DataParser::objectToArray($sqlResult[0], ['total'])['total'];
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
        } elseif ($this->checkPageRight('AllowSeeAllOfficeJob') === false) {
            $this->addCallBackParameter('jo_order_of_id', $this->User->Relation->getOfficeId());
        }
        $month = DateTimeParser::createDateTime();
        $this->addCallBackParameter('jo_start_period', $month->format('Y-m') . '-01 00:01:00');
        $this->addCallBackParameter('jo_end_period', $month->format('Y-m-t') . ' 23:59:00');
    }
}
