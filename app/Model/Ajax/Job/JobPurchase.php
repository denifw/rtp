<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Job;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\JobPurchaseDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the ajax request fo JobPurchase.
 *
 * @package    app
 * @subpackage Model\Ajax\Job
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobPurchase extends AbstractBaseAjaxModel
{

    /**
     * Function to the page right by id for modal.
     *
     * @return array
     */
    public function getByIdForUpdate(): array
    {
        $result = [];
        if ($this->isValidParameter('jop_id') === true) {
            $number = new NumberFormatter();
            $result = JobPurchaseDao::getByReference($this->getIntParameter('jop_id'));
            $result['jop_rate_number'] = $number->doFormatFloat((float)$result['jop_rate']);
            $result['jop_quantity_number'] = $number->doFormatFloat((float)$result['jop_quantity']);
            $result['jop_exchange_rate_number'] = $number->doFormatFloat((float)$result['jop_exchange_rate']);
        }

        return $result;
    }

    /**
     * Function to the page right by id for modal.
     *
     * @return array
     */
    public function getByIdForUploadReceipt(): array
    {
        if ($this->isValidParameter('jop_id') === true) {
            $query = 'SELECT jop_id as jop_id_doc, jop_description as jop_description_doc
                    FROM job_purchase
                    WHERE (jop_id = ' . $this->getIntParameter('jop_id') . ')';
            $sqlResults = DB::select($query);
            if (count($sqlResults) === 1) {
                return DataParser::objectToArray($sqlResults[0]);
            }
        }

        return [];
    }

    /**
     * Function to the page right by id for modal.
     *
     * @return array
     */
    public function getByIdForDeleteReceipt(): array
    {
        if ($this->isValidParameter('jop_id') === true) {
            $query = 'SELECT jop_id as jop_id_doc_del, jop_description as jop_description_doc_del, doc.doc_id as jop_doc_id_del
                    FROM job_purchase as jop INNER JOIN
                    (SELECT doc_id, doc_group_reference, doc_type_reference
                        FROM document
                        WHERE (doc_deleted_on is null) and (doc_dct_id = 69)) as doc ON jop.jop_id = doc.doc_type_reference and jop.jop_jo_id = doc_group_reference
                    WHERE (jop_id = ' . $this->getIntParameter('jop_id') . ')';
            $sqlResults = DB::select($query);
            if (count($sqlResults) === 1) {
                return DataParser::objectToArray($sqlResults[0]);
            }
        }

        return [];
    }

    /**
     * Function to the page right by id for modal.
     *
     * @return array
     */
    public function getByIdForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('jop_id') === true) {
            $number = new NumberFormatter();
            $data = JobPurchaseDao::getByReference($this->getIntParameter('jop_id'));
            $keys = array_keys($data);
            foreach ($keys as $key) {
                $result[$key . '_del'] = $data[$key];
            }
            $result['jop_rate_del_number'] = $number->doFormatFloat((float)$result['jop_rate_del']);
            $result['jop_quantity_del_number'] = $number->doFormatFloat((float)$result['jop_quantity_del']);
            $result['jop_exchange_rate_del_number'] = $number->doFormatFloat((float)$result['jop_exchange_rate_del']);
        }

        return $result;
    }

    /**
     * Function to the page right by id for modal.
     *
     * @return array
     */
    public function getByIdForUpdateFromQuotation(): array
    {
        $result = [];
        if ($this->isValidParameter('jop_id') === true) {
            $number = new NumberFormatter();
            $data = JobPurchaseDao::getByReference($this->getIntParameter('jop_id'));
            $keys = array_keys($data);
            foreach ($keys as $key) {
                $result[$key . '_qt'] = $data[$key];
            }
            $result['jop_rate_qt_number'] = $number->doFormatFloat((float)$result['jop_rate_qt']);
            $result['jop_quantity_qt_number'] = $number->doFormatFloat((float)$result['jop_quantity_qt']);
            $result['jop_exchange_rate_qt_number'] = $number->doFormatFloat((float)$result['jop_exchange_rate_qt']);
        }

        return $result;
    }
}
