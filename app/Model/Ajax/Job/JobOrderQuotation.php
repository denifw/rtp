<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Job;

use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\JobOrderQuotationDao;

/**
 * Class to handle the ajax request fo JobOrderQuotation.
 *
 * @package    app
 * @subpackage Model\Ajax\Job
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class JobOrderQuotation extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for modal form delete
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('joq_id') === true) {
            $joqData= JobOrderQuotationDao::getByReference($this->getIntParameter('joq_id'));
            if (empty($joqData) === false) {
                $keys = array_keys($joqData);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $joqData[$key];
                }
            }
        }

        return $result;
    }
}
