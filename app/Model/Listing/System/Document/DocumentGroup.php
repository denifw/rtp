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
use App\Model\Dao\System\Document\DocumentGroupDao;

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
        parent::__construct(get_class($this), 'dcg');
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
        $this->ListingTable->addRows($this->loadData());
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
        return DocumentGroupDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return DocumentGroupDao::loadData(
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

        if ($this->isValidParameter('dcg_code') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dcg_code', $this->getStringParameter('dcg_code'));
        }

        if ($this->isValidParameter('dcg_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dcg_active', $this->getStringParameter('dcg_active'));
        }
        # return the where query.
        return $wheres;
    }
}
