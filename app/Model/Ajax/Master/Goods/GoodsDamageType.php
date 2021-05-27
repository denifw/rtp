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
use App\Frame\Mvc\AbstractBaseAjaxModel;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo GoodsDamageType.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class GoodsDamageType extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('gdt_ss_id') === true) {
            $wheres = [];
            $strOrWheres = StringFormatter::generateOrLikeQuery($this->getStringParameter('search_key', ''), [
                'gdt_code', 'gdt_description'
            ]);
            if(empty($strOrWheres) === false) {
                $wheres[] = $strOrWheres;
            }

            $wheres[] = '(gdt_deleted_on IS NULL)';
            $wheres[] = '(gdt_ss_id = ' . $this->getIntParameter('gdt_ss_id') . ')';
            $wheres[] = "(gdt_active = 'Y')";

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT gdt_id, gdt_description, gdt_code
                    FROM goods_damage_type' . $strWhere;
            $query .= ' ORDER BY gdt_code, gdt_id';
            $query .= ' LIMIT 30 OFFSET 0';
            $sqlResults = DB::select($query);
            $result = [];
            if(empty($sqlResults) === false) {
                $data = DataParser::arrayObjectToArray($sqlResults);
                foreach ($data as $row) {
                    $row['gdt_description'] = $row['gdt_code'].' ' . $row['gdt_description'];
                    $result[] = $row;
                }
            }
            return $this->doPrepareSingleSelectData($result, 'gdt_description', 'gdt_id');
        }

        return [];
    }
}
