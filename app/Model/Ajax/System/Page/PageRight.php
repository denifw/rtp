<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System\Page;

use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Page\PageRightDao;

/**
 * Class to handle the ajax request fo PageRight.
 *
 * @package    app
 * @subpackage Model\Ajax\Page
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class PageRight extends AbstractBaseAjaxModel
{
    /**
     * Function to the page right by id for modal.
     *
     * @return array
     */
    public function getByIdForModal(): array
    {
        $result = [];
        if ($this->isValidParameter('pr_id') === true) {
            $result = PageRightDao::getByReference($this->getIntParameter('pr_id'));
        }

        return $result;
    }


}
