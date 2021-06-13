<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\System\Access;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Access\SystemSettingDao;

/**
 * Class to control the system of SystemSetting.
 *
 * @package    app
 * @subpackage Model\Listing\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemSetting extends AbstractListingModel
{

    /**
     * SystemSetting constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ss');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('relation'), $this->Field->getText('ss_relation', $this->getStringParameter('ss_relation')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getText('ss_active', $this->getStringParameter('ss_active')));
    }

    /**
     * Abstract function to load sorting field.
     *
     * @return void
     */
    public function loadSortingOptions(): void
    {
        $this->ListingSort->addOption('ss_relation', Trans::getWord('relation'));
        parent::loadSortingOptions();
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
            'ss_relation' => Trans::getWord('relation'),
            'ss_language' => Trans::getWord('language'),
            'ss_name_space' => Trans::getWord('nameSpace'),
            'ss_decimal_number' => Trans::getWord('decimalNumber'),
            'ss_decimal_separator' => Trans::getWord('decimalSeparator'),
            'ss_thousand_separator' => Trans::getWord('thousandSeparator'),
            'ss_system' => Trans::getWord('system'),
            'ss_active' => Trans::getWord('active'),

        ]);
        # Load the data for SystemSetting.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['ss_id']);
        $this->ListingTable->setColumnType('ss_decimal_number', 'integer');
        $this->ListingTable->setColumnType('ss_system', 'yesno');
        $this->ListingTable->setColumnType('ss_active', 'yesno');
        $this->ListingTable->addColumnAttribute('ss_decimal_separator', 'style', 'text-align: center');
        $this->ListingTable->addColumnAttribute('ss_thousand_separator', 'style', 'text-align: center');
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return SystemSettingDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = SystemSettingDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());
        $results = [];
        foreach ($data as $row) {
            if ($row['ss_decimal_separator'] === '.') {
                $row['ss_decimal_separator'] = Trans::getWord('dot') . ' (' . $row['ss_decimal_separator'] . ')';
            } else {
                $row['ss_decimal_separator'] = Trans::getWord('comma') . ' (' . $row['ss_decimal_separator'] . ')';
            }
            if ($row['ss_thousand_separator'] === '.') {
                $row['ss_thousand_separator'] = Trans::getWord('dot') . ' (' . $row['ss_thousand_separator'] . ')';
            } else {
                $row['ss_thousand_separator'] = Trans::getWord('comma') . ' (' . $row['ss_thousand_separator'] . ')';
            }
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

        if ($this->isValidParameter('ss_relation') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('ss.ss_relation', $this->getStringParameter('ss_relation'));
        }

        if ($this->isValidParameter('ss_active') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ss.ss_active', $this->getStringParameter('ss_active'));
        }
        return $wheres;
    }
}
