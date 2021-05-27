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
use App\Model\Dao\Master\Crm\IndustryDao;

/**
 * Class to handle the ajax request fo Industry.
 *
 * @package    app
 * @subpackage Model\Ajax\Master\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Industry extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for Industry
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('ids_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('ids_name', $this->getStringParameter('search_key'));
            $wheres[] = '(ids_deleted_on IS NULL)';
            $wheres[] = '(ids_ss_id = ' . $this->getIntParameter('ids_ss_id') . ')';
            $wheres[] = "(ids_active = 'Y')";

            return IndustryDao::loadSingleSelectData($wheres);
        }

        return [];
    }
}
