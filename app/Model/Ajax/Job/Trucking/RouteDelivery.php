<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Job\Trucking;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\Trucking\RouteDeliveryDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo RouteDelivery.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\test
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class RouteDelivery extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for RouteDelivery
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('', $this->getStringParameter('search_key'));


        return RouteDeliveryDao::loadSingleSelectData($wheres);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectTableData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('rd_code') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('rd.rd_code', $this->getStringParameter('rd_code'));
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT rd.rd_code, rd.rd_dtc_or_id, rd.rd_dtc_des_id, rd.rd_drive_time, rd.rd_distance,
                            dtc_or.dtc_name as rd_dtc_name, dtc_des.dtc_name as dtc_des_rd_name
                        FROM route_delivery as rd
                        INNER JOIN district as dtc_or on rd.rd_dtc_or_id = dtc_or.dtc_id
                        INNER JOIN district as dtc_des on rd.rd_dtc_des_id = dtc_des.dtc_id' . $strWhere;
        $query .= ' ORDER BY rd.rd_code';
        $query .= ' LIMIT 50 OFFSET 0';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

}
