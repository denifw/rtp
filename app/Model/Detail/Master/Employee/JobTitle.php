<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Detail\Master\Employee;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Master\Employee\JobTitleDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail JobTitle page
 *
 * @package    app
 * @subpackage Model\Detail\Master\Employee
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class JobTitle extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'jt', 'jt_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $jtColVal = [
            'jt_ss_id' => $this->User->getSsId(),
            'jt_description' => $this->getStringParameter('jt_description'),
        ];
        $jtDao = new JobTitleDao();
        $jtDao->doInsertTransaction($jtColVal);
        return $jtDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $jtDao = new JobTitleDao();
            $jtDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'jt_description' => $this->getStringParameter('jt_description'),
            ]);
        } else if ($this->isDeleteAction() === true) {
            $jtDao = new JobTitleDao();
            $jtDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return JobTitleDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isUpdate() === true) {
            $status = new LabelSuccess(Trans::getWord('active'));
            if ($this->isValidParameter('jt_deleted_on') === true) {
                $this->View->addErrorMessage(Trans::getWord('deletedData', 'message', '', [
                    'user' => $this->getStringParameter('jt_deleted_by'),
                    'time' => DateTimeParser::format($this->getStringParameter('jt_deleted_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                    'reason' => $this->getStringParameter('jt_deleted_reason')
                ]));
                $status = new LabelDanger(Trans::getWord('deleted'));
            }
            $this->View->setDescription($this->PageSetting->getPageDescription() . ' - ' . $status);
        }
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
            $this->Validation->checkRequire('jt_description', 2, 256);
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
        $portlet = new Portlet('JtPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(6, 6);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('jt_description', $this->getStringParameter('jt_description')), true);

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isValidParameter('jt_deleted_on') === false) {
            $this->setEnableDeleteButton(true);
        } else {
            $this->setDisableUpdate();
        }
        parent::loadDefaultButton();
    }
}
