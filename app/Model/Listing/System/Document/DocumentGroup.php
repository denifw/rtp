<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\System\Document;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;

/**
 * Class to control the system of DocumentGroup.
 *
 * @package    app
 * @subpackage Model\Listing\System\Document
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DocumentGroup extends AbstractListingModel
{

    /**
     * DocumentGroup constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'documentGroup');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('dcg_code', $this->getStringParameter('dcg_code')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('dcg_active', $this->getStringParameter('dcg_active')));
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
            'dcg_code' => Trans::getWord('code'),
            'dcg_description' => Trans::getWord('description'),
            'dcg_table' => Trans::getWord('table'),
            'dcg_value_field' => Trans::getWord('valueField'),
            'dcg_text_field' => Trans::getWord('textField'),
            'dcg_active' => Trans::getWord('active'),
        ]);
        # Load the data for DocumentGroup.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['dcg_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['dcg_id']);
        $this->ListingTable->setColumnType('dcg_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (dcg_id)) AS total_rows
                   FROM document_group';
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
        $query = 'SELECT dcg_id, dcg_code, dcg_description, dcg_table, dcg_value_field, dcg_text_field, dcg_active
                        FROM document_group ';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY dcg_id, dcg_code, dcg_description, dcg_table, dcg_value_field, dcg_text_field, dcg_active';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY dcg_code';
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

        if ($this->isValidParameter('dcg_code') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('dcg_code', $this->getStringParameter('dcg_code'));
        }

        if ($this->isValidParameter('dcg_active') === true) {
            $wheres[] = "(dcg_active = '" . $this->getStringParameter('dcg_active') . "')";
        }

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
