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
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
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
class EquipmentStatus extends AbstractBaseWidgetDashboard
{
    /**w
     * Constructor to load when there is a new object created.
     *
     * @param string $id The unique id from the chart.
     */
    public function __construct(string $id = '')
    {
        parent::__construct($id);
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
        foreach ($tempData AS $row) {
            $total = 0;
            if (empty($row['total']) === false) {
                $total = $row['total'];
            }
            $totalText = '';
            $label = $row['eqs_name'];
            if ($label === 'Available') {
                $totalText = new LabelSuccess($total);
                $totalText->addAttribute('style','font-size: 13px');
            } elseif ($label === 'Not Available') {
                $totalText = new LabelDark($total);
                $totalText->addAttribute('style','font-size: 13px');
            } elseif ($label=== 'On Service') {
                $totalText = new LabelWarning($total);
                $totalText->addAttribute('style','font-size: 13px');
            }
            $data[$row['eqs_name']] = $totalText;
        }
        $data = [
            'title' => $this->getStringParameter('title'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-blue-fourth',
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
        $wheres = [];
        $wheres[] = '(eqs.eqs_deleted_on IS NULL)';
        $wheres[] = '(eq_ss_id = ' . $this->getIntParameter('eq_ss_id') . ')';
        $strWhere =  ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT eq.eq_ss_id, eqs.eqs_id, eqs.eqs_name, COUNT(eq.eq_id) AS eq.total
                  FROM   equipment_status AS eqs
                         LEFT OUTER JOIN equipment AS eq ON eq.eq_eqs_id = eqs.eqs_id
                  GROUP BY eq.eq_ss_id, eqs.eqs_id, eqs.eqs_name' . $strWhere;
        $sqlResult = DB::select($query);
        $results = [];
        if (empty($sqlResult) === false) {
            $results = DataParser::arrayObjectToArray($sqlResult);
        }

        return $results;
    }

    /**
     * Function to load addtional call back parameter.
     *
     * @return void
     */
    protected function loadAddtionalCallBackParameter(): void
    {
    }

}
