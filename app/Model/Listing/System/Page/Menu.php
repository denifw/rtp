<?php
/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 10/04/2019
 * Time: 17:51
 */

namespace App\Model\Listing\System\Page;


use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Page\MenuDao;

/**
 * Class to manage the creation of the listing Menu page.
 *
 * @package    App
 * @subpackage Model\Listing\System\Page
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class Menu extends AbstractListingModel
{

    /**
     * Menu constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'mn');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $parentField = $this->Field->getSingleSelect('mn', 'parent_menu', $this->getStringParameter('parent_menu'));
        $parentField->setHiddenField('mn_parent', $this->getStringParameter('mn_parent'));
        $parentField->setEnableNewButton(false);
        $parentField->setEnableDetailButton(false);
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('mn_name', $this->getStringParameter('mn_name')));
        $this->ListingForm->addField(Trans::getWord('subMenuOf'), $parentField);
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('mn_active', $this->getStringParameter('mn_active')));
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
                'mn_name' => Trans::getWord('name'),
                'parent_menu' => Trans::getWord('subMenuOf'),
                'mn_icon' => Trans::getWord('icon'),
                'mn_order' => Trans::getWord('sortNumber'),
                'mn_active' => Trans::getWord('active')
            ]
        );
        # Load the data for Menu.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['mn_id'], false);
        $this->ListingTable->setColumnType('mn_order', 'integer');
        $this->ListingTable->setColumnType('mn_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return MenuDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return MenuDao::loadData(
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
        if ($this->isValidParameter('mn_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('m1.mn_name', $this->getStringParameter('mn_name'));
        }
        if ($this->isValidParameter('mn_parent') === true) {
            $wheres[] = SqlHelper::generateStringCondition('m1.mn_parent', $this->getStringParameter('mn_parent'));
        }
        if ($this->isValidParameter('mn_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('m1.mn_active', $this->getStringParameter('mn_active'));
        }


        return $wheres;
    }
}
