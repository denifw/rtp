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
use App\Model\Dao\System\Document\DocumentTemplateTypeDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail DocumentTemplateType page
 *
 * @package    app
 * @subpackage Model\Detail\System\Document
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class DocumentTemplateType extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'dtt', 'dtt_id');
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
            'dtt_code' => mb_strtolower($this->getStringParameter('dtt_code')),
            'dtt_description' => $this->getStringParameter('dtt_description'),
            'dtt_active' => $this->getStringParameter('dtt_active', 'Y')
        ];
        $dttDao = new DocumentTemplateTypeDao();
        $dttDao->doInsertTransaction($colVal);
        return $dttDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'dtt_code' => mb_strtolower($this->getStringParameter('dtt_code')),
            'dtt_description' => $this->getStringParameter('dtt_description'),
            'dtt_active' => $this->getStringParameter('dtt_active')
        ];
        $dttDao = new DocumentTemplateTypeDao();
        $dttDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);

    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return DocumentTemplateTypeDao::getByReference($this->getDetailReferenceValue());
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
            $this->Validation->checkRequire('dtt_code', '2', '125');
            $this->Validation->checkUnique('dtt_code', 'document_template_type', [
                'dtt_id' => $this->getDetailReferenceValue()
            ], [
                'dtt_code' => $this->getStringParameter('dtt_code')
            ]);
            $this->Validation->checkRequire('dtt_description', '2', '255');
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
        $portlet = new Portlet('DttPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(6, 6);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('dtt_code', $this->getStringParameter('dtt_code')), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getTextArea('dtt_description', $this->getStringParameter('dtt_description')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('dtt_active', $this->getStringParameter('dtt_active')));

        # Add field to field set

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }
}
