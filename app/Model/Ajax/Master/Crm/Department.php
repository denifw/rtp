<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Master\Crm;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\Crm\DepartmentDao;

/**
 * Class to handle the ajax request fo Department.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Department extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for Department
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('dpt_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('dpt_name', $this->getStringParameter('search_key'));
            $wheres[] = '(dpt_deleted_on IS NULL)';
            $wheres[] = '(dpt_ss_id = ' . $this->getIntParameter('dpt_ss_id') . ')';
            $wheres[] = "(dpt_active = 'Y')";

            return DepartmentDao::loadSingleSelectData($wheres);
        }

        return [];
    }
}
