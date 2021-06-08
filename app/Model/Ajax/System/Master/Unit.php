<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Ajax\System\Master;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Master\UnitDao;

/**
 * Class to handle the ajax request fo UnitOfMeasure.
 *
 * @package    app
 * @subpackage Model\Ajax\Master
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class Unit extends AbstractBaseAjaxModel
{
    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateOrLikeCondition(['uom_name', 'uom_code'], $this->getStringParameter('search_key'));
        }
        if ($this->isValidParameter('uom_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('uom_id', $this->getStringParameter('uom_id'));
        }
        $wheres[] = SqlHelper::generateStringCondition('uom_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('uom_deleted_on');
        return UnitDao::loadSingleSelectData(['uom_name', 'uom_code'], $wheres);
    }
}
