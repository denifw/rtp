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
use App\Model\Dao\Master\Crm\JobTitleDao;

/**
 * Class to handle the ajax request fo JobTitle.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class JobTitle extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for JobTitle
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('jbt_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('jbt_name', $this->getStringParameter('search_key'));
            $wheres[] = '(jbt_deleted_on IS NULL)';
            $wheres[] = '(jbt_ss_id = ' . $this->getIntParameter('jbt_ss_id') . ')';
            $wheres[] = "(jbt_active = 'Y')";

            return JobTitleDao::loadSingleSelectData($wheres);
        }

        return [];
    }
}
