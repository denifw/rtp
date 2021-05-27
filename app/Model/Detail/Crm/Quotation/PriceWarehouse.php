<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\Crm\Quotation;

use App\Frame\Gui\Modal;
use App\Model\Dao\System\Location\DistrictCodeDao;
use App\Model\Dao\System\Service\ServiceDao;

/**
 * Class to handle the creation of detail PriceInklaring page
 *
 * @package    app
 * @subpackage Model\Detail\Crm\Quotation
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class PriceWarehouse extends AbstractPrice
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'prcSlsWh', 'prc_id');
        $this->setParameters($parameters);
        if ($this->isInsert() === true) {
            $this->setService(ServiceDao::getServiceWarehouse());
        }
        $this->setParameter('prc_type', 'S');
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        return $this->doInsertWarehouse();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $this->doUpdateWarehouse();
        } elseif ($this->getFormAction() === 'doCopy') {
            $newPrcId = $this->doInsertWarehouse(true);
            $this->doCopyPriceDetail($newPrcId);
        } else {
            parent::doUpdate();
        }
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        parent::loadForm();
        $this->Tab->addPortlet('general', $this->getWarehousePortlet());
        if ($this->isUpdate() === true) {
            $this->Tab->addPortlet('general', $this->getPriceDetailFieldSet());
        }

    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {
            $this->loadValidationWarehouse();
        } elseif ($this->getFormAction() === 'doCopy') {
            $this->loadValidationWarehouse(true);
        } else {
            parent::loadValidationRole();
        }
    }

    /**
     * Function create copy modal
     *
     * @return Modal
     */
    protected function getCopyModal(): Modal
    {
        $modal = parent::getCopyModal();
        $modal->addFieldSet($this->getCopyWarehouseFieldSet(true));

        # Create Custom Field
        return $modal;
    }


}
