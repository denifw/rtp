<?php
/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 10/04/2019
 * Time: 17:51
 */

namespace App\Model\Listing\System\Page;


use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

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
        parent::__construct(get_class($this), 'menu');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $parentField = $this->Field->getSingleSelect('menu', 'parent_menu', $this->getStringParameter('parent_menu'));
        $parentField->setHiddenField('mn_parent', $this->getIntParameter('mn_parent'));
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
                'mn_order' => Trans::getWord('sortNumber'),
                'mn_active' => Trans::getWord('active')
            ]
        );
        # Load the data for Menu.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['mn_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['mn_id']);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['mn_id'], true);
        $this->ListingTable->setColumnType('mn_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (m1.mn_id)) AS total_rows
                   FROM menu AS m1 LEFT OUTER JOIN
                menu AS m2 ON m1.mn_parent = m2.mn_id';
        # Set where condition.
        $query .= $this->getWhereCondition();

        return $this->loadTotalListingRows($query);
    }


    /**
     * Get query to get the listing data.
     *
     * @param array $outFields To store the out field from selection data.
     *
     * @return array
     */
    private function loadData(array $outFields): array
    {
        # Set Select query;
        $query = 'SELECT m1.mn_id, m1.mn_name, m1.mn_active, m2.mn_name AS parent_menu, m1.mn_parent, m1.mn_order , m2.mn_order as parent_order
                FROM menu AS m1 LEFT OUTER JOIN
                menu AS m2 ON m1.mn_parent = m2.mn_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY m1.mn_id, m1.mn_name, m1.mn_active, m2.mn_name, m1.mn_parent, m1.mn_order, m2.mn_order ';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY m2.mn_order, m1.mn_order';
        }

        return $this->loadDatabaseRow($query, $outFields);
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
        if ($this->isValidParameter('mn_name') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('m1.mn_name', $this->getStringParameter('mn_name'));
        }
        if ($this->isValidParameter('mn_parent') === true) {
            $wheres[] = '(m1.mn_parent = ' . $this->getIntParameter('mn_parent') . ')';
        }
        if ($this->isValidParameter('mn_active') === true) {
            $wheres[] = "(m1.mn_active = '" . $this->getStringParameter('mn_active') . "')";
        }

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
