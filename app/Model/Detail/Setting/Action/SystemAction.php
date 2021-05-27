<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Setting\Action;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Setting\Action\SystemActionDao;

/**
 * Class to handle the creation of detail SystemAction page
 *
 * @package    app
 * @subpackage Model\Detail\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemAction extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'systemAction', 'sac_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        if ($this->isValidParameter('sac_order') === true) {
            $order = $this->getIntParameter('sac_order');
        } else {
            $order = SystemActionDao::getLastOrderData($this->User->getSsId(), $this->getIntParameter('sac_srt_id')) + 1;
        }
        $colVal = [
            'sac_ss_id' => $this->User->getSsId(),
            'sac_srt_id' => $this->getIntParameter('sac_srt_id'),
            'sac_ac_id' => $this->getIntParameter('sac_ac_id'),
            'sac_order' => $order,
        ];
        $sacDao = new SystemActionDao();
        $sacDao->doInsertTransaction($colVal);

        return $sacDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'sac_ss_id' => $this->User->getSsId(),
            'sac_srt_id' => $this->getIntParameter('sac_srt_id'),
            'sac_ac_id' => $this->getIntParameter('sac_ac_id'),
            'sac_order' => $this->getIntParameter('sac_order'),
        ];
        $sacDao = new SystemActionDao();
        $sacDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return SystemActionDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->User->isUserSystem() === false) {
            Message::throwMessage(Trans::getWord('pageNotFound', 'message'), 'ERROR');
        }
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('sac_ac_id');
        if ($this->isUpdate() === true) {
            $this->Validation->checkRequire('sac_order');
        }
        if ($this->isValidParameter('sac_order') === true) {
            $this->Validation->checkInt('sac_order', 1);
            if ($this->isValidParameter('sac_srt_id') === true) {
                $this->Validation->checkUnique('sac_order', 'system_action', [
                    'sac_id' => $this->getDetailReferenceValue()
                ], [
                    'sac_srt_id' => $this->getIntParameter('sac_srt_id'),
                    'sac_ss_id' => $this->User->getSsId()
                ]);
            }
        }
        $this->Validation->checkUnique('sac_ac_id', 'system_action', [
            'sac_id' => $this->getDetailReferenceValue()
        ], [
            'sac_ss_id' => $this->User->getSsId()
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create Fields.
        $serviceField = $this->Field->getSingleSelect('service', 'sac_service', $this->getStringParameter('sac_service'));
        $serviceField->setHiddenField('sac_srv_id', $this->getIntParameter('sac_srv_id'));
        $serviceField->addParameter('ssr_ss_id', $this->User->getSsId());
        $serviceField->setEnableNewButton(false);
        $serviceField->setEnableDetailButton(false);
        $serviceField->addClearField('srt_name');
        $serviceField->addClearField('sac_srt_id');
        $serviceField->addClearField('ac_description');
        $serviceField->addClearField('sac_ac_id');

        $termField = $this->Field->getSingleSelect('serviceTerm', 'sac_service_term', $this->getStringParameter('sac_service_term'));
        $termField->setHiddenField('sac_srt_id', $this->getIntParameter('sac_srt_id'));
        $termField->addParameterById('srt_srv_id', 'sac_srv_id', Trans::getWord('service'));
        $termField->addParameter('ssr_ss_id', $this->User->getSsId());
        $termField->setEnableNewButton(false);
        $termField->setEnableDetailButton(false);
        $termField->addClearField('ac_description');
        $termField->addClearField('sac_ac_id');

        $actionField = $this->Field->getSingleSelect('action', 'sac_action', $this->getStringParameter('sac_action'));
        $actionField->setHiddenField('sac_ac_id', $this->getIntParameter('sac_ac_id'));
        $actionField->addParameterById('ac_srt_id', 'sac_srt_id', Trans::getWord('serviceTerm'));
        $actionField->setEnableNewButton(false);
        $actionField->setEnableDetailButton(false);


        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $fieldSet->addField(Trans::getWord('service'), $serviceField, true);
        $fieldSet->addField(Trans::getWord('serviceTerm'), $termField, true);
        $fieldSet->addField(Trans::getWord('action'), $actionField, true);
        $fieldSet->addField(Trans::getWord('orderNumber'), $this->Field->getText('sac_order', $this->getIntParameter('sac_order')));
        if ($this->isUpdate() === true) {
            $fieldSet->setRequiredFields(['sac_order']);
        }

        # Create a portlet box.
        $portlet = new Portlet('SacGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }
}
