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
 * Class to control the system of DocumentType.
 *
 * @package    app
 * @subpackage Model\Listing\System\Document
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DocumentType extends AbstractListingModel
{

    /**
     * DocumentType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'documentType');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $documentGroupField = $this->Field->getSingleSelect('documentGroup', 'dct_group', $this->getStringParameter('dct_group'));
        $documentGroupField->setHiddenField('dct_dcg_id');
        $documentGroupField->setEnableDetailButton(false);
        $documentGroupField->setEnableNewButton(false);
        $this->ListingForm->addField(Trans::getWord('documentGroup'), $documentGroupField);
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('dct_code', $this->getStringParameter('dct_code')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('dct_active', $this->getStringParameter('dct_active')));

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
            'dct_group' => Trans::getWord('group'),
            'dct_code' => Trans::getWord('code'),
            'dct_description' => Trans::getWord('description'),
            'dct_table' => Trans::getWord('table'),
            'dct_value_field' => Trans::getWord('valueField'),
            'dct_text_field' => Trans::getWord('textField'),
            'dct_master' => Trans::getWord('master' ),
            'dct_active' => Trans::getWord('active'),
        ]);
        # Load the data for DocumentType.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['dct_id', 'dct_dcg_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['dct_id']);
        $this->ListingTable->setColumnType('dct_master', 'yesno');
        $this->ListingTable->setColumnType('dct_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (dct.dct_id)) AS total_rows
                   FROM document_type as dct INNER JOIN
                        document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id ';
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
        $query = 'SELECT dct.dct_id, dct.dct_dcg_id, dct.dct_description, dcg.dcg_code as dct_group, dct.dct_code, dct.dct_table, dct.dct_value_field, 
                    dct.dct_text_field, dct.dct_active, dct.dct_master
                        FROM document_type as dct INNER JOIN
                        document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id ';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY dct.dct_id, dct.dct_dcg_id, dct.dct_description, dcg.dcg_code, dct.dct_code, dct.dct_table, dct.dct_value_field, 
                    dct.dct_text_field, dct.dct_active, dct.dct_master';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY dct_group, dct_code';
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

        if ($this->isValidParameter('dct_dcg_id') === true) {
            $wheres[] = '(dct.dct_dcg_id = ' . $this->getIntParameter('dct_dcg_id') . ')';
        }
        if ($this->isValidParameter('dct_code') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('dct.dct_code', $this->getStringParameter('dct_code'));
        }
        if ($this->isValidParameter('dct_active') === true) {
            $wheres[] = "(dct.dct_active = '" . $this->getStringParameter('dct_active') . "')";
        }

        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
