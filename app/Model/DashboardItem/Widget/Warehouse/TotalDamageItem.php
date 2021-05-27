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
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractBaseWidgetDashboard;
use Illuminate\Support\Facades\DB;

/**
 * Total total good items
 *
 * @package    app
 * @subpackage Model\Chart\Widget\Project\Applicator
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2018 Spada
 */
class TotalDamageItem extends AbstractBaseWidgetDashboard
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
            'tile_style' => 'tile-stats tile-danger',
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
        if ($this->isValidParameter('jo_rel_id') === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('jo_rel_id') . ')';
        }
        $wheres[] = '(jis.jis_deleted_on IS NULL)';
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = '(jid.jid_gdt_id IS NOT NULL)';

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sum(jis.jis_quantity) as total
                    FROM job_inbound_stock as jis INNER JOIN
                      job_inbound_detail as jid ON jis.jis_jid_id = jid.jid_id INNER JOIN
                      job_inbound as ji ON jid.jid_ji_id = ji.ji_id INNER JOIN
                      job_order as jo ON ji.ji_jo_id = jo.jo_id' . $strWhere;
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
    }
}
