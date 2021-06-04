<?php
/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 12/04/2019
 * Time: 13:10
 */

namespace App\Model\Ajax\System\Page;


use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Page\MenuDao;

class Menu extends AbstractBaseAjaxModel
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
            $wheres[] = SqlHelper::generateLikeCondition('m1.mn_name', $this->getStringParameter('search_key'));
        }
        $wheres[] = SqlHelper::generateNullCondition('m1.mn_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('m1.mn_active', 'Y');
        return MenuDao::loadSingleSelectData(['parent_menu', 'mn_name'], $wheres);
    }

}
