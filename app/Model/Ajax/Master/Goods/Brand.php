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
 * Class to handle the ajax request fo Brand.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Goods
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Brand extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('br_ss_id') === true) {
            $wheres = [];
            $wheres[] = StringFormatter::generateLikeQuery('br_name', $this->getStringParameter('search_key'));
            $wheres[] = '(br_ss_id = ' . $this->getIntParameter('br_ss_id') . ')';
            $wheres[] = '(br_deleted_on IS NULL)';
            $wheres[] = "(br_active = 'Y')";
            if ($this->isValidParameter('gd_rel_id') === true) {
                $wheres[] = '(br_id IN (SELECT gd_br_id 
                                            FROM goods WHERE (gd_deleted_on IS NULL) AND (gd_rel_id = ' . $this->getIntParameter('gd_rel_id') . ')
                                            GROUP BY gd_br_id))';
            }
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT br_id, br_name
                    FROM brand' . $strWhere;
            $query .= ' ORDER BY br_name';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'br_name', 'br_id');
        }

        return [];
    }
}
