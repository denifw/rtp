<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Listing\System\Service;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\System\Service\ServiceTermDao;

/**
 * Class to manage the creation of the listing ServiceTerm page.
 *
 * @package    app
 * @subpackage Model\Listing\System\Service
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class ServiceTerm extends AbstractListingModel
{

    /**
     * ServiceTerm constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'serviceTerm');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $this->ListingForm->addField(Trans::getWord('service'), $this->Field->getText('srv_name', $this->getStringParameter('srv_name')));
        $this->ListingForm->addField(Trans::getWord('serviceTerm'), $this->Field->getText('srt_name', $this->getStringParameter('srt_name')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('srt_active', $this->getStringParameter('srt_active')));
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
                'srt_service' => Trans::getWord('service'),
                'srt_name' => Trans::getWord('name'),
                'srt_description' => Trans::getWord('description'),
                'srt_route' => Trans::getWord('route'),
                'srt_container' => Trans::getWord('container'),
                'srt_load' => Trans::getWord('load'),
                'srt_unload' => Trans::getWord('unload'),
                'srt_pol' => Trans::getWord('pol'),
                'srt_pod' => Trans::getWord('pod'),
                'srt_active' => Trans::getWord('active'),
            ]
        );
        # Load the data for ServiceTerm.
        $this->ListingTable->addRows($this->loadData());
        # Add special settings to the table
        $this->ListingTable->setColumnType('srt_container', 'yesno');
        $this->ListingTable->setColumnType('srt_load', 'yesno');
        $this->ListingTable->setColumnType('srt_unload', 'yesno');
        $this->ListingTable->setColumnType('srt_pol', 'yesno');
        $this->ListingTable->setColumnType('srt_pod', 'yesno');
        $this->ListingTable->setColumnType('srt_active', 'yesno');
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['srt_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return ServiceTermDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     * @return array
     */
    private function loadData(): array
    {

        return ServiceTermDao::loadData(
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
            $wheres[] = SqlHelper::generateLikeCondition('srv.srv_name', $this->getStringParameter('srv_name'));
        }
        if ($this->isValidParameter('srt_name')) {
            $wheres[] = SqlHelper::generateLikeCondition('srt.srt_name', $this->getStringParameter('srt_name'));
        }
        if ($this->isValidParameter('srt_active')) {
            $wheres[] = SqlHelper::generateStringCondition('srt.srt_active', $this->getStringParameter('srt_active'));
        }
        # return the where query.
        return $wheres;
    }
}
