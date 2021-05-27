<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\System;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\Label;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\SystemTypeDao;

/**
 * Class to control the system of SystemType.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class SystemType extends AbstractListingModel
{

    /**
     * SystemType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'sty');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('group'), $this->Field->getText('sty_group', $this->getStringParameter('sty_group')));
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('sty_name', $this->getStringParameter('sty_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('sty_active', $this->getStringParameter('sty_active')));
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
            'sty_group' => Trans::getWord('group'),
            'sty_name' => Trans::getWord('name'),
            'sty_active' => Trans::getWord('active'),
        ]);
        $rows = [];
        $data = $this->loadData();
        foreach ($data as $row) {
            $row['sty_name'] = new Label($row['sty_name'], $row['sty_label_type']);
            $rows[] = $row;
        }
        # Load the data for SystemType.
        $this->ListingTable->addRows($rows);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['sty_id']);
        $this->ListingTable->setColumnType('sty_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return SystemTypeDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return SystemTypeDao::loadData(
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
        $wheres[] = '(sty.sty_deleted_on IS NULL)';

        if ($this->isValidParameter('sty_group')) {
            $wheres[] = SqlHelper::generateLikeCondition('sty_group', $this->getStringParameter('sty_group'));
        }
        if ($this->isValidParameter('sty_name')) {
            $wheres[] = SqlHelper::generateLikeCondition('sty_name', $this->getStringParameter('sty_name'));
        }
        if ($this->isValidParameter('sty_active')) {
            $wheres[] = SqlHelper::generateLikeCondition('sty_active', $this->getStringParameter('sty_active'));
        }

        # return the list where condition.
        return $wheres;
    }
}
