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

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Document\DocumentTypeDao;

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
        parent::__construct(get_class($this), 'dct');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $documentGroupField = $this->Field->getSingleSelect('dcg', 'dct_group', $this->getStringParameter('dct_group'));
        $documentGroupField->setHiddenField('dct_dcg_id', $this->getStringParameter('dct_dcg_id'));
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
            'dct_active' => Trans::getWord('active'),
        ]);
        # Load the data for DocumentType.
        $this->ListingTable->addRows($this->loadData());
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
        return DocumentTypeDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return DocumentTypeDao::loadData(
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

        if ($this->isValidParameter('dct_dcg_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dct.dct_dcg_id', $this->getStringParameter('dct_dcg_id'));
        }
        if ($this->isValidParameter('dct_code') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dct.dct_code', $this->getStringParameter('dct_code'));
        }
        if ($this->isValidParameter('dct_active') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dct.dct_active', $this->getStringParameter('dct_active'));
        }


        return $wheres;
    }
}
