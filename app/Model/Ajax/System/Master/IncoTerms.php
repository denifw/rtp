<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\System\Master;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Master\IncoTermsDao;

/**
 * Class to handle the ajax request fo IncoTerms.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Master
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class IncoTerms extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for IncoTerms
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('ict_name', $this->getStringParameter('search_key'));
        $wheres[] = SqlHelper::generateNullCondition('ict_deleted_on');
        return IncoTermsDao::loadSingleSelectData($wheres);
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('ict_id') === true) {
            return IncoTermsDao::getByReference($this->getIntParameter('ict_id'));
        }
        return [];
    }

    /**
     * Function to load the data by id for copy action
     *
     * @return array
     */
    public function getByIdForCopy(): array
    {
        $data = [];
        if ($this->isValidParameter('ict_id') === true) {
            $data = IncoTermsDao::getByReference($this->getIntParameter('ict_id'));
            if (empty($data) === false) {
                $data['ict_id'] = '';
            }
        }

        return $data;
    }

    /**
     * Function to load the data by id for delete action
     *
     * @return array
     */
    public function getByIdForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('ict_id') === true) {
            $data = IncoTermsDao::getByReference($this->getIntParameter('ict_id'));
            if (empty($data) === false) {
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    $result[$key . '_del'] = $data[$key];
                }
            }
        }

        return $result;
    }
}
