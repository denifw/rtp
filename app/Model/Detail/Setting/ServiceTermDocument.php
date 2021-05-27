<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Setting;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Setting\ServiceTermDocumentDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail ServiceTermDocument page
 *
 * @package    app
 * @subpackage Model\Detail\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class ServiceTermDocument extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'serviceTermDocument', 'std_id');
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
            'std_ss_id' => $this->User->getSsId(),
            'std_srt_id' => $this->getIntParameter('std_srt_id'),
            'std_dct_id' => $this->getIntParameter('std_dct_id'),
            'std_general' => $this->getStringParameter('std_general', 'N'),
        ];
        $stdDao = new ServiceTermDocumentDao();
        $stdDao->doInsertTransaction($colVal);

        return $stdDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doDelete') {
            $stdDao = new ServiceTermDocumentDao();
            $stdDao->doDeleteTransaction($this->getDetailReferenceValue());
        } else {
            $colVal = [
                'std_ss_id' => $this->User->getSsId(),
                'std_srt_id' => $this->getIntParameter('std_srt_id'),
                'std_dct_id' => $this->getIntParameter('std_dct_id'),
                'std_general' => $this->getStringParameter('std_general', 'N'),
            ];
            $stdDao = new ServiceTermDocumentDao();
            $stdDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return ServiceTermDocumentDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate()) {
            if ($this->isValidParameter('std_deleted_on') === false) {
                $deleteModal = $this->getDeleteModal();
                $this->View->addModal($deleteModal);
                $btnDel = new ModalButton('btnSdDel', Trans::getWord('delete'), $deleteModal->getModalId());
                $btnDel->setIcon(Icon::Trash)->btnDanger()->pullRight()->btnMedium();
                $this->View->addButton($btnDel);
            } else {
                $this->setDisableUpdate();
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
        if (empty($this->getFormAction())) {
            $this->Validation->checkRequire('srv_id');
            $this->Validation->checkRequire('std_srt_id');
            $this->Validation->checkRequire('std_dct_id');
            $this->Validation->checkUnique('std_dct_id', 'service_term_document', [
                'std_id' => $this->getDetailReferenceValue()
            ], [
                'std_ss_id' => $this->User->getSsId(),
                'std_srt_id' => $this->getIntParameter('std_srt_id'),
            ]);
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create Fields.

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $serviceField = $this->Field->getSingleSelect('service', 'srv_name', $this->getStringParameter('srv_name'));
        $serviceField->setHiddenField('srv_id', $this->getIntParameter('srv_id'));
        $serviceField->addParameter('ssr_ss_id', $this->User->getSsId());
        $serviceField->setEnableNewButton(false);
        $serviceField->setEnableDetailButton(false);
        $serviceField->addClearField('srt_name');
        $serviceField->addClearField('std_srt_id');

        $termField = $this->Field->getSingleSelect('serviceTerm', 'srt_name', $this->getStringParameter('srt_name'));
        $termField->setHiddenField('std_srt_id', $this->getIntParameter('std_srt_id'));
        $termField->addParameterById('srt_srv_id', 'srv_id', Trans::getWord('service'));
        $termField->addParameter('ssr_ss_id', $this->User->getSsId());
        $termField->setEnableNewButton(false);
        $termField->setEnableDetailButton(false);

        $dctField = $this->Field->getSingleSelect('documentType', 'dct_code', $this->getStringParameter('dct_code'));
        $dctField->setHiddenField('std_dct_id', $this->getIntParameter('std_dct_id'));
        $dctField->addParameter('dcg_code', 'joborder');
        $dctField->setEnableNewButton(false);
        $dctField->setEnableDetailButton(false);

        # Add field to fieldset.
        $fieldSet->addField(Trans::getWord('service'), $serviceField, true);
        $fieldSet->addField(Trans::getWord('serviceTerm'), $termField, true);
        $fieldSet->addField(Trans::getWord('documentType'), $dctField, true);
        $fieldSet->addField(Trans::getWord('generalDocument'), $this->Field->getYesNo('std_general', $this->getStringParameter('std_general')));
        # Create a portlet box.
        $portlet = new Portlet('StdGeneralId', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }
    /**
     * Function to get storage delete modal.
     *
     * @return \App\Frame\Gui\Modal
     */
    private function getDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('SdDelMdl', Trans::getWord('deleteConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDelete');
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12);
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));

        return $modal;
    }
}
