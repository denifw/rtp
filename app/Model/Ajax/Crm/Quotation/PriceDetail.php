<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Crm\Quotation;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Crm\Quotation\PriceDetailDao;

/**
 * Class to handle the ajax request fo PriceDetail.
 *
 * @package    app
 * @subpackage Model\Ajax\Crm
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class PriceDetail extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for PriceDetail
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('', $this->getStringParameter('search_key'));
        return PriceDetailDao::loadSingleSelectData($wheres);
    }

    /**
     * @return array for update detail price
     */
    public function getPriceDetailById(): array
    {
        $result = [];
        if ($this->isValidParameter('prd_id') === true) {
            $result = PriceDetailDao::getByReference($this->getIntParameter('prd_id'));
            if (empty($result) === false) {
                $number = new NumberFormatter();
                $result['prd_quantity_number'] = $number->doFormatFloat((float)$result['prd_quantity']);
                $result['prd_rate_number'] = $number->doFormatFloat((float)$result['prd_rate']);
                $result['prd_minimum_rate_number'] = $number->doFormatFloat((float)$result['prd_minimum_rate']);
                $result['prd_exchange_rate_number'] = $number->doFormatFloat((float)$result['prd_exchange_rate']);
            }
        }
        return $result;

    }

    /**
     * @return array delete detail price
     */
    public function getPriceDetailByIdForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('prd_id') === true) {
            $data = PriceDetailDao::getByReference($this->getIntParameter('prd_id'));
            if(empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
                $number = new NumberFormatter();
                $result['prd_quantity_del_number'] = $number->doFormatFloat((float)$result['prd_quantity_del']);
                $result['prd_rate_del_number'] = $number->doFormatFloat((float)$result['prd_rate_del']);
                $result['prd_minimum_rate_del_number'] = $number->doFormatFloat((float)$result['prd_minimum_rate_del']);
                $result['prd_exchange_rate_del_number'] = $number->doFormatFloat((float)$result['prd_exchange_rate_del']);
            }
        }
        return $result;
    }


}
