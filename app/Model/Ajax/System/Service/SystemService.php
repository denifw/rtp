<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Ajax\System\Service;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Service\SystemServiceDao;

/**
 * Class to handle the ajax request fo Service.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Service
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class SystemService extends AbstractBaseAjaxModel
{
    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('ssr_ss_id') === true) {
            $wheres = [];
            $wheres[] = StringFormatter::generateLikeQuery('srv_name', $this->getStringParameter('search_key'));
            $wheres[] = '(ssr.ssr_ss_id = ' . $this->getIntParameter('ssr_ss_id') . ')';
            $wheres[] = '(ssr.ssr_deleted_on IS NULL)';
            $wheres[] = "(ssr.ssr_active = 'Y')";
            return SystemServiceDao::loadSingleSelectData($wheres);
        }

        return [];
    }
}
