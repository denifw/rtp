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

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Icon;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Page\SystemTableDao;

/**
 * Class to control the system of SystemTable.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemTable extends AbstractListingModel
{

    /**
     * SystemTable constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'st');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('st_name', $this->getStringParameter('st_name')));
        $this->ListingForm->addField(Trans::getWord('prefix'), $this->Field->getText('st_prefix', $this->getStringParameter('st_prefix')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('st_active', $this->getStringParameter('st_active')));
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
            'st_name' => Trans::getWord('name'),
            'st_prefix' => Trans::getWord('prefix'),
            'st_path' => Trans::getWord('path'),
            'st_active' => Trans::getWord('active'),
            'seeder' => Trans::getWord('seeder'),
        ]);
        # Load the data for SystemTable.
        $columns = array_merge(array_keys($this->ListingTable->getHeaderRow()), ['st_id']);
        $listingData = $this->loadData($columns);
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['st_id']);
        $this->ListingTable->setColumnType('st_active', 'yesno');
        $this->ListingTable->addColumnAttribute('seeder', 'style', 'text-align: center');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return SystemTableDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = SystemTableDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());

        $results = [];
        foreach ($data as $row) {
            $btn = new Button('btnSeed' . $row['st_id'], '');
            $btn->setIcon(Icon::Eye)->btnDanger()->viewIconOnly();
            $fileName = str_replace(' ', '', $row['st_name']) . 'Dao';
            $path = str_replace(['\\', ' '], ['/', ''], $row['st_path']) . '/' . $fileName;
            $btn->setPopup('seed', ['page' => $path]);
            $row['seeder'] = $btn;
            $results[] = $row;
        }
        return $results;
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

        if ($this->isValidParameter('st_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('st_name', $this->getStringParameter('st_name'));
        }
        if ($this->isValidParameter('st_prefix') === true) {
            $wheres[] = SqlHelper::generateStringCondition('st_prefix', $this->getStringParameter('st_prefix'));
        }
        if ($this->isValidParameter('st_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('st_active', $this->getStringParameter('st_active'));
        }

        return $wheres;
    }
}
