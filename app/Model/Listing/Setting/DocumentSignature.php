<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\Setting;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Setting\DocumentSignatureDao;

/**
 * Class to control the system of DocumentSignature.
 *
 * @package    app
 * @subpackage Model\Listing\Setting
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class DocumentSignature extends AbstractListingModel
{

    /**
     * DocumentSignature constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'documentSignature');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {

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
            'ds_dtt_description' => Trans::getWord('templateType'),
            'ds_dt_description' => Trans::getWord('template'),
            'ds_cp_name' => Trans::getWord('person')
        ]);
        # Load the data for documentSignature.
        $this->ListingTable->addRows($this->loadData());
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['ds_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return DocumentSignatureDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return DocumentSignatureDao::loadData(
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

        $wheres[] = '(ds.ds_ss_id =' . $this->User->getSsId() . ')';

        if ($this->isValidParameter('ds_dt_id')) {
            $wheres[] = '(ds.ds_dt_id = ' . $this->getIntParameter('ds_dt_id') . ')';
        }
        if ($this->isValidParameter('ds_cp_id')) {
            $wheres[] = '(ds.ds_cp_id = ' . $this->getIntParameter('ds_cp_id') . ')';
        }

        # return the list where condition.
        return $wheres;
    }
}
