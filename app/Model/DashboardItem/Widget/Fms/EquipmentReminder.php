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
use App\Frame\Gui\Templates\ScoreGeneralEquipment;
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
class EquipmentReminder extends AbstractBaseWidgetDashboard
{
    /**w
     * Constructor to load when there is a new object created.
     *
     * @param string $id The unique id from the chart.
     */
    public function __construct(string $id = '')
    {
        parent::__construct($id);
        $this->Template = new ScoreGeneralEquipment($id);
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
            $data[$row['title']] = $total;
        }
        $data = [
            'title' => $this->getStringParameter('title'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-grey',
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
        $query = 'SELECT \'Coming Soon\' AS title, COUNT(svrm.svrm_id) AS total
                  FROM  service_reminder AS svrm LEFT OUTER JOIN
                         (SELECT  sr.svrm_id,
                                   (sr.svrm_meter_interval - (coalesce(eqm.eqm_meter, 0) - coalesce(svo.svo_meter, 0))) AS svrm_meter_remaining
                          FROM     service_reminder AS sr LEFT OUTER JOIN
                                   equipment AS eq ON eq.eq_id = sr.svrm_eq_id LEFT OUTER JOIN
                                   service_task AS svt ON svt.svt_id = sr.svrm_svt_id LEFT OUTER JOIN
                                    (SELECT   eqm_eq_id, MAX(eqm_meter) AS eqm_meter
						             FROM     equipment_meter 
						             WHERE    eqm_deleted_on IS NULL
						              GROUP BY eqm_eq_id) AS eqm ON eqm.eqm_eq_id = eq.eq_id LEFT OUTER JOIN
						             (SELECT  MAX(svo_meter) AS svo_meter, MAX(svo_start_service_date) AS svo_start_service_date, svo_eq_id, svd_svt_id
						              FROM    service_order INNER JOIN
								              service_order_detail ON svd_svo_id = svo_id 
						              WHERE  svo_start_service_date IS NOT NULL AND svo_deleted_on IS NULL
						              GROUP BY svo_eq_id, svd_svt_id) AS svo ON svo.svo_eq_id = eq.eq_id AND svo.svd_svt_id = svt.svt_id) AS s on s.svrm_id = svrm.svrm_id
                  WHERE ((s.svrm_meter_remaining >= 0) AND (svrm.svrm_meter_threshold >= s.svrm_meter_remaining)
                         OR (svrm.svrm_next_due_date >= NOW()) AND (NOW() >= svrm.svrm_next_due_date_threshold))
                  UNION ALL
                  SELECT \'Over Due\' AS title, COUNT(svrm.svrm_id) AS total
                  FROM  service_reminder AS svrm LEFT OUTER JOIN
                         (SELECT  sr.svrm_id,
                                   (sr.svrm_meter_interval - (coalesce(eqm.eqm_meter, 0) - coalesce(svo.svo_meter, 0))) AS svrm_meter_remaining
                          FROM     service_reminder AS sr LEFT OUTER JOIN
                                   equipment AS eq ON eq.eq_id = sr.svrm_eq_id LEFT OUTER JOIN
                                   service_task AS svt ON svt.svt_id = sr.svrm_svt_id LEFT OUTER JOIN
                                    (SELECT   eqm_eq_id, MAX(eqm_meter) AS eqm_meter
						             FROM     equipment_meter 
						             WHERE    eqm_deleted_on IS NULL
						              GROUP BY eqm_eq_id) AS eqm ON eqm.eqm_eq_id = eq.eq_id LEFT OUTER JOIN
						             (SELECT  MAX(svo_meter) AS svo_meter, MAX(svo_start_service_date) AS svo_start_service_date, svo_eq_id, svd_svt_id
						              FROM    service_order INNER JOIN
								              service_order_detail ON svd_svo_id = svo_id 
						              WHERE  svo_start_service_date IS NOT NULL AND svo_deleted_on IS NULL
						              GROUP BY svo_eq_id, svd_svt_id) AS svo ON svo.svo_eq_id = eq.eq_id AND svo.svd_svt_id = svt.svt_id) AS s on s.svrm_id = svrm.svrm_id
                  WHERE ((s.svrm_meter_remaining < 0) OR (NOW() > svrm.svrm_next_due_date))';
        $sqlResult = DB::select($query);
        $results = [];
        if (empty($sqlResult) === false) {
            $results = DataParser::arrayObjectToArray($sqlResult);
        }

        return $results;
    }

    protected function loadAddtionalCallBackParameter(): void
    {
    }

}
