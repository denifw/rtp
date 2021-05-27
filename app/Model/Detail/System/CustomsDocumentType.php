<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Detail\System;

use App\Frame\Formatter\Trans;
use App\Model\Dao\System\CustomsDocumentTypeDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;

/**
 * Class to handle the creation of detail CustomsDocumentType page
 *
 * @package    app
 * @subpackage Model\Detail\System
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class CustomsDocumentType extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'customsDocumentType', 'cdt_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $colVal = [
            'cdt_name' => $this->getStringParameter('cdt_name'),
            'cdt_active' => $this->getStringParameter('cdt_active', 'Y'),
        ];
        $cdtDao = new CustomsDocumentTypeDao();
        $cdtDao->doInsertTransaction($colVal);

        return $cdtDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'cdt_name' => $this->getStringParameter('cdt_name'),
            'cdt_active' => $this->getStringParameter('cdt_active', 'Y'),
        ];
        $cdtDao = new CustomsDocumentTypeDao();
        $cdtDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return CustomsDocumentTypeDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('cdt_name', 3, 125);
        $this->Validation->checkUnique('cdt_name', 'customs_document_type', [
            'cdt_id' => $this->getDetailReferenceValue()
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field to field set
        $nameField = $this->Field->getText('cdt_name', $this->getStringParameter('cdt_name'));
        $fieldSet->addField(Trans::getWord('name'), $nameField,true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('cdt_active', $this->getStringParameter('cdt_active')));
        # Create a portlet box.
        $portlet = new Portlet('GnrlPtl', Trans::getWord('general'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


}
