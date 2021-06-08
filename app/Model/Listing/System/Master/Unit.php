<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Listing\System\Master;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Master\UnitDao;

/**
 * Class to manage the creation of the listing UnitOfMeasure page.
 *
 * @package    app
 * @subpackage Model\Listing\Master
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Unit extends AbstractListingModel
{

    /**
     * UnitOfMeasure constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'uom');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('uom_name', $this->getStringParameter('uom_name')));
        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('uom_code', $this->getStringParameter('uom_code')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('uom_active', $this->getStringParameter('uom_active')));
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
                'uom_name' => Trans::getWord('name'),
                'uom_code' => Trans::getWord('code'),
                'uom_active' => Trans::getWord('active'),
            ]
        );
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('uom_active', 'yesno');
        # Add special settings to the table
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['uom_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return UnitDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return UnitDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable()
        );
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
        if ($this->isValidParameter('uom_name')) {
            $wheres[] = SqlHelper::generateLikeCondition('uom_name', $this->getStringParameter('uom_name'));
        }
        if ($this->isValidParameter('uom_code')) {
            $wheres[] = SqlHelper::generateLikeCondition('uom_code', $this->getStringParameter('uom_code'));
        }
        if ($this->isValidParameter('uom_active')) {
            $wheres[] = SqlHelper::generateStringCondition('uom_active', $this->getStringParameter('uom_active'));
        }
        return $wheres;
    }
}
