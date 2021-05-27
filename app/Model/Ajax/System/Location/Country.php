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

use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo Country.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Country extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $searchKey1 = StringFormatter::generateLikeQuery('cnt_name', $this->getStringParameter('search_key'));
        $searchKey2 = StringFormatter::generateLikeQuery('cnt_iso', $this->getStringParameter('search_key'));
        $wheres[] = '(' . $searchKey1 . ' OR ' . $searchKey2 . ')';
        $wheres[] = '(cnt_deleted_on IS NULL)';
        $wheres[] = "(cnt_active = 'Y')";

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT cnt_id, cnt_name
                    FROM country ' . $strWhere;
        $query .= ' ORDER BY cnt_name';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'cnt_name', 'cnt_id');
    }
}
