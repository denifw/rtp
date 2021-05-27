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
use App\Model\Dao\System\Service\ServiceDao;

/**
 * Class to handle the creation of detail PriceInklaring page
 *
 * @package    app
 * @subpackage Model\Detail\Crm\Quotation
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class PriceInklaring extends AbstractPrice
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'prcSlsInk', 'prc_id');
        $this->setParameters($parameters);
        if ($this->isInsert() === true) {
            $this->setService(ServiceDao::getServiceInklaring());
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
        return $this->doInsertInklaring();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $this->doUpdateInklaring();
        } elseif ($this->getFormAction() === 'doCopy') {
            $newPrcId = $this->doInsertInklaring(true);
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
        $this->setInklaringPortParameter();
        parent::loadForm();
        $this->Tab->addPortlet('general', $this->getInklaringPortlet());
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
            $this->loadValidationInklaring();
        } elseif ($this->getFormAction() === 'doCopy') {
            $this->loadValidationInklaring(true);
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
        $modal->addFieldSet($this->getCopyInklaringFieldSet(true));

        # Create Custom Field
        return $modal;
    }

}
