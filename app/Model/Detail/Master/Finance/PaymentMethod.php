<?php

/**
 * Contains code written by the MBS Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Deni Firdaus Waruwu<deni.fw@spada-informatika.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Detail\Master\Finance;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Master\Finance\PaymentMethodDao;

/**
 * Class to handle the creation of detail CostCode page
 *
 * @package    app
 * @subpackage Model\Detail\Master
 * @author    Deni Firdaus Waruwu<deni.fw@spada-informatika.com>
 * @copyright 2020 spada-informatika.com
 */
class PaymentMethod extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'pm', 'pm_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $colVal = [
            'pm_ss_id' => $this->User->getSsId(),
            'pm_name' => $this->getStringParameter('pm_name'),
            'pm_active' => $this->getStringParameter('pm_active', 'Y'),
        ];
        $pmDao = new PaymentMethodDao();
        $pmDao->doInsertTransaction($colVal);

        return $pmDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'pm_name' => $this->getStringParameter('pm_name'),
            'pm_active' => $this->getStringParameter('pm_active', 'Y'),
        ];
        $pmDao = new PaymentMethodDao();
        $pmDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return PaymentMethodDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('pm_name', 2, 150);
        $this->Validation->checkUnique('pm_name', 'payment_method', [
            'pm_id' => $this->getDetailReferenceValue()
        ], [
            'pm_ss_id' => $this->User->getSsId()
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('pm_name', $this->getStringParameter('pm_name')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('pm_active', $this->getStringParameter('pm_active')));
        # Create a portlet box.
        $portlet = new Portlet('pmGeneralPtl', Trans::getWord('general'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12);

        return $portlet;
    }
}
