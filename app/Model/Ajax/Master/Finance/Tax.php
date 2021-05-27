<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Ajax\Master\Finance;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo Tax.
 *
 * @package    app
 * @subpackage Model\Ajax\System
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class Tax extends AbstractBaseAjaxModel
{
    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('tax_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('tax_name', $this->getStringParameter('search_key'));
            $wheres[] = '(tax_deleted_on IS NULL)';
            $wheres[] = "(tax_id IN (select td_tax_id
                                    FROM tax_detail
                                    where td_active = 'Y' and td_deleted_on IS NULL
                                    group by td_tax_id))";
            $wheres[] = "(tax_active = 'Y')";

            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT tax_id, tax_name, (CASE WHEN tax_percent is null then 0 else tax_percent END) as tax_percent
                            FROM tax as t LEFT OUTER JOIN
                                (select td_tax_id, SUM(td_percent) as tax_percent
                                from tax_detail 
                                where td_active = \'Y\' and td_deleted_on is null
                                group by td_tax_id) as td ON t.tax_id = td.td_tax_id' . $strWhere;
            $query .= ' ORDER BY tax_name, tax_id';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'tax_name', 'tax_id', true);
        }
        return [];
    }

}
