<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Fms;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Fms\RenewalOrderDetailDao;

/**
 * Class to handle the ajax request fo RenewalOrderDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class RenewalOrderDetail extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for modal form
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('rnd_id') === true) {
            $number = new NumberFormatter();
            $tempResult = RenewalOrderDetailDao::getByReference($this->getIntParameter('rnd_id'));
            if (empty($tempResult) === false) {
                $result = $tempResult;
                $result['rnd_est_cost_number'] = $number->doFormatCurrency($result['rnd_est_cost']);

                return $result;
            }

            return [];
        }

        return [];
    }


    /**
     * Function to load the data for modal form delete
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('rnd_id') === true) {
            $number = new NumberFormatter();
            $renewalDetail = RenewalOrderDetailDao::getByReference($this->getIntParameter('rnd_id'));
            if (empty($renewalDetail) === false) {
                $keys = array_keys($renewalDetail);
                foreach ($keys as $key) {
                    if ($key === 'rnd_est_cost') {
                        $result[$key . '_del_number'] = $number->doFormatCurrency($renewalDetail[$key]);
                    }
                    $result[$key . '_del'] = $renewalDetail[$key];
                }
            }
        }

        return $result;
    }

    /**
     * Function to load the data for modal form delete
     *
     * @return array
     */
    public function loadRenewalOrderDetailData(): array
    {
        $wheres = [];
        $wheres[] = '(rnd.rnd_rno_id = ' . $this->getIntParameter('rnd_rno_id') . ')';
        $wheres[] = '(rnd.rnd_id NOT IN (SELECT rnc_rnd_id FROM renewal_order_cost))';
        $wheres[] = '(rnd.rnd_deleted_on IS NULL)';
        $numberFormatter = new NumberFormatter();
        $results = [];
        $tempData = RenewalOrderDetailDao::loadData($wheres);
        foreach ($tempData AS $row) {
            $row['rnd_est_cost'] = $numberFormatter->doFormatFloat($row['rnd_est_cost']);
            $results[] = $row;
        }
        return $results;
    }
}
