<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Setting;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Service\SystemServiceDao;

/**
 * Class to control the system of SystemService.
 *
 * @package    app
 * @subpackage Model\Listing\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemService extends AbstractListingModel
{

    /**
     * SystemService constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'activeService');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $serviceField = $this->Field->getSingleSelect('service', 'ssr_service', $this->getStringParameter('ssr_service'));
        $serviceField->setHiddenField('ssr_srv_id', $this->getIntParameter('ssr_srv_id'));
        $serviceField->addParameter('ssr_ss_id', $this->User->getSsId());
        $serviceField->setEnableNewButton(false);
        $serviceField->setEnableDetailButton(false);
        $this->ListingForm->addField(Trans::getWord('service'), $serviceField);
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
            'srv_name' => Trans::getWord('service'),
            'srt_name' => Trans::getWord('serviceTerm'),
            'total_action' => Trans::getWord('numberOfAction'),
        ]);
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setColumnType('total_action', 'integer');
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
        $wheres[] = '(ssr.ssr_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(ssr.ssr_deleted_on IS NULL)';
        $wheres[] = "(ssr.ssr_active = 'Y')";
        if ($this->isValidParameter('ssr_srv_id') === true) {
            $wheres[] = '(ssr.ssr_srv_id = ' . $this->getIntParameter('ssr_srv_id') . ')';
        }

        return $wheres;
    }
}
