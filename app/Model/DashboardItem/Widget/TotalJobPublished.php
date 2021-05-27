<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Spada
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2018 spada
 */

namespace App\Model\DashboardItem\Widget;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractBaseWidgetDashboard;
use Illuminate\Support\Facades\DB;

/**
 * Total planning project
 *
 * @package    app
 * @subpackage Model\Chart\Widget\Project\Applicator
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2018 Spada
 */
class TotalJobPublished extends AbstractBaseWidgetDashboard
{
    /**
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
            'icon' => Icon::Indent,
            'tile_style' => 'tile-stats tile-danger',
            'amount' => $number->doFormatAmount($this->loadData()),
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
        $wheres[] = '(jo_deleted_on IS NULL)';
        $wheres[] = '(jo_publish_on IS NOT NULL)';
        $wheres[] = '(jo_start_on IS NULL)';
        $wheres[] = '(jo_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('jo_manager_id') === true) {
            $wheres[] = '(jo_manager_id = ' . $this->getIntParameter('jo_manager_id') . ')';
        }
        if ($this->isValidParameter('jo_rel_id') === true) {
            $wheres[] = '(jo_rel_id = ' . $this->getIntParameter('jo_rel_id') . ')';
        }
        if ($this->isValidParameter('jo_srv_id') === true) {
            $wheres[] = '(jo_srv_id = ' . $this->getIntParameter('jo_srv_id') . ')';
        }
        if ($this->isValidParameter('jo_srt_id') === true) {
            $wheres[] = '(jo_srt_id = ' . $this->getIntParameter('jo_srt_id') . ')';
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT COUNT(jo_id) AS total
                FROM job_order ' . $strWhere;
        $sqlResult = DB::select($query);
        $result = 0;
        if (empty($sqlResult) === false) {
            $result = DataParser::objectToArray($sqlResult[0], ['total'])['total'];
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
