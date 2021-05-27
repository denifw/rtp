<?php
/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 10/04/2019
 * Time: 14:15
 */

namespace App\Model\Listing\System\Page;


use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to manage the creation of the listing Page page.
 *
 * @package    App
 * @subpackage Model\Listing\System\Page
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class PageRight extends AbstractListingModel
{

    /**
     * Page constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter for the page.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'pageRight');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('page'), $this->Field->getText('pr_page', $this->getStringParameter('pr_page')));
        $this->ListingForm->addField(Trans::getWord('pageRoute'), $this->Field->getText('pr_pg_route', $this->getStringParameter('pr_pg_route')));
        $this->ListingForm->addField(Trans::getWord('right'), $this->Field->getText('pr_name', $this->getStringParameter('pr_name')));
        $this->ListingForm->addField(Trans::getWord('default'), $this->Field->getYesNo('pr_default', $this->getStringParameter('pr_default')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('pr_active', $this->getStringParameter('pr_active')));
        $this->ListingForm->addField(Trans::getWord('used'), $this->Field->getYesNo('pr_used', $this->getStringParameter('pr_used')));
        $this->ListingForm->setGridDimension(4);
    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        # set header column table
        $this->ListingTable->setHeaderRow(
            [
                'pr_name' => Trans::getWord('right'),
                'pr_description' => Trans::getWord('description'),
                'pg_title' => Trans::getWord('page'),
                'pg_route' => Trans::getWord('route'),
                'pc_name' => Trans::getWord('category'),
                'mn_name' => Trans::getWord('menu'),
                'pr_default' => Trans::getWord('default'),
                'pr_active' => Trans::getWord('active'),
            ]
        );
        # Load the data for Page.
        $data = $this->loadData();
        $rows = [];
        foreach ($data as $row) {
            if (empty($row['parent_menu']) === false) {
                $row['mn_name'] = $row['parent_menu'] . '/' . $row['mn_name'];
            }
            $rows[] = $row;
        }
        $this->ListingTable->addRows($rows);
        # Add special settings to the table
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['pr_id'], false);
        $this->ListingTable->setColumnType('pr_default', 'yesno');
        $this->ListingTable->setColumnType('pr_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (pr.pr_id)) AS total_rows
                   FROM page_right as pr INNER JOIN
                    page AS pg ON pr.pr_pg_id = pg.pg_id INNER JOIN
                  page_category AS pc on pg.pg_pc_id = pc.pc_id LEFT OUTER JOIN
                  menu AS mn on pg.pg_mn_id = mn.mn_id LEFT OUTER JOIN
                menu AS m2 ON mn.mn_parent = m2.mn_id';
        # Set where condition.
        $query .= $this->getWhereCondition();

        return $this->loadTotalListingRows($query);
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        # Set Select query;
        $query = 'SELECT pr.pr_id, pr.pr_name, pr.pr_default, pr.pr_active, pg.pg_id, pg.pg_title, pg.pg_route,
                        pc.pc_name, mn.mn_name, m2.mn_name as parent_menu, pr.pr_description
                FROM page_right as pr INNER JOIN
                    page AS pg ON pr.pr_pg_id = pg.pg_id INNER JOIN
                  page_category AS pc on pg.pg_pc_id = pc.pc_id LEFT OUTER JOIN
                  menu AS mn on pg.pg_mn_id = mn.mn_id LEFT OUTER JOIN
                menu AS m2 ON mn.mn_parent = m2.mn_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY pr.pr_id, pr.pr_name, pr.pr_default, pr.pr_active, pg.pg_id, pg.pg_title, pg.pg_route,
                        pc.pc_name, mn.mn_name, m2.mn_name';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY pg.pg_id, pr.pr_id';
        }

        return $this->loadDatabaseRow($query);
    }

    /**
     * Function to get the where condition.
     *
     * @return string
     */
    private function getWhereCondition(): string
    {
        # Set where conditions
        $wheres = [];
        if ($this->isValidParameter('pr_page') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('pg.pg_title', $this->getStringParameter('pr_page'));
        }
        if ($this->isValidParameter('pr_pg_route') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('pg.pg_route', $this->getStringParameter('pr_pg_route'));
        }
        if ($this->isValidParameter('pr_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('pr.pr_name', $this->getStringParameter('pr_name'));
        }
        if ($this->isValidParameter('pr_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('pr.pr_active', $this->getStringParameter('pr_active'));
        }
        if ($this->isValidParameter('pr_default') === true) {
            $wheres[] = SqlHelper::generateStringCondition('pr.pr_default', $this->getStringParameter('pr_default'));
        }
        if ($this->isValidParameter('pr_used') === true) {
            if ($this->getStringParameter('pr_used') === 'Y') {
                $wheres[] = '(pr.pr_id IN (select ugr_pr_id FROM user_group_right where ugr_deleted_on IS NULL))';
            } else {
                $wheres[] = '(pr.pr_id NOT IN (select ugr_pr_id FROM user_group_right where ugr_deleted_on IS NULL))';
            }
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }

}
