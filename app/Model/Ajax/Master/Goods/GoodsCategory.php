<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Master\Goods;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo GoodsCategory.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Goods
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class GoodsCategory extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('gdc_ss_id') === true) {
            $wheres = [];
            $wheres[] = StringFormatter::generateLikeQuery('gdc_name', $this->getStringParameter('search_key'));
            $wheres[] = '(gdc_ss_id = ' . $this->getIntParameter('gdc_ss_id') . ')';
            $wheres[] = '(gdc_deleted_on IS NULL)';
            $wheres[] = "(gdc_active = 'Y')";
            if ($this->isValidParameter('gd_rel_id') === true) {
                $wheres[] = '(gdc_id IN (SELECT gd_gdc_id 
                                            FROM goods WHERE (gd_deleted_on IS NULL) AND (gd_rel_id = '.$this->getIntParameter('gd_rel_id').')
                                            GROUP BY gd_gdc_id))';
            }
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT gdc_id, gdc_name
                    FROM goods_category' . $strWhere;
            $query .= ' ORDER BY gdc_name';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'gdc_name', 'gdc_id');
        }

        return [];
    }
}
