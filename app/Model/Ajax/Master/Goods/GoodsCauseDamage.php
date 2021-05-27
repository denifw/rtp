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
 * Class to handle the ajax request fo GoodsCauseDamage.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class GoodsCauseDamage extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if($this->isValidParameter('gcd_ss_id')) {
            $wheres = [];
            $wheres[] = '(' . StringFormatter::generateLikeQuery('gcd_description', $this->getStringParameter('search_key')) . ' OR ' . StringFormatter::generateLikeQuery('gcd_code', $this->getStringParameter('search_key')) . ')';

            $wheres[] = '(gcd_deleted_on IS NULL)';
            $wheres[] = '(gcd_ss_id = ' . $this->getIntParameter('gcd_ss_id') . ')';
            $wheres[] = "(gcd_active = 'Y')";

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT gcd_id, gcd_code, gcd_description
                    FROM goods_cause_damage' . $strWhere;
            $query .= ' ORDER BY gcd_code, gcd_id';
            $query .= ' LIMIT 30 OFFSET 0';

            $sqlResults = DB::select($query);
            $result = [];
            if(empty($sqlResults) === false) {
                $data = DataParser::arrayObjectToArray($sqlResults);
                foreach ($data as $row) {
                    $row['gcd_description'] = $row['gcd_code'].' ' . $row['gcd_description'];
                    $result[] = $row;
                }
            }
            return $this->doPrepareSingleSelectData($result, 'gcd_description', 'gcd_id');
        }
        return [];
    }
}
