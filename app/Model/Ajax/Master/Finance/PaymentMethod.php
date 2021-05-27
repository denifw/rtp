<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Master\Finance;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo Bank.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Finance
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class PaymentMethod extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if($this->isValidParameter('pm_ss_id')) {
            $wheres = [];
            $wheres[] = StringFormatter::generateLikeQuery('pm_name', $this->getStringParameter('search_key'));
            $wheres[] = '(pm_ss_id = '.$this->getIntParameter('pm_ss_id').')';
            $wheres[] = '(pm_deleted_on is null)';
            $wheres[] = "(pm_active = 'Y')";
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT pm_id, pm_name
                    FROM payment_method ' . $strWhere;
            $query .= ' ORDER BY pm_name, pm_id';
            $query .= ' LIMIT 30 OFFSET 0';
            return $this->loadDataForSingleSelect($query, 'pm_name', 'pm_id');
        }
        return [];
    }
}
