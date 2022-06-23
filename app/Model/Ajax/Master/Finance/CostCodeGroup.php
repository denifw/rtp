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
use App\Model\Dao\Master\Finance\CostCodeGroupDao;

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
            $helper = new SqlHelper();
            $helper->addStringWhere('ccg_ss_id', $this->getStringParameter('ccg_ss_id'));
            $helper->addStringWhere('ccg_active', 'Y');
            $helper->addNullWhere('ccg_deleted_on');
            $helper->addOrLikeWhere(['ccg_code', 'ccg_name'], $this->getStringParameter('search_key'));
            return CostCodeGroupDao::loadSingleSelectData(['ccg_code', 'ccg_name'], $helper);
        }

        return [];
    }
}
