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
use App\Model\Dao\Fms\ServiceOrderDetailDao;

/**
 * Class to handle the ajax request fo ServiceOrderDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Fms
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class ServiceOrderDetail extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for modal form
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('svd_id') === true) {
            $number = new NumberFormatter();
            $tempResult = ServiceOrderDetailDao::getByReference($this->getIntParameter('svd_id'));
            if (empty($tempResult) === false) {
                $result = $tempResult;
                $result['svd_est_cost_number'] = $number->doFormatCurrency($result['svd_est_cost']);

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
        if ($this->isValidParameter('svd_id') === true) {
            $number = new NumberFormatter();
            $serviceDetail = ServiceOrderDetailDao::getByReference($this->getIntParameter('svd_id'));
            if (empty($serviceDetail) === false) {
                $keys = array_keys($serviceDetail);
                foreach ($keys as $key) {
                    if ($key === 'svd_est_cost') {
                        $result[$key . '_del_number'] = $number->doFormatCurrency($serviceDetail[$key]);
                    }
                    $result[$key . '_del'] = $serviceDetail[$key];
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
    public function loadServiceOrderDetailData(): array
    {
        $wheres = [];
        $wheres[] = '(svd.svd_svo_id = ' . $this->getIntParameter('svd_svo_id') . ')';
        $wheres[] = '(svd.svd_id NOT IN (SELECT svc_svd_id FROM service_order_cost))';
        $wheres[] = '(svd.svd_deleted_on IS NULL)';
        $numberFormatter = new NumberFormatter();
        $results = [];
        $tempData = ServiceOrderDetailDao::loadData($wheres);
        foreach ($tempData AS $row) {
            $row['svd_est_cost'] = $numberFormatter->doFormatFloat($row['svd_est_cost']);
            $results[] = $row;
        }
        return $results;
    }
}
