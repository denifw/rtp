<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System\Location;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo District.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class District extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('dtc_name', $this->getStringParameter('search_key'));
        $wheres[] = '(dtc_deleted_on IS NULL)';
        $wheres[] = "(dtc_active = 'Y')";
        if ($this->isValidParameter('dtc_cnt_id') === true) {
            $wheres[] = '(dtc_cnt_id = ' . $this->getIntParameter('dtc_cnt_id') . ')';
        }
        if ($this->isValidParameter('dtc_stt_id') === true) {
            $wheres[] = '(dtc_stt_id = ' . $this->getIntParameter('dtc_stt_id') . ')';
        }
        if ($this->isValidParameter('dtc_cty_id') === true) {
            $wheres[] = '(dtc_cty_id = ' . $this->getIntParameter('dtc_cty_id') . ')';
        }

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT dtc_id, dtc_name
                    FROM district' . $strWhere;
        $query .= ' ORDER BY dtc_name, dtc_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'dtc_name', 'dtc_id');
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectAutoComplete(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('dtc_name', $this->getStringParameter('search_key'));
        $wheres[] = '(dtc.dtc_deleted_on IS NULL)';
        $wheres[] = "(dtc.dtc_active = 'Y')";
        if ($this->isValidParameter('dtc_cnt_id') === true) {
            $wheres[] = '(dtc.dtc_cnt_id = ' . $this->getIntParameter('dtc_cnt_id') . ')';
        }
        if ($this->isValidParameter('dtc_stt_id') === true) {
            $wheres[] = '(dtc.dtc_stt_id = ' . $this->getIntParameter('dtc_stt_id') . ')';
        }
        if ($this->isValidParameter('dtc_cty_id') === true) {
            $wheres[] = '(dtc.dtc_cty_id = ' . $this->getIntParameter('dtc_cty_id') . ')';
        }

        # District Code Where
        $dtccWheres = [];
        $dtccWheres[] = '(dtcc_deleted_on IS NULL)';
        if ($this->isValidParameter('dtcc_ss_id') === true) {
            $dtccWheres[] = SqlHelper::generateNumericCondition('dtcc_ss_id', $this->getIntParameter('dtcc_ss_id'));
        }
        $strDtccWheres = ' WHERE ' . implode(' AND ', $dtccWheres);

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT dtc.dtc_id, dtc.dtc_name || \', \' || cty.cty_name || \', \' || stt.stt_name || \', \' || cnt.cnt_name AS dtc_name, 
                        dtcc.dtcc_id as dtc_dtcc_id, dtcc.dtcc_code as dtc_dtcc_code, stt.stt_name as dtc_state, cty.cty_name as dtc_city
                    FROM district AS dtc INNER JOIN
                         city AS cty ON cty.cty_id = dtc.dtc_cty_id INNER JOIN
                         state AS stt ON stt.stt_id = cty.cty_stt_id INNER JOIN
                         country AS cnt ON cnt.cnt_id = stt.stt_cnt_id LEFT OUTER JOIN
                        (select dtcc_id, dtcc_code, dtcc_dtc_id
                            FROM district_code ' . $strDtccWheres . ') as dtcc ON dtc.dtc_id = dtcc_dtc_id' . $strWhere;
        $query .= ' ORDER BY dtc.dtc_name, cty.cty_name, stt.stt_name, cnt.cnt_name, dtc.dtc_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'dtc_name', 'dtc_id', true);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadCompleteSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('dtc_name', $this->getStringParameter('search_key'));
        $wheres[] = '(dtc.dtc_deleted_on IS NULL)';
        $wheres[] = "(dtc.dtc_active = 'Y')";
        if ($this->isValidParameter('dtc_cnt_id') === true) {
            $wheres[] = '(dtc.dtc_cnt_id = ' . $this->getIntParameter('dtc_cnt_id') . ')';
        }
        if ($this->isValidParameter('dtc_stt_id') === true) {
            $wheres[] = '(dtc.dtc_stt_id = ' . $this->getIntParameter('dtc_stt_id') . ')';
        }
        if ($this->isValidParameter('dtc_cty_id') === true) {
            $wheres[] = '(dtc.dtc_cty_id = ' . $this->getIntParameter('dtc_cty_id') . ')';
        }

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT dtc.dtc_id, dtc.dtc_name || \', \' || cty.cty_name || \', \' || stt.stt_name || \', \' || cnt.cnt_name AS dtc_name
                    FROM district AS dtc INNER JOIN
                         city AS cty ON cty.cty_id = dtc.dtc_cty_id INNER JOIN
                         state AS stt ON stt.stt_id = cty.cty_stt_id INNER JOIN
                         country AS cnt ON cnt.cnt_id = stt.stt_cnt_id' . $strWhere;
        $query .= ' ORDER BY dtc.dtc_name, cty.cty_name, stt.stt_name, cnt.cnt_name, dtc.dtc_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'dtc_name', 'dtc_id');
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectTableData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('dtc_state') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('stt.stt_name', $this->getStringParameter('dtc_state'));
        }
        if ($this->isValidParameter('dtc_city') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('cty.cty_name', $this->getStringParameter('dtc_city'));
        }
        if ($this->isValidParameter('dtc_country') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('cnt.cnt_name', $this->getStringParameter('dtc_country'));
        }
        if ($this->isValidParameter('dtc_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dtc.dtc_name', $this->getStringParameter('dtc_name'));
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT dtc.dtc_id, dtc.dtc_cnt_id, cnt.cnt_name as dtc_country, dtc.dtc_stt_id, stt.stt_name as dtc_state,
                        dtc.dtc_cty_id, cty.cty_name as dtc_city, dtc.dtc_name, dtc.dtc_iso, dtc.dtc_active
                        FROM district as dtc INNER JOIN
                        city as cty ON dtc.dtc_cty_id = cty.cty_id INNER JOIN
                        state as stt ON dtc.dtc_stt_id = stt.stt_id INNER JOIN
                        country as cnt ON dtc.dtc_cnt_id = cnt.cnt_id ' . $strWhere;
        $query .= ' ORDER BY cnt.cnt_name, stt.stt_name, cty.cty_name, dtc.dtc_name, dtc.dtc_id';
        $query .= ' LIMIT 50 OFFSET 0';
        $result = DB::select($query);

        return DataParser::arrayObjectToArray($result);
    }

}
