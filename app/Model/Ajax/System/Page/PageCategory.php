<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\System\Page;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Page\PageCategoryDao;

/**
 * Class to handle the ajax request fo PageCategory.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Page
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class PageCategory extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for PageCategory
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('pc_name', $this->getStringParameter('search_key'));
        }
        return PageCategoryDao::loadSingleSelectData('pc_name', $wheres);
    }
}
