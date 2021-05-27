<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\CustomerService;

use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\CustomerService\SalesOrderQuotationDao;

/**
 * Class to handle the ajax request fo SalesOrderQuotation.
 *
 * @package    app
 * @subpackage Model\Ajax\CustomerService
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class SalesOrderQuotation extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for modal form delete
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('soq_id') === true) {
            $soqData= SalesOrderQuotationDao::getByReference($this->getIntParameter('soq_id'));
            if (empty($soqData) === false) {
                $keys = array_keys($soqData);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $soqData[$key];
                }
            }
        }

        return $result;
    }
}
