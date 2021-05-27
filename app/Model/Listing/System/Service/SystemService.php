<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   MBS
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 Spada
 */

namespace App\Model\Listing\System\Service;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Service\SystemServiceDao;
use App\Model\Dao\System\SystemSettingDao;

/**
 * Class to manage the creation of the listing Service page.
 *
 * @package    App
 * @subpackage Model\Listing\System\Service
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class SystemService extends AbstractListingModel
{

    /**
     * Service constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter for the page.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'systemService');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $ssField = $this->Field->getSelect('ssr_ss_id', $this->getIntParameter('ssr_ss_id'));
        $ssField->addOptions(SystemSettingDao::loadAllData(), 'ss_relation', 'ss_id');
        $this->ListingForm->addField(Trans::getWord('systemSetting'), $ssField);
        $this->ListingForm->addField(Trans::getWord('service'), $this->Field->getText('srv_name', $this->getStringParameter('srv_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('ssr_active', $this->getStringParameter('ssr_active')));
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
                'ss_relation' => Trans::getWord('systemOwner'),
                'srv_name' => Trans::getWord('service'),
                'srt_name' => Trans::getWord('serviceTerm'),
                'ssr_active' => Trans::getWord('active'),
            ]
        );
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setColumnType('ssr_active', 'yesno');
        # Add special settings to the table
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['ssr_id'], true);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return SystemServiceDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return SystemServiceDao::loadData(
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
        if ($this->isValidParameter('srv_name')) {
            $wheres[] = StringFormatter::generateLikeQuery('srv_name', $this->getStringParameter('srv_name'));
        }
        if ($this->isValidParameter('ssr_ss_id')) {
            $wheres[] = '(ssr.ssr_ss_id = ' . $this->getIntParameter('ssr_ss_id') . ')';
        }
        if ($this->isValidParameter('ssr_active')) {
            $wheres[] = '(ssr.ssr_active = \'' . $this->getStringParameter('ssr_active') . '\')';
        }
        if ($this->User->isUserSystem() === false) {
            $wheres[] = '(ssr.ssr_ss_id = ' . $this->User->getSsId() . ')';
        }
        return $wheres;
    }
}
