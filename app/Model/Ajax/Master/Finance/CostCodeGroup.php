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

use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo CostCode.
 *
 * @package    app
 * @subpackage Model\Ajax\Master
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class CostCodeGroup extends AbstractBaseAjaxModel
{
    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('ccg_ss_id')) {
            $wheres = [];
            $wheres[] = '(' . StringFormatter::generateLikeQuery('ccg_code', $this->getStringParameter('search_key')) . ' OR ' . StringFormatter::generateLikeQuery('ccg_name', $this->getStringParameter('search_key')) . ')';
            $wheres[] = '(ccg_ss_id = ' . $this->getStringParameter('ccg_ss_id') . ')';
            $wheres[] = '(ccg_deleted_on IS NULL)';
            $wheres[] = "(ccg_active = 'Y')";
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = "SELECT ccg_id, (ccg_code || ' - ' || ccg_name) as ccg_text
                        FROM cost_code_group " . $strWhere;
            $query .= ' ORDER BY ccg_code, ccg_id';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'ccg_text', 'ccg_id');
        }

        return [];
    }
}
