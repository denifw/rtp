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
use App\Model\Dao\Master\Finance\PaymentTermsDao;

/**
 * Class to handle the creation of detail CostCode page
 *
 * @package    app
 * @subpackage Model\Detail\Master
 * @author    Deni Firdaus Waruwu<deni.fw@spada-informatika.com>
 * @copyright 2020 spada-informatika.com
 */
class PaymentTerms extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'pt', 'pt_id');
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
            'pt_ss_id' => $this->User->getSsId(),
            'pt_name' => $this->getStringParameter('pt_name'),
            'pt_days' => $this->getIntParameter('pt_days'),
            'pt_active' => $this->getStringParameter('pt_active', 'Y'),
        ];
        $ptDao = new PaymentTermsDao();
        $ptDao->doInsertTransaction($colVal);

        return $ptDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'pt_name' => $this->getStringParameter('pt_name'),
            'pt_days' => $this->getIntParameter('pt_days'),
            'pt_active' => $this->getStringParameter('pt_active', 'Y'),
        ];
        $ptDao = new PaymentTermsDao();
        $ptDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return PaymentTermsDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
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
        $this->Validation->checkRequire('pt_name', 2, 150);
        $this->Validation->checkUnique('pt_name', 'payment_terms', [
            'pt_id' => $this->getDetailReferenceValue()
        ], [
            'pt_ss_id' => $this->User->getSsId()
        ]);
        $this->Validation->checkRequire('pt_days');
        $this->Validation->checkInt('pt_days', 0);
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
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('pt_name', $this->getStringParameter('pt_name')), true);
        $fieldSet->addField(Trans::getWord('days'), $this->Field->getNumber('pt_days', $this->getIntParameter('pt_days')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('pt_active', $this->getStringParameter('pt_active')));
        # Create a portlet box.
        $portlet = new Portlet('ptGeneralPtl', Trans::getWord('general'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12);

        return $portlet;
    }
}
