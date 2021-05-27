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

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to control the system of ApiAccess.
 *
 * @package    app
 * @subpackage Model\Listing\System\Page
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class ApiAccess extends AbstractListingModel
{

    /**
     * ApiAccess constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'apiAccess');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('aa_name', $this->getStringParameter('aa_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('aa_active', $this->getStringParameter('aa_active')));
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
            'aa_name' => Trans::getWord('name'),
            'aa_description' => Trans::getWord('description'),
            'aa_default' => Trans::getWord('default'),
            'aa_active' => Trans::getWord('active'),
        ]);
        # Load the data for ApiAccess.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['aa_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setColumnType('aa_default', 'yesno');
        $this->ListingTable->setColumnType('aa_active', 'yesno');
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['aa_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (aa_id)) AS total_rows
                   FROM api_access ';
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
        $query = 'SELECT aa_id, aa_name, aa_description, aa_default, aa_active
                FROM api_access ';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY aa_id, aa_name, aa_description, aa_default, aa_active';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
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
        if ($this->isValidParameter('aa_name') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('aa_name', $this->getStringParameter('aa_name'));
        }
        if ($this->isValidParameter('aa_active') === true) {
            $wheres[] = "(aa_active = '" . $this->getStringParameter('aa_active') . "')";
        }

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
