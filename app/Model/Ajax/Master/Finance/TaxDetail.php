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

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\Finance\TaxDetailDao;

/**
 * Class to handle the ajax request fo Tax.
 *
 * @package    app
 * @subpackage Model\Ajax\System
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class TaxDetail extends AbstractBaseAjaxModel
{
    /**
     * Function to load page
     *
     * @return array
     */
    public function getByReference(): array
    {
        $results = [];
        if ($this->isValidParameter('td_id') === true) {
            $results = TaxDetailDao::getByReference($this->getStringParameter('td_id'));
            if (empty($results) === false) {
                $number = new NumberFormatter();
                $results['td_percent_number'] = $number->doFormatFloat((float)$results['td_percent']);
            }
        }

        return $results;
    }
}
