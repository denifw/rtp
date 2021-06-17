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
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\Finance\TaxDao;

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
            if ($this->isValidParameter('search_key') === true) {
                $wheres[] = SqlHelper::generateLikeCondition('tax_name', $this->getStringParameter('search_key'));
            }
            $wheres[] = SqlHelper::generateNullCondition('tax_deleted_on');
            $wheres[] = SqlHelper::generateNullCondition('tax_percent', false);
            $wheres[] = SqlHelper::generateStringCondition('tax_active', 'Y');
            return TaxDao::loadSingleSelectData('tax_name', $wheres);
        }
        return [];
    }

}
