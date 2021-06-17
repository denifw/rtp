<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\System\Access;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\SubmitButton;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\System\Access\UsersDao;

/**
 * Class to handle the creation of detail UserChangePassword page
 *
 * @package    app
 * @subpackage Model\Detail\User
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class UserChangePassword extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'changePassword', 'us_id');
        $this->setDetailReferenceValue($this->User->getId());
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {

        return '';
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdatePassword') {
            $colVal = [
                'us_password' => bcrypt($this->getStringParameter('new_password')),
            ];
            $userDao = new UsersDao();
            $userDao->doUpdateTransaction($this->getIntParameter('us_id'), $colVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return UsersDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->getFormAction() === 'doUpdatePassword' && $this->isValidPostValues() === true) {
            $this->setParameter('old_password', '');
            $this->setParameter('new_password', '');
            $this->setParameter('new_password_confirmation', '');
        }

        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        $this->loadButtons();
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdatePassword') {
            $this->Validation->checkRequire('old_password', 2);
            $this->Validation->checkCurrentPassword('old_password', $this->User->getId());
            $this->Validation->checkRequire('new_password', 5);
            $this->Validation->checkDifferent('new_password', 'old_password');
            $this->Validation->checkConfirmed('new_password');
        }
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        # Add field to field set
        $fieldSet->addField(Trans::getWord('currentPassword'), $this->Field->getPassword('old_password', $this->getStringParameter('old_password')), true);
        $fieldSet->addField(Trans::getWord('newPassword'), $this->Field->getPassword('new_password', $this->getStringParameter('new_password')), true);
        $fieldSet->addField(Trans::getWord('repeatNewPassword'), $this->Field->getPassword('new_password_confirmation', $this->getStringParameter('new_password_confirmation')), true);

        # Instantiate Portlet
        $portlet = new Portlet('GeneralPtl', Trans::getWord('formUpdate'));
        $portlet->setGridDimension(6, 6);
        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to load buttons
     *
     * @return void
     */
    private function loadButtons(): void
    {
        $this->setEnableCloseButton(false);
        $btnUpdate = new SubmitButton('btnUpPass', Trans::getWord('update'), 'doUpdatePassword', $this->getMainFormId());
        $btnUpdate->btnMedium()->btnPrimary()->pullRight();
        $this->View->addButton($btnUpdate);
        $btnClose = new HyperLink('btnClose', Trans::getWord('close'), url('/'));
        $btnClose->btnMedium()->btnDanger()->pullRight()->viewAsButton();
        $this->View->addButton($btnClose);

    }

}
