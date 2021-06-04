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
use App\Model\Dao\System\Document\DocumentTemplateDao;

/**
 * Class to control the system of DocumentTemplate.
 *
 * @package    app
 * @subpackage Model\Listing\System\Document
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class DocumentTemplate extends AbstractListingModel
{

    /**
     * DocumentTemplate constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'dt');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        #create type field

        $dtField = $this->Field->getSingleSelect('documentTemplateType','dt_dtt_description',$this->getStringParameter('dt_dtt_description'));
        $dtField->setHiddenField('dt_dtt_id',$this->getStringParameter('dt_dtt_id'));
        $dtField->setEnableNewButton(false);

        $this->ListingForm->addField(Trans::getWord('templateType'), $dtField);
        $this->ListingForm->addField(Trans::getWord('description'), $this->Field->getText('dt_description', $this->getStringParameter('dt_description')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('dt_active', $this->getStringParameter('dt_active')));
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
            'dt_dtt_description' => Trans::getWord('templateType'),
            'dt_description' => Trans::getWord('description'),
            'dt_path' => Trans::getWord('path'),
            'dt_active' => Trans::getWord('active')
        ]);
        # Load the data for DocumentTemplate.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['dt_id']);
        $this->ListingTable->setColumnType('dt_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return DocumentTemplateDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return DocumentTemplateDao::loadData(
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

        if ($this->isValidParameter('dt_dtt_id')) {
            $wheres[] = '(dt.dt_dtt_id = ' . $this->getIntParameter('dt_dtt_id') . ')';
        }
        if ($this->isValidParameter('dt_description')) {
            $wheres[] = SqlHelper::generateLikeCondition('dt_description', $this->getStringParameter('dt_description'));
        }
        if ($this->isValidParameter('dt_active')) {
            $wheres[] = SqlHelper::generateLikeCondition('dt_active', $this->getStringParameter('dt_active'));
        }

        # return the list where condition.
        return $wheres;
    }
}
