<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Job\Warehouse;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo StockOpname.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class StockOpname extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('jo_ss_id') === true) {
            $wheres = [];
            $wheres[] = '(jo.jo_deleted_on IS NULL)';
            $wheres[] = '(sop.sop_end_on IS NOT NULL)';
            $wheres[] = StringFormatter::generateLikeQuery('jo.jo_number', $this->getStringParameter('search_key'));
            $wheres[] = '(jo.jo_ss_id = ' . $this->getIntParameter('jo_ss_id') . ')';
            if ($this->isValidParameter('jo_rel_id') === true) {
                $wheres[] = '(jo.jo_rel_id = ' . $this->getIntParameter('jo_rel_id') . ')';
            }
            if ($this->isValidParameter('sop_wh_id') === true) {
                $wheres[] = '(sop.sop_wh_id = ' . $this->getIntParameter('sop_wh_id') . ')';
            }

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jo.jo_id, jo.jo_number, sop.sop_date 
                    FROM job_order as jo INNER JOIN
                    stock_opname as sop ON jo.jo_id = sop.sop_jo_id ' . $strWhere;
            $query .= ' ORDER BY jo.jo_number DESC, jo.jo_id';
            $query .= ' LIMIT 30 OFFSET 0';
            $sqlResult = DB::select($query);
            $result = [];
            if (empty($sqlResult) === false) {
                $data = DataParser::arrayObjectToArray($sqlResult, [
                    'jo_id',
                    'jo_number',
                    'sop_date',
                ]);
                foreach ($data as $row) {
                    $text = $row['jo_number'];
                    if (empty($row['sop_date']) === false) {
                        $text .= ' - ' . DateTimeParser::format($row['sop_date'], 'Y-m-d', 'd.M.Y');
                    }
                    $result[] = [
                        'text' => $text,
                        'value' => $row['jo_id']
                    ];
                }
            }

            return $result;
        }

        return [];
    }
}
