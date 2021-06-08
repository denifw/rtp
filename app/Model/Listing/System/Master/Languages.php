<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Listing\System\Master;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Master\LanguagesDao;

/**
 * Class to control the system of Languages.
 *
 * @package    app
 * @subpackage Model\Listing\System\Master
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class Languages extends AbstractListingModel
{

    /**
     * Languages constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'lg');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('lg_locale', $this->getStringParameter('lg_locale')));
        $this->ListingForm->addField(Trans::getWord('isoCode'), $this->Field->getText('lg_iso', $this->getStringParameter('lg_iso')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('lg_active', $this->getStringParameter('lg_active')));
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
            'lg_locale' => Trans::getWord('name'),
            'lg_iso' => Trans::getWord('isoCode'),
            'lg_active' => Trans::getWord('active'),
        ]);
        # Load the data for Languages.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['lg_id']);
        $this->ListingTable->setColumnType('lg_active', 'yesno');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return LanguagesDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return LanguagesDao::loadData(
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

        if ($this->isValidParameter('lg_locale') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('lg_locale', $this->getStringParameter('lg_locale'));
        }
        if ($this->isValidParameter('lg_iso') === true) {
            $wheres[] = SqlHelper::generateStringCondition('lg_iso', $this->getStringParameter('lg_iso'), '=', 'up');
        }
        if ($this->isValidParameter('lg_active')) {
            $wheres[] = SqlHelper::generateStringCondition('lg_active', $this->getStringParameter('lg_active'));
        }

        # return the list where condition.
        return $wheres;
    }
}
