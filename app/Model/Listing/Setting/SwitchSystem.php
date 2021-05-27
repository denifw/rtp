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

/**
 * Class to control the system of SwitchSystem.
 *
 * @package    app
 * @subpackage Model\Listing\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SwitchSystem extends AbstractListingModel
{


    /**
     * Property to store the system data.
     *
     * @var $System array.
     */
    private $Systems = [];

    /**
     * SwitchSystem constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'switchSystem');
        $this->setParameters($parameters);
        $this->loadData();
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        $this->Pagination->setRowsPerPage(count($this->Systems));
        # set header column table
        $this->ListingTable->setHeaderRow([
            'ss_relation' => Trans::getWord('name'),
        ]);
        # Load the data for SwitchSystem.
        $this->ListingTable->addRows($this->Systems);
        $this->ListingTable->setViewActionByHyperlink('doSwitch', ['ss_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return count($this->Systems);
    }


    /**
     * Get query to get the listing data.
     *
     * @return void
     */
    private function loadData(): void
    {
        if ($this->User->isMappingEnabled()) {
            $this->Systems = $this->User->getMapping();
        }
    }

}
