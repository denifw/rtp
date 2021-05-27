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

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Crm\DealDao;

/**
 * Class to handle the ajax request fo Deal.
 *
 * @package    app
 * @subpackage Model\Ajax\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Deal extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for Deal
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('dl_ss_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateNumericCondition('dl_ss_id', $this->getIntParameter('dl_ss_id'));
            $wheres[] = SqlHelper::generateLikeCondition('dl_name', $this->getStringParameter('search_key'));
            if ($this->isValidParameter('dl_rel_id') === true) {
                $wheres[] = SqlHelper::generateNumericCondition('dl_rel_id', $this->getIntParameter('dl_rel_id'));
            }

            return DealDao::loadSingleSelectData($wheres);
        }

        return [];
    }
}
