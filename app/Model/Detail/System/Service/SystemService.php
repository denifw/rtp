<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Detail\System\Service;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Setting\Action\SystemActionDao;
use App\Model\Dao\System\Service\ActionDao;
use App\Model\Dao\System\Service\SystemServiceDao;

/**
 * Class to handle the creation of detail Service page
 *
 * @package    app
 * @subpackage Model\Detail\System\Service
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class SystemService extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'systemService', 'ssr_id');
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
            'ssr_ss_id' => $this->getIntParameter('ssr_ss_id'),
            'ssr_srv_id' => $this->getIntParameter('ssr_srv_id'),
            'ssr_srt_id' => $this->getIntParameter('ssr_srt_id'),
            'ssr_active' => $this->getStringParameter('ssr_active', 'Y'),
        ];
        $serviceDao = new SystemServiceDao();
        $serviceDao->doInsertTransaction($colVal);
        # Load service Term Action
        $actions = ActionDao::getByServiceTermId($this->getIntParameter('ssr_srt_id'));
        $sacDao = new SystemActionDao();
        foreach ($actions as $row) {
            $sacColVal = [
                'sac_ss_id' => $this->getIntParameter('ssr_ss_id'),
                'sac_srt_id' => $this->getIntParameter('ssr_srt_id'),
                'sac_ac_id' => $row['ac_id'],
                'sac_order' => $row['ac_order'],
            ];
            $sacDao->doInsertTransaction($sacColVal);
        }
        return $serviceDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'ssr_ss_id' => $this->getIntParameter('ssr_ss_id'),
            'ssr_srv_id' => $this->getIntParameter('ssr_srv_id'),
            'ssr_srt_id' => $this->getIntParameter('ssr_srt_id'),
            'ssr_active' => $this->getStringParameter('ssr_active', 'Y'),
        ];
        $serviceDao = new SystemServiceDao();
        $serviceDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return SystemServiceDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('ssr_srv_id');
        $this->Validation->checkRequire('ssr_srt_id');
        $this->Validation->checkRequire('ssr_ss_id');
        $this->Validation->checkUnique('ssr_srt_id', 'system_service', [
            'ssr_id' => $this->getDetailReferenceValue(),
        ], [
            'ssr_ss_id' => $this->getIntParameter('ssr_ss_id'),
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
        $fieldSet->setGridDimension(12, 12, 12);
        $systemOwnerField = $this->Field->getSingleSelect('systemSetting', 'ss_relation', $this->getStringParameter('ss_relation'));
        $systemOwnerField->setHiddenField('ssr_ss_id', $this->getIntParameter('ssr_ss_id'));
        $systemOwnerField->setEnableNewButton(false);
        $systemOwnerField->setEnableDetailButton(false);

        $serviceField = $this->Field->getSingleSelect('service', 'srv_name', $this->getStringParameter('srv_name'));
        $serviceField->setHiddenField('ssr_srv_id', $this->getIntParameter('ssr_srv_id'));
        $serviceField->setEnableDetailButton(false);
        $serviceField->setEnableNewButton(false);

        $srtField = $this->Field->getSingleSelect('serviceTerm', 'srt_name', $this->getStringParameter('srt_name'));
        $srtField->setHiddenField('ssr_srt_id', $this->getIntParameter('ssr_srt_id'));
        $srtField->addOptionalParameterById('srt_srv_id', 'ssr_srv_id');
        $srtField->setEnableDetailButton(false);
        $srtField->setEnableNewButton(false);

        if ($this->isUpdate() === true) {
            $systemOwnerField->setReadOnly();
            $serviceField->setReadOnly();
            $srtField->setReadOnly();
        }

        # Add field to field set
        $fieldSet->addField(Trans::getWord('systemOwner'), $systemOwnerField, true);
        $fieldSet->addField(Trans::getWord('service'), $serviceField, true);
        $fieldSet->addField(Trans::getWord('serviceTerm'), $srtField, true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('ssr_active', $this->getStringParameter('ssr_active')));
        # Create a portlet box.
        $portlet = new Portlet('systemServiceGeneralPortlet', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


}
