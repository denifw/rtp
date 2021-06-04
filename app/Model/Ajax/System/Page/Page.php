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
use App\Model\Dao\System\Page\PageDao;

class Page extends AbstractBaseAjaxModel
{
    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectTable(): array
    {
        $wheres = [];
        if ($this->isValidParameter('pg_title') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('pg.pg_title', $this->getStringParameter('pg_title'));
        }
        if ($this->isValidParameter('pc_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('pc.pc_name', $this->getStringParameter('pc_name'));
        }
        if ($this->isValidParameter('mn_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('mn.mn_name', $this->getStringParameter('mn_name'));
        }
        if ($this->isValidParameter('pg_system') === true) {
            $wheres[] = SqlHelper::generateStringCondition('pg.pg_system', $this->getStringParameter('pg_system'));
        }
        if ($this->isValidParameter('pg_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('pg.pg_active', $this->getStringParameter('pg_active'));
        }
        $wheres[] = SqlHelper::generateNullCondition('pg.pg_deleted_on');
        $data = PageDao::loadData($wheres);
        $results = [];
        foreach ($data as $row) {
            if (empty($row['parent_menu']) === false) {
                $row['mn_menu'] = $row['parent_menu'] . '/' . $row['mn_name'];
            }
            $results[] = $row;
        }
        return $results;
    }

}
