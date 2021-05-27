<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Fms\Master;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo ServiceTask.
 *
 * @package    app
 * @subpackage Model\Ajax\Fms\Master
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class ServiceTask extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('svt_ss_id') === true) {
            $wheres = [];
            $wheres[] = StringFormatter::generateLikeQuery('svt_name', $this->getStringParameter('search_key'));
            $wheres[] = '(svt_ss_id = ' . $this->getIntParameter('svt_ss_id') . ')';
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT svt_id, svt_name
                    FROM service_task' . $strWhere;
            $query .= ' ORDER BY svt_name';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'svt_name', 'svt_id');
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadAutoCompleteDataForServiceReminder(): array
    {
        if ($this->isValidParameter('svt_ss_id') === true) {
            $wheres = [];
            $wheres[] = StringFormatter::generateLikeQuery('svt_name', $this->getStringParameter('search_key'));
            $wheres[] = '(svt_ss_id = ' . $this->getIntParameter('svt_ss_id') . ')';
            $wheres[] = '(svt_active = \'Y\')';
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT svt_id, svt_name, svo.svo_start_service_date, svo.svo_meter, svo.eq_primary_meter
                      FROM   service_task AS svt LEFT OUTER JOIN
                             (SELECT MAX(svo_meter) AS svo_meter, svd_svt_id, eq_primary_meter, MAX(svo_start_service_date) AS svo_start_service_date
                              FROM   service_order INNER JOIN
                                     service_order_detail ON svd_svo_id = svo_id INNER JOIN 
                                     equipment ON eq_id = svo_eq_id
                              WHERE svo_eq_id = ' . $this->getIntParameter('svt_eq_id') . ' AND
                                    svo_start_service_date IS NOT NULL AND svo_deleted_on IS NULL
                               GROUP BY  svd_svt_id, eq_primary_meter) AS svo ON svo.svd_svt_id = svt.svt_id' . $strWhere;
            $query .= ' ORDER BY svt_name';
            $query .= ' LIMIT 30 OFFSET 0';
            $results = [];
            $data = DB::select($query);
            if (empty($data) === false) {
                $numberFormatter = new NumberFormatter();
                $tempResult = DataParser::arrayObjectToArray($data);
                foreach ($tempResult AS $row) {
                    $serviceMeter = '';
                    $serviceDate = '';
                    $indicator = '';
                    $svtService = 'The equipment never did this service: ' . $row['svt_name'];
                    if (empty($row['svo_start_service_date']) === false && empty($row['svo_meter']) === false) {
                        if ($row['eq_primary_meter'] === 'km') {
                            $indicator = 'Odometer';
                        } elseif ($row['eq_primary_meter'] === 'hours') {
                            $indicator = 'Hours Meter';
                        }
                        if (empty($row['svo_meter']) === false) {
                            $serviceMeter = $numberFormatter->doFormatFloat($row['svo_meter']);
                        }
                        if (empty($row['svo_start_service_date']) === false) {
                            $serviceDate = DateTimeParser::format($row['svo_start_service_date'],'Y-m-d', 'd M Y');
                        }
                        $svtService = 'Last service : ' . $serviceDate . ' || ' . $indicator . ' on ' . $serviceMeter . ' ' . $row['eq_primary_meter'];
                    }
                    $row['svt_service'] = $svtService;
                    foreach ($row AS $key => $value) {
                        $result[$key] = $value;
                    }
                    $result['text'] = $row['svt_name'];
                    $result['value'] = $row['svt_id'];
                    $results[] = $result;
                }
            }

            # return the data.
            return $results;
        }

        return [];

    }
}
