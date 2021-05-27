<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Master;

use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Master\CostCodeServiceDao;

/**
 * Class to handle the ajax request fo CostCodeService.
 *
 * @package    app
 * @subpackage Model\Ajax\Master
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class CostCodeService extends AbstractBaseAjaxModel
{

    /**
     * Function to the page right by id for modal.
     *
     * @return array
     */
    public function getByIdForModal(): array
    {
        $result = [];
        if ($this->isValidParameter('ccs_id') === true) {
            $result = CostCodeServiceDao::getByReference($this->getIntParameter('ccs_id'));
        }

        return $result;
    }
}
