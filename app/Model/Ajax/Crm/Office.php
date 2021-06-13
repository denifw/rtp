<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Crm;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Crm\OfficeDao;

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
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('ofc.of_name', $this->getStringParameter('search_key'));
        }
        if ($this->isValidParameter('of_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ofc.of_id', $this->getStringParameter('of_id'));
        }
        if ($this->isValidParameter('of_rel_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ofc.of_rel_id', $this->getStringParameter('of_rel_id'));
        }
        if ($this->isValidParameter('of_ss_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('rel.rel_ss_id', $this->getStringParameter('of_ss_id'));
            $wheres[] = SqlHelper::generateStringCondition('rel.rel_owner', 'Y');
        }
        if ($this->isValidParameter('of_invoice') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ofc.of_invoice', $this->getStringParameter('of_invoice'));
        }
        $wheres[] = SqlHelper::generateStringCondition('ofc.of_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('ofc.of_deleted_on');
        return OfficeDao::loadSingleSelectData($wheres);
    }

}
