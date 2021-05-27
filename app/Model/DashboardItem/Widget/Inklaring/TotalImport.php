<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Spada
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada
 */

namespace App\Model\DashboardItem\Widget\Inklaring;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractBaseWidgetDashboard;
use Illuminate\Support\Facades\DB;

/**
 * Total planning project
 *
 * @package    app
 * @subpackage Model\DashboardItem\Widget\Inklaring
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 Spada
 */
class TotalImport extends AbstractBaseWidgetDashboard
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
            'tile_style' => 'tile-stats tile-teal-third',
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
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        if ($this->isValidParameter('jo_rel_id') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('jo_rel_id') . ')';
        }
        if ($this->isValidParameter('jo_srt_id') === true) {
            $wheres[] = '(jo.jo_srt_id = ' . $this->getIntParameter('jo_srt_id') . ')';
        }
        if ($this->isValidParameter('jo_start_period') === true) {
            $startFrom = "(jo.jo_start_on = '" . $this->getStringParameter('jo_start_period') . "')";
            if ($this->isValidParameter('jo_end_period') === true) {
                $startFrom = "(jo.jo_start_on >= '" . $this->getStringParameter('jo_start_period') . "')";
            }
            $wheres[] = $startFrom;
        }
        if ($this->isValidParameter('jo_end_period') === true) {
            $startUntil = "(jo.jo_start_on = '" . $this->getStringParameter('jo_end_period') . "')";
            if ($this->isValidParameter('jo_start_period') === true) {
                $startUntil = "(jo.jo_start_on <= '" . $this->getStringParameter('jo_end_period') . "')";
            }
            $wheres[] = $startUntil;
        }

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT SUM(jog.jog_quantity) as total
                  FROM job_order as jo INNER JOIN
                       job_inklaring as jik on jik.jik_jo_id = jo.jo_id INNER JOIN
                       job_goods as jog on jog.jog_jo_id = jo.jo_id' . $strWhere;
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
        } elseif ($this->checkPageRight('AllowSeeAllOfficerJob') === false) {
            $this->addCallBackParameter('jo_manager_id', $this->User->getId());
        } elseif ($this->checkPageRight('AllowSeeAllOfficeJob') === false) {
            $this->addCallBackParameter('jo_order_of_id', $this->User->Relation->getOfficeId());
        }
    }
}
