<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\System;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\SystemTypeDao;

/**
 * Class to handle the ajax request fo SystemType.
 *
 * @package    app
 * @subpackage Model\Ajax\System
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
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
        $wheres = [];
        $wheres[] = '(sty.sty_deleted_on IS NULL)';
        $wheres[] = "(sty.sty_active = 'Y')";
        $wheres[] = SqlHelper::generateLikeCondition('sty_name', $this->getStringParameter('search_key'));
        $wheres[] = SqlHelper::generateStringCondition('sty_group', $this->getStringParameter('sty_group'));

        return SystemTypeDao::loadSingleSelectData($wheres);
    }
}
