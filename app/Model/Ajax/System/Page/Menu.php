<?php
/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 12/04/2019
 * Time: 13:10
 */

namespace App\Model\Ajax\System\Page;


use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;

class Menu extends AbstractBaseAjaxModel
{
    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('mn.mn_name', $this->getStringParameter('search_key'));
        $wheres[] = '(mn.mn_deleted_on IS NULL)';
        $wheres[] = "(mn.mn_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT mn.mn_id, (CASE WHEN m1.mn_name is NULL THEN mn.mn_name ELSE m1.mn_name || ' - ' || mn.mn_name END) as mn_name
                        FROM menu as mn LEFT OUTER JOIN 
                        menu as m1 ON mn.mn_parent = m1.mn_id " . $strWhere;

        $query .= ' ORDER BY mn.mn_name, mn.mn_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'mn_name', 'mn_id');
    }

}