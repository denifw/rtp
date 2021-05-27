<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Ajax\Master;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo UnitOfMeasure.
 *
 * @package    app
 * @subpackage Model\Ajax\Master
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class Unit extends AbstractBaseAjaxModel
{
    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = '(' . StringFormatter::generateLikeQuery('uom_code', $this->getStringParameter('search_key')) . ' OR ' . StringFormatter::generateLikeQuery('uom_name', $this->getStringParameter('search_key')) . ')';
        if ($this->isValidParameter('uom_id') === true) {
            $wheres[] = '(uom_id = ' . $this->getIntParameter('uom_id') . ')';
        }
        $wheres[] = '(uom_deleted_on IS NULL)';
        $wheres[] = "(uom_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT uom_id, uom_name || ' (' || uom_code || ')' as uom_description
                        FROM unit " . $strWhere;
        $query .= ' ORDER BY uom_name, uom_code, uom_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'uom_description', 'uom_id');
    }
}
