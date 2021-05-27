<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Master\Goods;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\Goods\GoodsDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo Goods.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Goods extends AbstractBaseAjaxModel
{


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $result = [];
        if ($this->isValidParameter('gd_ss_id') === true) {
            $wheres = [];
            $wheres[] = '(' . StringFormatter::generateLikeQuery('gd_name', $this->getStringParameter('search_key')) . ' OR ' . StringFormatter::generateLikeQuery('gd_sku', $this->getStringParameter('search_key')) . ')';

            if ($this->isValidParameter('gd_rel_id') === true) {
                $wheres[] = '(gd_rel_id = ' . $this->getIntParameter('gd_rel_id') . ')';
            }
            if ($this->isValidParameter('gd_br_id') === true) {
                $wheres[] = '(gd_br_id = ' . $this->getIntParameter('gd_br_id') . ')';
            }
            if ($this->isValidParameter('gd_gdc_id') === true) {
                $wheres[] = '(gd_gdc_id = ' . $this->getIntParameter('gd_gdc_id') . ')';
            }
            $wheres[] = '(gd_ss_id = ' . $this->getIntParameter('gd_ss_id') . ')';
            $wheres[] = '(gd_deleted_on IS NULL)';
            $wheres[] = "(gd_active = 'Y')";

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = "SELECT gd_id, (gd_sku || ' - ' || gd_name) as text
                    FROM goods" . $strWhere;
            $query .= ' ORDER BY gd_name';
            $query .= ' LIMIT 30 OFFSET 0';
            $result = $this->loadDataForSingleSelect($query, 'text', 'gd_id');
        }

        return $result;
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadCompleteGoodsSingleSelect(): array
    {
        $result = [];
        if ($this->isValidParameter('gd_ss_id') === true) {
            $wheres = [];
            $strOrWheres = StringFormatter::generateOrLikeQuery($this->getStringParameter('search_key', ''), [
                'br.br_name', 'gdc.gdc_name', 'gd.gd_name', 'gd.gd_sku',
            ]);
            if (empty($strOrWheres) === false) {
                $wheres[] = $strOrWheres;
            }

            if ($this->isValidParameter('gd_rel_id') === true) {
                $wheres[] = '(gd.gd_rel_id = ' . $this->getIntParameter('gd_rel_id') . ')';
            }
            if ($this->isValidParameter('gd_id') === true) {
                $wheres[] = '(gd.gd_id = ' . $this->getIntParameter('gd_id') . ')';
            }
            $wheres[] = '(gd.gd_ss_id = ' . $this->getIntParameter('gd_ss_id') . ')';
            $wheres[] = '(gd.gd_deleted_on IS NULL)';
            $wheres[] = "(gd.gd_active = 'Y')";

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = "SELECT gd.gd_id, gd.gd_sku, gd.gd_name, br.br_name, gdc.gdc_name
                    FROM goods as gd INNER JOIN
                    brand as br ON br.br_id = gd.gd_br_id INNER JOIN
                    goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id " . $strWhere;
            $query .= ' ORDER BY gdc.gdc_name, br.br_name, gd.gd_name, gd.gd_id';
            $query .= ' LIMIT 30 OFFSET 0';
            $sqlResults = DB::select($query);
            $gdDao = new GoodsDao();
            if (empty($sqlResults) === false) {
                $data = DataParser::arrayObjectToArray($sqlResults);
                foreach ($data as $row) {
                    $row['gd_full_name'] = $gdDao->formatFullName($row['gdc_name'], $row['br_name'], $row['gd_name'], $row['gd_sku']);
                    $result[] = $row;
                }
            }
        }

        return $this->doPrepareSingleSelectData($result, 'gd_full_name', 'gd_id');
    }

    /**
     * Function to load the data for single select table
     *
     * @return array
     */
    public function loadSingleSelectTableData(): array
    {
        $result = [];
        if ($this->isValidParameter('gd_ss_id') === true) {
            $wheres = [];
            if ($this->isValidParameter('gd_id') === true) {
                $wheres[] = '(gd_id = ' . $this->getIntParameter('gd_id') . ')';
            }
            if ($this->isValidParameter('gd_ignore_id') === true) {
                $wheres[] = '(gd.gd_id <> ' . $this->getIntParameter('gd_ignore_id') . ')';
            }
            if ($this->isValidParameter('gd_sku') === true) {
                $wheres[] = StringFormatter::generateLikeQuery('gd.gd_sku', $this->getStringParameter('gd_sku'));
            }
            if ($this->isValidParameter('gd_name') === true) {
                $wheres[] = StringFormatter::generateLikeQuery('gd.gd_name', $this->getStringParameter('gd_name'));
            }
            if ($this->isValidParameter('br_name') === true) {
                $wheres[] = StringFormatter::generateLikeQuery('br.br_name', $this->getStringParameter('br_name'));
            }
            if ($this->isValidParameter('gdc_name') === true) {
                $wheres[] = StringFormatter::generateLikeQuery('gdc.gdc_name', $this->getStringParameter('gdc_name'));
            }
            if ($this->isValidParameter('gd_relation') === true) {
                $wheres[] = StringFormatter::generateLikeQuery('rel.rel_name', $this->getStringParameter('gd_relation'));
            }
            if ($this->isValidParameter('gd_rel_id') === true) {
                $wheres[] = '(gd.gd_rel_id = ' . $this->getIntParameter('gd_rel_id') . ')';
            }
            if ($this->isValidParameter('gd_bundling') === true) {
                $wheres[] = "(gd.gd_bundling = '" . $this->getStringParameter('gd_bundling') . "')";
            }
            $wheres[] = '(gd.gd_ss_id = ' . $this->getIntParameter('gd_ss_id') . ')';
            $wheres[] = '(gd.gd_deleted_on IS NULL)';
            $wheres[] = "(gd.gd_active = 'Y')";

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT gd.gd_id, gd.gd_sku, gd.gd_name, gd.gd_br_id, br.br_name as gd_br_name, gd.gd_gdc_id, gdc.gdc_name as gd_gdc_name,
                            rel.rel_name as gd_relation, gd.gd_sn
                    FROM goods as gd INNER JOIN
                    relation as rel ON rel.rel_id = gd.gd_rel_id INNER JOIN
                    brand as br ON gd.gd_br_id = br.br_id INNER JOIN
                    goods_category as gdc ON gd.gd_gdc_id = gdc.gdc_id ' . $strWhere;
            $query .= ' ORDER BY gd.gd_sku, gd.gd_id';
            $query .= ' LIMIT 50 OFFSET 0';
            $sqlResults = DB::select($query);
            if (empty($sqlResults) === false) {
                $temp = DataParser::arrayObjectToArray($sqlResults);
                $gdDao = new GoodsDao();
                foreach ($temp as $row) {
                    $row['gd_required_sn'] = new LabelDanger(Trans::getWord('no'));
                    if ($row['gd_sn'] === 'Y') {
                        $row['gd_required_sn'] = new LabelSuccess(Trans::getWord('yes'));
                    }
                    $row['gd_full_name'] = $gdDao->formatFullName($row['gd_gdc_name'], $row['gd_br_name'], $row['gd_name'], $row['gd_sku']);
                    $result[] = $row;
                }
            }
        }

        return $result;
    }
}
