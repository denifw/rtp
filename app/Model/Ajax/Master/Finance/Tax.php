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
            $helper = new SqlHelper();
            $helper->addStringWhere('tax_ss_id', $this->getStringParameter('tax_ss_id'));
            $helper->addLikeWhere('tax_name', $this->getStringParameter('search_key'));
            $helper->addStringWhere('tax_group', $this->getStringParameter('tax_group'));
            $helper->addNullWhere('tax_deleted_on');
            return TaxDao::loadSingleSelectData('tax_name', $helper);
        }
        return [];
    }

}
