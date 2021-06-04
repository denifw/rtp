<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\System\Page;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Page\PageCategoryDao;

/**
 * Class to control the system of PageCategory.
 *
 * @package    app
 * @subpackage Model\Listing\Page
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class PageCategory extends AbstractListingModel
{

    /**
     * PageCategory constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'pc');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('pc_name', $this->getStringParameter('pc_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('pc_active', $this->getStringParameter('pc_active')));
    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        # set header column table
        $this->ListingTable->setHeaderRow([
            'pc_name' => Trans::getWord('name'),
            'pc_code' => Trans::getWord('code'),
            'pc_route' => Trans::getWord('route'),
            'pc_active' => Trans::getWord('active'),
        ]);
        # Load the data for PageCategory.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['pc_id']);
        $this->ListingTable->setColumnType('pc_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return PageCategoryDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return PageCategoryDao::loadData(
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

        if ($this->isValidParameter('pc_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('pc_name', $this->getStringParameter('pc_name'));
        }
        if ($this->isValidParameter('pc_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('pc_active', $this->getStringParameter('pc_active'));
        }
        return $wheres;
    }
}
