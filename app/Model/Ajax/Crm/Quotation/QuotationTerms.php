<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Crm\Quotation;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Crm\Quotation\QuotationTermsDao;

/**
 * Class to handle the ajax request fo QuotationTerms.
 *
 * @package    app
 * @subpackage Model\Ajax\Crm\Quotation
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class QuotationTerms extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for QuotationTerms
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('qtm_id') === false) {
            return [];
        }
        return QuotationTermsDao::getByReference($this->getIntParameter('qtm_id'));
    }

    /**
     * Function to load the data for single select for QuotationTerms
     *
     * @return array
     */
    public function getByIdForDelete(): array
    {
        if ($this->isValidParameter('qtm_id') === false) {
            return [];
        }
        $data = QuotationTermsDao::getByReference($this->getIntParameter('qtm_id'));
        if (empty($data) === true) {
            return [];
        }
        $keys = array_keys($data);
        $results = [];
        foreach ($keys as $key) {
            $results[$key . '_del'] = $data[$key];
        }
        return $results;
    }
}
