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
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\Warehouse\JobOutboundDetailDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo JobOutboundDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobOutboundDetail extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('', $this->getStringParameter('search_key'));

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT
                    FROM ' . $strWhere;
        $query .= ' ORDER BY ';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, '', '');
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        if ($this->isValidParameter('jod_id') === true) {
            $temp = JobOutboundDetailDao::getByReference($this->getIntParameter('jod_id'));
            $number = new NumberFormatter();
            $result = [];
            if (empty($temp) === false) {
                $gdDao = new GoodsDao();
                $temp['jod_goods'] = $gdDao->formatFullName($temp['jod_gdc_name'], $temp['jod_br_name'], $temp['jod_gd_name']);
                $keys = array_keys($temp);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $temp[$key];
                }
            }

            $result['jod_quantity_del_number'] = $number->doFormatFloat($result['jod_quantity_del']);

            return $result;
        }

        return [];
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadDataForLoading(): array
    {
        if ($this->isValidParameter('job_id') === true) {
            $wheres = [];
            $wheres[] = '(jod.jod_job_id = ' . $this->getIntParameter('job_id') . ')';
            $wheres[] = '(jod.jod_qty_loaded IS NULL)';
            $temp = JobOutboundDetailDao::getDataForLoading($wheres);
            $number = new NumberFormatter();
            $result = [];
            foreach ($temp as $row) {
                $row['jodl_quantity_number'] = $number->doFormatFloat($row['jodl_quantity']);
                $result[] = $row;
            }
            return $result;
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReferenceForLoading(): array
    {
        $result = [];
        if ($this->isValidParameter('jodl_id') === true) {
            $wheres = [];
            $wheres[] = '(jod.jod_id = ' . $this->getIntParameter('jodl_id') . ')';
            $temp = JobOutboundDetailDao::getDataForLoading($wheres);
            if (count($temp) === 1) {
                $result = $temp[0];
                $number = new NumberFormatter();
                $result['jodl_quantity_number'] = $number->doFormatFloat($result['jodl_quantity']);
                $result['jodl_qty_loaded_number'] = $number->doFormatFloat($result['jodl_qty_loaded']);

            }
        }
        return $result;
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReferenceForUpdate(): array
    {
        if ($this->isValidParameter('jod_id') === true) {
            $temp = JobOutboundDetailDao::getByReference($this->getIntParameter('jod_id'));
            $number = new NumberFormatter();
            $result = [];
            if (empty($temp) === false) {
                $gdDao = new GoodsDao();
                $temp['jod_goods'] = $gdDao->formatFullName($temp['jod_gdc_name'], $temp['jod_br_name'], $temp['jod_gd_name']);
                $keys = array_keys($temp);
                foreach ($keys as $key) {
                    $result[$key . '_up'] = $temp[$key];
                }
            }
            $result['jod_quantity_up_number'] = $number->doFormatFloat($result['jod_quantity_up']);

            return $result;
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadLotNumberForBundling(): array
    {
        if ($this->isValidParameter('job_id') === true && $this->isValidParameter('jog_id') === true && $this->isValidParameter('jb_id') === true) {
            $wheres = [];
            $wheres[] = StringFormatter::generateLikeQuery('jid.jid_lot_number', $this->getStringParameter('search_key', ''));
            $wheres[] = '(jod.jod_job_id = ' . $this->getIntParameter('job_id') . ')';
            $wheres[] = '(jod.jod_jog_id = ' . $this->getIntParameter('jog_id') . ')';
            $wheres[] = '(jid.jid_lot_number IS NOT NULL)';
            $wheres[] = "(jid.jid_lot_number <> '')";
            $wheres[] = '(jod.jod_deleted_on IS NULL)';
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jid.jid_lot_number, SUM(jod.jod_quantity) as stock, (CASE WHEN jb.used IS NULL THEN 0 ELSE jb.used END) as total_used
                    FROM job_outbound_detail as jod INNER JOIN
                    job_inbound_detail as jid ON jod.jod_jid_id = jid.jid_id LEFT OUTER JOIN
                    (SELECT jbm_lot_number, SUM(jbm_quantity) as used
                        FROM job_bundling_detail as jbd INNER JOIN
                        job_bundling_material as jbm ON jbm.jbm_jbd_id = jbd.jbd_id 
                        WHERE (jbm.jbm_jog_id = ' . $this->getIntParameter('jog_id') . ')
                        AND (jbd.jbd_jb_id = ' . $this->getIntParameter('jb_id') . ') AND (jbm.jbm_deleted_on IS NULL) AND (jbd.jbd_deleted_on IS NULL)
                        AND (jbm.jbm_id <> ' . $this->getIntParameter('jbm_id') . ') AND (jbm.jbm_lot_number IS NOT NULL)
                        GROUP BY jbm_lot_number) as jb ON jid.jid_lot_number = jb.jbm_lot_number ' . $strWhere;
            $query .= ' GROUP BY jid.jid_lot_number, jb.used';
            $query .= ' ORDER BY jid.jid_lot_number';
            $query .= ' LIMIT 20 OFFSET 0';
            $sqlResults = DB::select($query);
            $result = [];
            if (empty($sqlResults) === false) {
                $data = DataParser::arrayObjectToArray($sqlResults);
                foreach ($data as $row) {
                    if ((float)$row['total_used'] < (float)$row['stock']) {
                        $result[] = [
                            'text' => $row['jid_lot_number'],
                            'value' => $row['jid_lot_number'],
                            'qty' => $row['stock'],
                            'used' => $row['total_used'],
                        ];
                    }
                }
                return $result;
            }

        }
        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSerialNumberForBundling(): array
    {
        if ($this->isValidParameter('job_id') === true && $this->isValidParameter('jog_id') === true && $this->isValidParameter('jb_id') === true) {
            $wheres = [];
            $wheres[] = StringFormatter::generateLikeQuery('jid.jid_serial_number', $this->getStringParameter('search_key', ''));
            $wheres[] = '(jod.jod_job_id = ' . $this->getIntParameter('job_id') . ')';
            $wheres[] = '(jod.jod_jog_id = ' . $this->getIntParameter('jog_id') . ')';
            $wheres[] = '(jid.jid_serial_number IS NOT NULL)';
            $wheres[] = "(jid.jid_serial_number <> '')";
            $wheres[] = '(jod.jod_deleted_on IS NULL)';
            if ($this->isValidParameter('lot_number') === true) {
                $wheres[] = "(jid.jid_lot_number = '" . $this->getStringParameter('lot_number') . "')";
            }
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT jid.jid_id, jid.jid_serial_number, SUM(jod.jod_quantity) as stock, (CASE WHEN jb.used IS NULL THEN 0 ELSE jb.used END) as total_used
                    FROM job_outbound_detail as jod INNER JOIN
                    job_inbound_detail as jid ON jod.jod_jid_id = jid.jid_id LEFT OUTER JOIN
                    (SELECT jbm_serial_number, SUM(jbm_quantity) as used
                        FROM job_bundling_detail as jbd INNER JOIN
                        job_bundling_material as jbm ON jbm.jbm_jbd_id = jbd.jbd_id 
                        WHERE (jbm.jbm_jog_id = ' . $this->getIntParameter('jog_id') . ')
                        AND (jbd.jbd_jb_id = ' . $this->getIntParameter('jb_id') . ') AND (jbm.jbm_deleted_on IS NULL) AND (jbd.jbd_deleted_on IS NULL)
                        AND (jbm.jbm_id <> ' . $this->getIntParameter('jbm_id') . ') AND (jbm.jbm_serial_number IS NOT NULL)
                        GROUP BY jbm_serial_number) as jb ON jid.jid_serial_number = jb.jbm_serial_number ' . $strWhere;
            $query .= ' GROUP BY jid.jid_id, jid.jid_serial_number, jb.used';
            $query .= ' ORDER BY jid.jid_serial_number';
            $query .= ' LIMIT 20 OFFSET 0';
            $sqlResults = DB::select($query);
            $result = [];
            if (empty($sqlResults) === false) {
                $data = DataParser::arrayObjectToArray($sqlResults);
                foreach ($data as $row) {
                    if ((float)$row['total_used'] < (float)$row['stock']) {
                        $result[] = [
                            'text' => $row['jid_serial_number'],
                            'value' => $row['jid_id'],
                            'qty' => $row['stock'],
                            'used' => $row['total_used'],
                        ];
                    }
                }
                return $result;
            }

        }
        return [];
    }
}
