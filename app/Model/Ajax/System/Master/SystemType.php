<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Project
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Ajax\System\Master;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Master\SystemTypeDao;

/**
 * Class to handle the ajax request fo SystemType.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Master
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class SystemType extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for SystemType
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('sty_group') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateStringCondition('sty_group', $this->getStringParameter('sty_group'), '=', 'low');
            if ($this->isValidParameter('search_key') === true) {
                $wheres[] = SqlHelper::generateOrLikeCondition([
                    'sty_code', 'sty_name'
                ], $this->getStringParameter('search_key'));
            }
            $wheres[] = SqlHelper::generateStringCondition('sty_active', 'Y');
            $wheres[] = SqlHelper::generateNullCondition('sty_deleted_on');

            return SystemTypeDao::loadSingleSelectData('sty_name', $wheres);
        }
        return [];
    }

    /**
     * Function to load the data by id
     *
     * @return array
     */
    public function getById(): array
    {
        if ($this->isValidParameter('sty_id') === true) {
            return SystemTypeDao::getByReference($this->getStringParameter('sty_id'));
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
        if ($this->isValidParameter('sty_id') === true) {
            $data = SystemTypeDao::getByReference($this->getStringParameter('sty_id'));
            if (empty($data) === false) {
                $data['sty_id'] = '';
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
        if ($this->isValidParameter('sty_id') === true) {
            $data = SystemTypeDao::getByReference($this->getStringParameter('sty_id'));
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
