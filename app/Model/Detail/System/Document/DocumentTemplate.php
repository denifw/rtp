<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\System\Document;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\Document\DocumentTemplateDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail DocumentTemplate page
 *
 * @package    app
 * @subpackage Model\Detail\System\Document
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class DocumentTemplate extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'documentTemplate', 'dt_id');
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
            'dt_dtt_id' => $this->getIntParameter('dt_dtt_id'),
            'dt_description' => $this->getStringParameter('dt_description'),
            'dt_path' => $this->getStringParameter('dt_path'),
            'dt_active' => $this->getStringParameter('dt_active','Y')
        ];

        $dtDao = new DocumentTemplateDao();
        $dtDao->doInsertTransaction($colVal);
        return $dtDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'dt_dtt_id' => $this->getIntParameter('dt_dtt_id'),
            'dt_description' => $this->getStringParameter('dt_description'),
            'dt_path' => $this->getStringParameter('dt_path'),
            'dt_active' => $this->getStringParameter('dt_active')
        ];

        $dtDao = new DocumentTemplateDao();
        $dtDao->doUpdateTransaction($this->getDetailReferenceValue(),$colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return DocumentTemplateDao::getByReference($this->getDetailReferenceValue());
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
            $this->Validation->checkRequire('dt_dtt_id');
            $this->Validation->checkRequire('dt_description',2,255);
            $this->Validation->checkRequire('dt_path',2,255);
            $this->Validation->checkUnique('dt_path','document_template',[
                'dt_id' => $this->getDetailReferenceValue()
            ],[
                'dt_dtt_id' => $this->getIntParameter('dt_dtt_id')
            ]);
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
        $portlet = new Portlet('DtPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(6,6);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12,12);

        $dtField = $this->Field->getSingleSelect('documentTemplateType','dt_dtt_description',$this->getStringParameter('dt_dtt_description'));
        $dtField->setHiddenField('dt_dtt_id',$this->getIntParameter('dt_dtt_id'));
        $dtField->setEnableNewButton(false);

        # Add field to field set
        $fieldSet->addField(Trans::getWord('templateType'),$dtField,true);
        $fieldSet->addField(Trans::getWord('description'),$this->Field->getText('dt_description',$this->getStringParameter('dt_description')),true);
        $fieldSet->addField(Trans::getWord('path'),$this->Field->getText('dt_path',$this->getStringParameter('dt_path')),true);
        $fieldSet->addField(Trans::getWord('active'),$this->Field->getYesNo('dt_active',$this->getStringParameter('dt_active')));

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }
}
