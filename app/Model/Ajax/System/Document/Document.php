<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System\Document;

use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Document\DocumentDao;

/**
 * Class to handle the ajax request fo Document.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Document
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Document extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        return [];
    }


    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('doc_id') === true) {
            $data = DocumentDao::getByReference($this->getStringParameter('doc_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
            }
        }

        return $result;
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getGoodsImageForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('doc_id') === true) {
            $data = DocumentDao::getByReference($this->getIntParameter('doc_id'));
            if (empty($data) === false) {
                $result['gd_im_id_del'] = $data['doc_id'];
                $result['gd_im_description_del'] = $data['doc_description'];
                $result['gd_im_name_del'] = $data['doc_file_name'];
            }
        }

        return $result;
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getEquipmentImageForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('doc_id') === true) {
            $data = DocumentDao::getByReference($this->getIntParameter('doc_id'));
            if (empty($data) === false) {
                $result['eq_im_id_del'] = $data['doc_id'];
                $result['eq_im_description_del'] = $data['doc_description'];
                $result['eq_im_name_del'] = $data['doc_file_name'];
            }
        }

        return $result;
    }

}
