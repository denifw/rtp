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
use App\Model\Dao\Master\Finance\CostCodeDao;

/**
 * Class to handle the ajax request fo CostCode.
 *
 * @package    app
 * @subpackage Model\Ajax\Master
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class CostCode extends AbstractBaseAjaxModel
{
    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('cc_ss_id')) {
            $helper = new SqlHelper();
            $helper->addStringWhere('cc.cc_ss_id', $this->getStringParameter('cc_ss_id'));
            $helper->addOrLikeWhere(['cc.cc_code', 'cc.cc_name'], $this->getStringParameter('search_key'));
            $helper->addStringWhere('cc.cc_active', 'Y');
            $helper->addNullWhere('cc.cc_deleted_on');

            $helper->addLikeWhere('ccg.ccg_code', $this->getStringParameter('cc_group_code'));
            $helper->addLikeWhere('ccg.ccg_name', $this->getStringParameter('cc_group_name'));
            $helper->addStringWhere('ccg.ccg_type', $this->getStringParameter('cc_ccg_type'));
            $helper->addStringWhere('ccg.ccg_active', 'Y');
            $helper->addNullWhere('ccg.ccg_deleted_on');
            return CostCodeDao::loadSingleSelectData(['cc_code', 'cc_name'], $helper);
        }

        return [];
    }

}
