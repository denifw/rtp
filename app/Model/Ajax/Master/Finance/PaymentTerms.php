<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Project
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Ajax\Master\Finance;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\Finance\PaymentTermsDao;

/**
 * Class to handle the ajax request fo PaymentTerms.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Finance
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class PaymentTerms extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for PaymentTerms
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('pt_ss_id')) {
            $wheres = [];
            if ($this->isValidParameter('search_key') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('pt_name', $this->getStringParameter('search_key'));
            }
            $wheres[] = SqlHelper::generateStringCondition('pt_ss_id', $this->getStringParameter('pt_ss_id'));
            $wheres[] = SqlHelper::generateStringCondition('pt_active', 'Y');
            $wheres[] = SqlHelper::generateNullCondition('pt_deleted_on');
            return PaymentTermsDao::loadSingleSelectData('pt_name', $wheres);
        }
        return [];
    }

}
