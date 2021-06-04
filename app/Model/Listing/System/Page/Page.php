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
use App\Model\Dao\System\Page\PageCategoryDao;
use App\Model\Dao\System\Page\PageDao;

/**
 * Class to manage the creation of the listing Page page.
 *
 * @package    App
 * @subpackage Model\Listing\System\Page
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class Page extends AbstractListingModel
{

    /**
     * Page constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter for the page.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'pg');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $menuField = $this->Field->getSingleSelect('mn', 'page_menu', $this->getStringParameter('page_menu'));
        $menuField->setHiddenField('pg_mn_id', $this->getStringParameter('pg_mn_id'));
        $menuField->setEnableNewButton(false);
        $menuField->setEnableDetailButton(false);
        $categoryField = $this->Field->getSelect('pg_pc_id', $this->getIntParameter('pg_pc_id'));
        $categoryField->addOptions(PageCategoryDao::loadActiveData(), 'pc_name', 'pc_id');
        $this->ListingForm->addField(Trans::getWord('title'), $this->Field->getText('pg_title', $this->getStringParameter('pg_title')));
        $this->ListingForm->addField(Trans::getWord('route'), $this->Field->getText('pg_route', $this->getStringParameter('pg_route')));
        $this->ListingForm->addField(Trans::getWord('category'), $categoryField);
        $this->ListingForm->addField(Trans::getWord('menu'), $menuField);
        $this->ListingForm->addField(Trans::getWord('default'), $this->Field->getYesNo('pg_default', $this->getStringParameter('pg_default')));
        $this->ListingForm->addField(Trans::getWord('system'), $this->Field->getYesNo('pg_system', $this->getStringParameter('pg_system')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('pg_active', $this->getStringParameter('pg_active')));
        $this->ListingForm->addField(Trans::getWord('used'), $this->Field->getYesNo('pg_used', $this->getStringParameter('pg_used')));
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
                'pg_title' => Trans::getWord('title'),
                'pg_description' => Trans::getWord('description'),
                'pg_route' => Trans::getWord('route'),
                'mn_name' => Trans::getWord('menu'),
                'pc_name' => Trans::getWord('category'),
                'pg_order' => Trans::getWord('sortNumber'),
                'pg_default' => Trans::getWord('default'),
                'pg_system' => Trans::getWord('system'),
                'pg_active' => Trans::getWord('active')
            ]
        );
        # Load the data for Page.
        $this->ListingTable->addRows($this->loadData());
        # Add special settings to the table
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['pg_id'], false);
        $this->ListingTable->setColumnType('pg_default', 'yesno');
        $this->ListingTable->setColumnType('pg_system', 'yesno');
        $this->ListingTable->setColumnType('pg_active', 'yesno');
        $this->ListingTable->setColumnType('pg_order', 'integer');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return PageDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return PageDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
    }

    /**
     * Function to get the where condition.
     *
     * @return array
     */
    private function getWhereCondition(): array
    {
        # Set where conditions
        $wheres = [];
        if ($this->isValidParameter('pg_title') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('pg.pg_title', $this->getStringParameter('pg_title'));
        }
        if ($this->isValidParameter('pg_mn_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('pg.pg_mn_id', $this->getStringParameter('pg_mn_id'));
        }
        if ($this->isValidParameter('pg_pc_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('pg.pg_pc_id', $this->getStringParameter('pg_pc_id'));
        }
        if ($this->isValidParameter('pg_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('pg.pg_active', $this->getStringParameter('pg_active'));
        }
        if ($this->isValidParameter('pg_default') === true) {
            $wheres[] = SqlHelper::generateStringCondition('pg.pg_default', $this->getStringParameter('pg_default'));
        }
        if ($this->isValidParameter('pg_system') === true) {
            $wheres[] = SqlHelper::generateStringCondition('pg.pg_system', $this->getStringParameter('pg_system'));
        }
        if ($this->isValidParameter('pg_used') === true) {
            if ($this->getStringParameter('pg_used') === 'Y') {
                $wheres[] = '(pg.pg_id IN (select ugp_pg_id FROM user_group_page where ugp_deleted_on IS NULL))';
            } else {
                $wheres[] = '(pg.pg_id NOT IN (select ugp_pg_id FROM user_group_page where ugp_deleted_on IS NULL))';
            }
        }
        if ($this->isValidParameter('pg_route') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('pg_route', $this->getStringParameter('pg_route'));
        }
        return $wheres;
    }

}
