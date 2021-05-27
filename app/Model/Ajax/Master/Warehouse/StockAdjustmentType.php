<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Master\Warehouse;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo StockAdjustmentType.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class StockAdjustmentType extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = '(' . StringFormatter::generateLikeQuery('sat_description', $this->getStringParameter('search_key')) . ' OR ' . StringFormatter::generateLikeQuery('sat_code', $this->getStringParameter('search_key')) . ')';

        $wheres[] = '(sat_deleted_on IS NULL)';
        $wheres[] = '(sat_ss_id = ' . $this->getIntParameter('sat_ss_id') . ')';
        $wheres[] = "(sat_active = 'Y')";

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sat_id, sat_description
                    FROM stock_adjustment_type' . $strWhere;
        $query .= ' ORDER BY sat_description';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'sat_description', 'sat_id');
    }
}
