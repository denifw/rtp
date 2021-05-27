<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Relation;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Relation\RelationBankDao;

/**
 * Class to handle the ajax request fo RelationBank.
 *
 * @package    app
 * @subpackage Model\Ajax\Crm\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class RelationBank extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('rel_ss_id') && $this->isValidParameter('rb_rel_id')) {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('rb.rb_number', $this->getStringParameter('search_key'));
            $wheres[] = SqlHelper::generateNullCondition('rb.rb_deleted_on');
            $wheres[] = SqlHelper::generateStringCondition('rb.rb_active', 'Y');
            $wheres[] = SqlHelper::generateNumericCondition('rb.rb_rel_id', $this->getIntParameter('rb_rel_id'));
            $wheres[] = SqlHelper::generateNumericCondition('rel.rel_ss_id', $this->getIntParameter('rel_ss_id'));
            return RelationBankDao::loadSingleSelectData($wheres);
        }
        return [];
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('rb_id') === true) {
            return RelationBankDao::getByReference($this->getIntParameter('rb_id'));
        }
        return [];
    }
}
