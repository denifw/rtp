<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Master;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo WarehouseStorage.
 *
 * @package    app
 * @subpackage Model\Ajax\Master
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class WarehouseStorage extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('whs_wh_id') === true) {
            $wheres = [];
            $wheres[] = StringFormatter::generateLikeQuery('whs_name', $this->getStringParameter('search_key'));

            $wheres[] = '(whs_deleted_on IS NULL)';
            $wheres[] = '(whs_wh_id = ' . $this->getIntParameter('whs_wh_id') . ')';
            $wheres[] = "(whs_active = 'Y')";

            if ($this->isValidParameter('ignore_id') === true) {
                $wheres[] = '(whs_id <> ' . $this->getIntParameter('ignore_id') . ')';
            }

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT whs_id, whs_name
                    FROM warehouse_storage' . $strWhere;
            $query .= ' ORDER BY whs_name';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'whs_name', 'whs_id');
        }

        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('whs_id') === true) {
            $wheres = [];
            $wheres[] = StringFormatter::generateLikeQuery('whs_name', $this->getStringParameter('search_key'));

            $wheres[] = '(whs_id = ' . $this->getIntParameter('whs_id') . ')';

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT whs_id, whs_name, whs_length, whs_width, whs_height, whs_volume, whs_active
                    FROM warehouse_storage ' . $strWhere;
            $query .= ' ORDER BY whs_name';
            $sqlResult = DB::select($query);
            if (\count($sqlResult) === 1) {
                $number = new NumberFormatter();
                $result = DataParser::objectToArray($sqlResult[0], [
                    'whs_id',
                    'whs_wh_id',
                    'whs_name',
                    'whs_length',
                    'whs_width',
                    'whs_height',
                    'whs_volume',
                    'whs_active',
                ]);
                $result['whs_length_number'] = $number->doFormatCurrency($result['whs_length']);
                $result['whs_width_number'] = $number->doFormatCurrency($result['whs_width']);
                $result['whs_height_number'] = $number->doFormatCurrency($result['whs_height']);
                $result['whs_volume_number'] = $number->doFormatCurrency($result['whs_volume']);

                return $result;
            }
        }

        return [];
    }
}
