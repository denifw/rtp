<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System\Location;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo Port.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Location
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Port extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('po_name', $this->getStringParameter('search_key'));
        if ($this->isValidParameter('po_tm_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('po.po_tm_id', $this->getIntParameter('po_tm_id'));
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT po_id, po_code, po_name || \' - \' || cnt_name || \' - \' || tm_name AS po_name, cnt.cnt_name as po_country, cty.cty_name as po_city
                  FROM port as po
                      INNER JOIN transport_module as tm ON tm.tm_id = po.po_tm_id
                      INNER JOIN country as cnt ON cnt.cnt_id = po.po_cnt_id
                      LEFT OUTER JOIN city as cty ON po.po_cty_id = cty.cty_id' . $strWhere;
        $query .= ' ORDER BY po.po_name, po.po_id';
        $query .= ' LIMIT 30 OFFSET 0';
        return $this->loadDataForSingleSelect($query, 'po_name', 'po_id', true);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectAutoComplete(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateOrLikeCondition(['po.po_code', 'po.po_name'], $this->getStringParameter('search_key'));
        if ($this->isValidParameter('po_tm_id') === true) {
            $wheres[] = '(po.po_tm_id = ' . $this->getIntParameter('po_tm_id') . ')';
        }

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT po.po_id, po.po_name || ' - ' || cnt.cnt_name  AS po_name, po.po_code, cnt.cnt_name as po_country
                  FROM port as po INNER JOIN
                       transport_module as tm ON tm.tm_id = po.po_tm_id INNER JOIN
                       country as cnt ON cnt.cnt_id = po.po_cnt_id" . $strWhere;
        $query .= ' ORDER BY po.po_name, po.po_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'po_name', 'po_id', true);
    }

}
