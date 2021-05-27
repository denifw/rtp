<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\System\Location;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Location\CountryDao;

/**
 * Class to control the system of Country.
 *
 * @package    app
 * @subpackage Model\Listing\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Country extends AbstractListingModel
{

    /**
     * Country constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'country');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('cnt_name', $this->getStringParameter('cnt_name')));
        $this->ListingForm->addField(Trans::getWord('isoCode'), $this->Field->getText('cnt_iso', $this->getStringParameter('cnt_iso')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('cnt_active', $this->getStringParameter('cnt_active')));
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
            'cnt_name' => Trans::getWord('name'),
            'cnt_iso' => Trans::getWord('isoCode'),
            'cnt_active' => Trans::getWord('active'),
        ]);
        # Load the data for Country.
        $this->ListingTable->addRows($this->loadData());
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['cnt_id']);
        }
        $this->ListingTable->setColumnType('cnt_active', 'yesno');
        $this->ListingTable->addColumnAttribute('cnt_iso', 'style', 'text-align: center;');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return CountryDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return CountryDao::loadData(
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

        if ($this->isValidParameter('cnt_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('cnt_name', $this->getStringParameter('cnt_name'));
        }
        if ($this->isValidParameter('cnt_iso') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('cnt_iso', $this->getStringParameter('cnt_iso'));
        }
        if ($this->isValidParameter('cnt_active') === true) {
            $wheres[] = "(cnt_active = '" . $this->getStringParameter('cnt_active') . "')";
        }
        return $wheres;
    }
}
