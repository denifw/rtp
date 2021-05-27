<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\System;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo CustomsClearanceType.
 *
 * @package    app
 * @subpackage Model\Ajax\System
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class CustomsClearanceType extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for CustomsClearanceType
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('cct_name', $this->getStringParameter('search_key'));
        $wheres[] = '(cct_active = \'Y\')';
        $wheres[] = '(cct_deleted_on IS NULL)';

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT cct_id, cct_name, cct_code
                    FROM customs_clearance_type' . $strWhere;
        $query .= ' ORDER BY cct_name, cct_id';
        $query .= ' LIMIT 30 OFFSET 0';
        return $this->loadDataForSingleSelect($query, 'cct_name', 'cct_id', true);
    }

    /**
     * Function to load the data for single select for CustomsClearanceType
     *
     * @return array
     */
    public function loadSingleSelectTableData(): array
    {
        $wheres = [];
        $wheres[] = '(cct_active = \'Y\')';
        $wheres[] = '(cct_deleted_on IS NULL)';

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT cct.cct_id, cct.cct_name
                    FROM customs_clearance_type cct' . $strWhere;
        $query .= ' ORDER BY cct_name';
        $query .= ' LIMIT 30 OFFSET 0';
        $result = DB::select($query);
        return DataParser::arrayObjectToArray($result);
    }
}
