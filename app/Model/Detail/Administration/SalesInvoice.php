<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Detail\Administration;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Administration\SalesInvoiceDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail SalesInvoice page
 *
 * @package    app
 * @subpackage Model\Detail\Administration
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class SalesInvoice extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'si', 'si_id');
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
            'si_ss_id' => $this->User->getSsId(),
            'si_rel_id' => $this->getStringParameter('si_rel_id'),
            'si_of_id' => $this->getStringParameter('si_of_id'),
            'si_cp_id' => $this->getStringParameter('si_cp_id'),
            'si_jo_id' => $this->getStringParameter('si_jo_id'),
            'si_pt_id' => $this->getStringParameter('si_pt_id'),
        ];
        $siDao = new SalesInvoiceDao();
        $siDao->doInsertTransaction($colVal);
        return $siDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $colVal = [
                'si_ss_id' => $this->User->getSsId(),
                'si_rel_id' => $this->getStringParameter('si_rel_id'),
                'si_of_id' => $this->getStringParameter('si_of_id'),
                'si_cp_id' => $this->getStringParameter('si_cp_id'),
                'si_jo_id' => $this->getStringParameter('si_jo_id'),
                'si_pt_id' => $this->getStringParameter('si_pt_id'),
            ];
            $siDao = new SalesInvoiceDao();
            $siDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return SalesInvoiceDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {
            # TODO: Set the validation rule here.
        } else {
            parent::loadValidationRole();
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('SiPtl', $this->getDefaultPortletTitle());

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Relation
        $relField = $this->Field->getSingleSelect('rel', 'si_customer', $this->getStringParameter('si_customer'));
        $relField->setHiddenField('si_rel_id', $this->getStringParameter('si_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setDetailReferenceCode('rel_id');
        $relField->addClearField('si_of_customer');
        $relField->addClearField('si_of_id');
        $relField->addClearField('si_pic_customer');
        $relField->addClearField('si_cp_id');
        # Office
        $ofField = $this->Field->getSingleSelect('of', 'si_of_customer', $this->getStringParameter('si_of_customer'));
        $ofField->setHiddenField('si_of_id', $this->getStringParameter('si_of_id'));
        $ofField->addParameterById('of_rel_id', 'si_rel_id', Trans::getWord('customer'));
        $ofField->setDetailReferenceCode('of_id');
        $ofField->addClearField('si_pic_customer');
        $ofField->addClearField('si_cp_id');
        # Contact Person
        $cpField = $this->Field->getSingleSelect('cp', 'si_pic_customer', $this->getStringParameter('si_pic_customer'));
        $cpField->setHiddenField('si_cp_id', $this->getStringParameter('si_cp_id'));
        $cpField->addParameterById('cp_rel_id', 'si_rel_id', Trans::getWord('customer'));
        $cpField->addParameterById('cp_of_id', 'si_of_id', Trans::getWord('customerOffice'));
        $cpField->setDetailReferenceCode('cp_id');

        # Job Order
        $joField = $this->Field->getSingleSelect('jo', 'si_jo_number', $this->getStringParameter('si_jo_number'));
        $joField->setHiddenField('si_jo_id', $this->getStringParameter('si_jo_id'));
        $joField->addParameter('jo_ss_id', $this->User->getSsId());
        $joField->addParameterById('jo_rel_id', 'si_rel_id', Trans::getWord('customer'));
        $joField->addParameter('jo_active', 'Y');
        $joField->setDetailReferenceCode('jo_id');
        $joField->setEnableNewButton(false);

        # Payment Terms
        $ptField = $this->Field->getSingleSelect('pt', 'si_payment_terms', $this->getStringParameter('si_payment_terms'));
        $ptField->setHiddenField('si_pt_id', $this->getStringParameter('si_pt_id'));
        $ptField->addParameter('pt_ss_id', $this->User->getSsId());
        $ptField->setDetailReferenceCode('pt_id');

        # Payment Terms
        $baField = $this->Field->getSingleSelect('ba', 'si_bank_account', $this->getStringParameter('si_bank_account'));
        $baField->setHiddenField('si_ba_id', $this->getStringParameter('si_ba_id'));
        $baField->addParameter('ba_ss_id', $this->User->getSsId());
        $baField->addParameter('ba_main', 'Y');
        $baField->addParameter('ba_receivable', 'Y');
        $baField->setDetailReferenceCode('pt_id');
        $baField->setEnableNewButton(false);

        # Add field to field set
        $fieldSet->addField(Trans::getWord('customer'), $relField, true);
        $fieldSet->addField(Trans::getWord('jobNumber'), $joField);
        $fieldSet->addField(Trans::getWord('customerOffice'), $ofField, true);
        $fieldSet->addField(Trans::getWord('picCustomer'), $cpField, true);
        $fieldSet->addField(Trans::getWord('ar'), $baField, true);
        $fieldSet->addField(Trans::getWord('paymentTerms'), $ptField, true);

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }
}
