<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Spada
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada
 */

namespace App\Model\DashboardItem\Widget\Fms;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Gui\Templates\NumberGeneralEquipment;
use App\Frame\Mvc\AbstractBaseWidgetDashboard;
use Illuminate\Support\Facades\DB;

/**
 * Total planning project
 *
 * @package    app
 * @subpackage Model\DashboardItem\Widget\Fms
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 Spada
 */
class EquipmentCost extends AbstractBaseWidgetDashboard
{
    /**w
     * Constructor to load when there is a new object created.
     *
     * @param string $id The unique id from the chart.
     */
    public function __construct(string $id = '')
    {
        parent::__construct('equipmentCost', $id);
        $this->Template = new NumberGeneralEquipment($id);
        $this->Template->setGridDimension(4);
    }


    /**
     * Function to load the template data.
     *
     * @return void
     */
    public function loadTemplate(): void
    {
        $tempData = $this->loadData();
        $data = [];
        $numberFormatter = new NumberFormatter();
        $subTotal = 0;
        foreach ($tempData AS $row) {
            $total = 0;
            if (empty($row['total_cost']) === false) {
                $total = $numberFormatter->doFormatCurrency($row['total_cost']);
                $subTotal += $row['total_cost'];
            }
            $data[$row['title']] = $total;
        }
        $data['total'] = $numberFormatter->doFormatCurrency($subTotal);
        $data = [
            'title' => $this->getStringParameter('title'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-danger',
            'data' => $data,
        ];
        $this->Template->setData($data);
    }

    /**
     * Function to load total number of draft project.
     *
     * @return array
     */
    public function loadData(): array
    {
        $strWhere = '(eq.eq_ss_id = ' . $this->getIntParameter('eq_ss_id') . ')';
        $query = 'SELECT  \'Fuel\' AS title, SUM(eqf.eqf_qty_fuel * eqf.eqf_cost) AS total_cost
                  FROM    equipment AS eq INNER JOIN
                          equipment_fuel AS eqf ON eqf.eqf_eq_id = eq.eq_id
                  WHERE eqf.eqf_deleted_on IS NULL AND eqf.eqf_confirm_on IS NOT NULL AND ' . $strWhere .
                  ' UNION ALL
                  SELECT  \'Service\' AS title, 
                          SUM(svc.svc_total) AS total_cost
                  FROM    equipment AS eq INNER JOIN
                          service_order AS svo ON svo.svo_eq_id = eq.eq_id INNER JOIN
                          service_order_cost AS svc ON svc.svc_svo_id = svo.svo_id
                  WHERE svc.svc_deleted_on IS NULL AND ' . $strWhere;
        $sqlResult = DB::select($query);
        $results = [];
        if (empty($sqlResult) === false) {
            $results = DataParser::arrayObjectToArray($sqlResult);
        }

        return $results;
    }
}
