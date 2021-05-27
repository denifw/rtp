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
 * Class to handle the creation of detail PriceTrucking page
 *
 * @package    app
 * @subpackage Model\Detail\Crm\Quotation
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class CogsDelivery extends AbstractPrice
{

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'prcPrcDl', 'prc_id');
        $this->setParameters($parameters);
        if ($this->isInsert() === true) {
            $this->setService(ServiceDao::getServiceDelivery());
        }
        $this->setParameter('prc_type', 'P');
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        return $this->doInsertTrucking();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $this->doUpdateTrucking();
        } elseif ($this->getFormAction() === 'doCopy') {
            $newPrcId = $this->doInsertTrucking(true);
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
        if ($this->isUpdate() === true) {
            if ($this->isValidParameter('prc_dtc_origin') === true) {
                $dtcCodeOrigin = DistrictCodeDao::getBySystemAndDistrictId($this->User->getSsId(), $this->getIntParameter('prc_dtc_origin'));
                if (empty($dtcCodeOrigin) === false) {
                    $this->setParameter('prc_dtc_or_code', $dtcCodeOrigin['dtcc_code']);
                    $this->setParameter('prc_or_dtcc_code', $dtcCodeOrigin['dtcc_code']);
                    $this->setParameter('prc_or_dtcc_id', $dtcCodeOrigin['dtcc_id']);
                }
            }
            # Load destination district code.
            if ($this->isValidParameter('prc_dtc_destination') === true) {
                $dtcCodeDestination = DistrictCodeDao::getBySystemAndDistrictId($this->User->getSsId(), $this->getIntParameter('prc_dtc_destination'));
                if (empty($dtcCodeDestination) === false) {
                    $this->setParameter('prc_dtc_des_code', $dtcCodeDestination['dtcc_code']);
                    $this->setParameter('prc_des_dtcc_code', $dtcCodeDestination['dtcc_code']);
                    $this->setParameter('prc_des_dtcc_id', $dtcCodeDestination['dtcc_id']);
                }
            }
        }
        parent::loadForm();
        $this->Tab->addPortlet('general', $this->getTruckingPortlet());
        if ($this->isUpdate() === true) {
            $this->Tab->addPortlet('general', $this->getTruckingDetailPortlet());
            if($this->isValidParameter('prc_code') === true) {
                $this->Tab->addPortlet('general', $this->getPriceDetailFieldSet());
            }
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
            $this->loadValidationTrucking();
        } elseif ($this->getFormAction() === 'doCopy') {
            $this->loadValidationTrucking(true);
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
        $modal->addFieldSet($this->getCopyTruckingFieldSet(true));

        # Create Custom Field
        return $modal;
    }


}
