<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Relation;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Relation\OfficeDao;

/**
 * Class to handle the ajax request fo Ajax.
 *
 * @package    app
 * @subpackage Model\Ajax\Crm\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Office extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('ofc.of_name', $this->getStringParameter('search_key'));
        if ($this->isValidParameter('of_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('ofc.of_id', $this->getIntParameter('of_id'));
        }
        if ($this->isValidParameter('of_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('ofc.of_rel_id', $this->getIntParameter('of_rel_id'));
        }
        if ($this->isValidParameter('of_ss_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('rel.rel_ss_id', $this->getIntParameter('of_ss_id'));
            $wheres[] = SqlHelper::generateStringCondition('rel.rel_owner', 'Y');
        }
        if ($this->isValidParameter('of_invoice') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ofc.of_invoice', $this->getStringParameter('of_invoice'));
        }
        $wheres[] = SqlHelper::generateStringCondition('ofc.of_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('ofc.of_deleted_on');
        return OfficeDao::loadSingleSelectData($wheres);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadOfficeAddress(): array
    {
        $wheres = [];
        if ($this->isValidParameter('of_rel_id') === true) {
            $wheres[] = '(o.of_rel_id = ' . $this->getIntParameter('of_rel_id') . ')';
        }
        $wheres[] = "((o.of_address IS NOT NULL) OR (o.of_address <> '') )";
        $wheres[] = "(o.of_active = 'Y')";
        $wheres[] = '(o.of_deleted_on IS NULL)';

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT o.of_id, o.of_address
                    FROM office as o INNER JOIN
                     relation as rel ON o.of_rel_id = rel.rel_id ' . $strWhere;
        $query .= ' ORDER BY o.of_address, o.of_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'of_address', 'of_id');
    }


}
