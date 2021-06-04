<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\System\Document;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Document\DocumentTemplateTypeDao;

/**
 * Class to control the system of DocumentTemplateType.
 *
 * @package    app
 * @subpackage Model\Listing\System\Document
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class DocumentTemplateType extends AbstractListingModel
{

    /**
     * DocumentTemplateType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'dtt');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('dtt_code', $this->getStringParameter('dtt_code')));
        $this->ListingForm->addField(Trans::getWord('description'), $this->Field->getText('dtt_description', $this->getStringParameter('dtt_description')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('dtt_active', $this->getStringParameter('dtt_active')));
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
            'dtt_code' => Trans::getWord('code'),
            'dtt_description' => Trans::getWord('description'),
            'dtt_active' => Trans::getWord('active')
        ]);
        # Load the data for DocumentTemplateType.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('dtt_active', 'yesno');
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['dtt_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return DocumentTemplateTypeDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return DocumentTemplateTypeDao::loadData(
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

        if ($this->isValidParameter('dtt_code')) {
            $wheres[] = SqlHelper::generateLikeCondition('dtt_code', $this->getStringParameter('dtt_code'));
        }
        if ($this->isValidParameter('dtt_description')) {
            $wheres[] = SqlHelper::generateLikeCondition('dtt_description', $this->getStringParameter('dtt_description'));
        }
        if ($this->isValidParameter('dtt_active')) {
            $wheres[] = SqlHelper::generateLikeCondition('dtt_active', $this->getStringParameter('dtt_active'));
        }

        # return the list where condition.
        return $wheres;
    }
}
