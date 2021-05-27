<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Crm;

use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Crm\RelationTypeDao;

/**
 * Class to handle the ajax request fo RelationType.
 *
 * @package    app
 * @subpackage Model\Ajax\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class RelationType extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for modal form
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('rty_id') === true) {
            return RelationTypeDao::getByReference($this->getIntParameter('rty_id'));
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
        if ($this->isValidParameter('rty_id') === true) {
            $tpData= RelationTypeDao::getByReference($this->getIntParameter('rty_id'));
            if (empty($tpData) === false) {
                $keys = array_keys($tpData);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $tpData[$key];
                }
            }
        }

        return $result;
    }
}
