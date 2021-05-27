<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo customsDocumentType.
 *
 * @package    app
 * @subpackage Model\Ajax\System
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class CustomsDocumentType extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('cdt_name', $this->getStringParameter('search_key'));
        $wheres[] = '(cdt_active = \'Y\')';
        $wheres[] = '(cdt_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT cdt_id, cdt_name
                    FROM customs_document_type' . $strWhere;
        $query .= ' ORDER BY cdt_name';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'cdt_name', 'cdt_id');
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectTableData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('cdt_name', $this->getStringParameter('search_key'));
        if($this->isValidParameter('cdt_name') === true){
            $wheres[] = SqlHelper::generateLikeCondition('cdt.cdt_name',$this->getStringParameter('cdt_name'));
        }
        $wheres[] = '(cdt_active = \'Y\')';
        $wheres[] = '(cdt_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT cdt.cdt_id, cdt.cdt_name
                    FROM customs_document_type cdt' . $strWhere;
        $query .= ' ORDER BY cdt_name';
        $query .= ' LIMIT 30 OFFSET 0';
        $result = DB::select($query);
        return  DataParser::arrayObjectToArray($result);
    }
}
