<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\System\Access;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\SubmitButton;
use App\Frame\Gui\Icon;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\TableDatas;
use App\Model\Mail\MappingUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Class to handle the creation of detail UserMapping page
 *
 * @package    app
 * @subpackage Model\Detail\System\Access
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class UserMapping extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'userMapping', 'ump_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $umpColVal = [
            'ump_ss_id' => $this->getIntParameter('ump_ss_id'),
            'ump_us_id' => $this->getIntParameter('ump_us_id'),
            'ump_rel_id' => $this->getIntParameter('ump_rel_id'),
            'ump_cp_id' => $this->getIntParameter('ump_cp_id'),
            'ump_confirm' => 'N',
            'ump_default' => 'N',
            'ump_active' => 'Y',
        ];
        $umpDao = new UserMappingDao();
        $umpDao->doInsertTransaction($umpColVal);

        # Insert User Group.
        $ugdColVal = [
            'ugd_usg_id' => $this->getIntParameter('ugd_usg_id'),
            'ugd_ump_id' => $umpDao->getLastInsertId(),
        ];
        $ugdDao = new UserGroupDetailDao();
        $ugdDao->doInsertTransaction($ugdColVal);

        # Create user token for email confirmation user.
        $utDao = new UserTokenDao();
        $token = $utDao->generateTokenByUserAndSystem($this->getIntParameter('ump_us_id'), $this->getIntParameter('ump_ss_id'), 'MAPPING_USER');
        $expiredDate = $utDao->getExpiredDate('MAPPING_USER');
        $utColVal = [
            'ut_us_id' => $this->getIntParameter('ump_us_id'),
            'ut_ss_id' => $this->getIntParameter('ump_ss_id'),
            'ut_token' => $token,
            'ut_type' => 'MAPPING_USER',
            'ut_expired_on' => $expiredDate
        ];
        $utDao->doInsertTransaction($utColVal);
        # Send email confirmation.
        $user = UsersDao::getByReference($this->getIntParameter('ump_us_id'));
        $user['expired_date'] = $expiredDate;
        Mail::to($user['us_username'])->send(new MappingUser($user, $token));
        # Check if the email fail.
        $fails = Mail::failures();
        if (empty($fails) === false) {
            $this->addErrorMessage(Trans::getWord('successAddUserAndFailSendingEmail', 'message'));
        } else {
            $this->addSuccessMessage(Trans::getWord('successAddUserAndWaitingConfirmation', 'message'));
        }

        return $umpDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doSendEmailConfirmation') {
            $utDao = new UserTokenDao();
            # Remove old token
            $userToken = $utDao->getUserTokenByType($this->getIntParameter('ump_us_id'), $this->getIntParameter('ump_ss_id'), 'MAPPING_USER');
            if (empty($userToken) === false) {
                $utDao->doDeleteTransaction($userToken['ut_id']);
            }
            # Regenerate New Token
            $token = $utDao->generateTokenByUserAndSystem($this->getIntParameter('ump_us_id'), $this->getIntParameter('ump_ss_id'), 'MAPPING_USER');
            $expiredDate = $utDao->getExpiredDate('MAPPING_USER');
            $utColVal = [
                'ut_us_id' => $this->getIntParameter('ump_us_id'),
                'ut_ss_id' => $this->getIntParameter('ump_ss_id'),
                'ut_token' => $token,
                'ut_type' => 'MAPPING_USER',
                'ut_expired_on' => $expiredDate
            ];
            $utDao->doInsertTransaction($utColVal);
            # Send email confirmation.
            $user = UsersDao::getByReference($this->getIntParameter('ump_us_id'));
            $user['expired_date'] = $expiredDate;
            Mail::to($user['us_username'])->send(new MappingUser($user, $token));
            # Check if the email fail.
            $fails = Mail::failures();
            if (empty($fails) === false) {
                $this->addErrorMessage(Trans::getWord('successAddUserAndFailSendingEmail', 'message'));
            } else {
                $this->addSuccessMessage(Trans::getWord('successAddUserAndWaitingConfirmation', 'message'));
            }
        } else {
            $umpColVal = [
                'ump_ss_id' => $this->getIntParameter('ump_ss_id'),
                'ump_us_id' => $this->getIntParameter('ump_us_id'),
                'ump_rel_id' => $this->getIntParameter('ump_rel_id'),
                'ump_cp_id' => $this->getIntParameter('ump_cp_id'),
                'ump_default' => $this->getStringParameter('ump_default'),
                'ump_active' => $this->getStringParameter('ump_active'),
            ];
            $umpDao = new UserMappingDao();
            $umpDao->doUpdateTransaction($this->getDetailReferenceValue(), $umpColVal);

            # Start Update User Group
            $usgIds = $this->getArrayParameter('usg_id');
            $ugdIds = $this->getArrayParameter('ugd_id');
            $ugdActives = $this->getArrayParameter('ugd_active');
            if (\count($usgIds) > 0) {
                $ugdDao = new UserGroupDetailDao();
                foreach ($ugdIds as $key => $value) {
                    if (array_key_exists($key, $ugdActives) === true && $ugdActives[$key] === 'Y') {
                        if (empty($value) === true) {
                            $ugdColVal = [
                                'ugd_usg_id' => $usgIds[$key],
                                'ugd_ump_id' => $this->getDetailReferenceValue()
                            ];
                            $ugdDao->doInsertTransaction($ugdColVal);
                        } else {
                            $ugdDao->doUndoDeleteTransaction($value);
                        }
                    } else {
                        if (empty($value) === false) {
                            $ugdDao->doDeleteTransaction($value);
                        }
                    }
                }
            }
            # End Update User Group
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return UserMappingDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate() === true) {
            if ($this->getStringParameter('ump_confirm') === 'N') {
                $this->addWarningMessage('Warning!!!, ' . Trans::getWord('userWithoutEmailConfirmation', 'message'));
                $btn = new SubmitButton('btnReminder', Trans::getWord('sendReminder'), 'doSendEmailConfirmation', $this->getMainFormId());
                $btn->setIcon(Icon::ShareSquare)->btnPrimary()->pullRight();
                $this->View->addButton($btn);
                $this->setDisableUpdate();
            }
            $this->Tab->addPortlet('userGroup', $this->getUserGroupFieldSet());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateUserGroupDetail') {
            $this->Validation->checkRequire('ugd_usg_id');
            $this->Validation->checkUnique('ugd_usg_id', 'user_group_detail', [
                'ugd_id' => $this->getIntParameter('ugd_id')
            ], [
                'ugd_ump_id' => $this->getDetailReferenceValue(),
                'ugd_deleted_on' => null
            ]);
        } elseif ($this->getFormAction() === 'doDeleteUserGroupDetail') {
            $this->Validation->checkRequire('ugd_id_del');
        } else {
            if ($this->isInsert() === true) {
                $this->Validation->checkRequire('ugd_usg_id');
            }
            $this->Validation->checkRequire('ump_us_id');
            $this->Validation->checkRequire('ump_rel_id');
            $this->Validation->checkRequire('ump_ss_id');
            $this->Validation->checkRequire('ump_cp_id');
            $this->Validation->checkUnique('ump_us_id', 'user_mapping', [
                'ump_id' => $this->getIntParameter('ump_id')
            ], [
                'ump_ss_id' => $this->getIntParameter('ump_ss_id')
            ]);
            $this->Validation->checkUnique('ump_cp_id', 'user_mapping', [
                'ump_id' => $this->getIntParameter('ump_id')
            ], [
                'ump_ss_id' => $this->getIntParameter('ump_ss_id')
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

        $userField = $this->Field->getSingleSelect('user', 'ump_user', $this->getStringParameter('ump_user'));
        $userField->setHiddenField('ump_us_id', $this->getIntParameter('ump_us_id'));
        $userField->setEnableNewButton(false);
        $userField->setEnableDetailButton(false);
        $userField->addParameter('us_confirm', 'Y');

        $ssField = $this->Field->getSingleSelect('systemSetting', 'ump_system', $this->getStringParameter('ump_system'));
        $ssField->setHiddenField('ump_ss_id', $this->getIntParameter('ump_ss_id'));
        $ssField->setEnableDetailButton(false);
        $ssField->setEnableNewButton(false);
        $ssField->addClearField('ump_relation');
        $ssField->addClearField('ump_rel_id');
        $ssField->addClearField('ump_contact');
        $ssField->addClearField('ump_cp_id');
        if ($this->isInsert() === true) {
            $ssField->addClearField('ugd_group');
            $ssField->addClearField('ugd_usg_id');

        }


        $relField = $this->Field->getSingleSelect('relation', 'ump_relation', $this->getStringParameter('ump_relation'));
        $relField->setHiddenField('ump_rel_id', $this->getIntParameter('ump_rel_id'));
        $relField->addParameterById('rel_ss_id', 'ump_ss_id', Trans::getWord('systemSetting'));
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);
        $relField->addClearField('ump_contact');
        $relField->addClearField('us_contact');

        $contactField = $this->Field->getSingleSelect('contactPerson', 'ump_contact', $this->getStringParameter('ump_contact'));
        $contactField->setHiddenField('ump_cp_id', $this->getIntParameter('ump_cp_id'));
        $contactField->setDetailReferenceCode('cp_id');
        $contactField->addParameterById('cp_rel_id', 'ump_rel_id', Trans::getWord('relation'));

        if ($this->isUpdate() === true) {
            $userField->setReadOnly();
            $ssField->setReadOnly();
            $relField->setReadOnly();
            $contactField->setReadOnly();
        }

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $fieldSet->addField(Trans::getWord('user'), $userField, true);
        $fieldSet->addField(Trans::getWord('systemSetting'), $ssField, true);
        $fieldSet->addField(Trans::getWord('relation'), $relField, true);
        $fieldSet->addField(Trans::getWord('contactPerson'), $contactField, true);
        if ($this->isInsert() === true) {
            # Create User group page
            $userGroupField = $this->Field->getSingleSelect('userGroup', 'ugd_group', $this->getStringParameter('ugd_group'));
            $userGroupField->setHiddenField('ugd_usg_id', $this->getIntParameter('ugd_usg_id'));
            $userGroupField->setEnableNewButton(false);
            $userGroupField->setEnableDetailButton(false);
            $userGroupField->addParameterById('usg_ss_id', 'ump_ss_id', Trans::getWord('systemSetting'));
            $fieldSet->addField(Trans::getWord('userAccess'), $userGroupField, true);
        } else {
            $fieldSet->addField(Trans::getWord('default'), $this->Field->getYesNo('ump_default', $this->getStringParameter('ump_default')));
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('ump_active', $this->getStringParameter('ump_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('UmpGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the page Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getUserGroupFieldSet(): Portlet
    {
        # Create a table.
        $pageTable = new TableDatas('UmpUgdTbl');
        $pageTable->setHeaderRow([
            'ugd_id' => '',
            'usg_id' => '',
            'ugd_group' => Trans::getWord('userGroup'),
            'ugd_active' => Trans::getWord('active'),
        ]);
        $pageTable->setRowsPerPage(30);

        $data = $this->loadUserGroupData();
        $countResult = \count($data);
        for ($i = 0; $i < $countResult; $i++) {
            $data[$i]['ugd_id'] = $this->Field->getHidden('ugd_id[' . $i . ']', $data[$i]['ugd_id']);
            $data[$i]['usg_id'] = $this->Field->getHidden('usg_id[' . $i . ']', $data[$i]['usg_id']);

            $checked = false;
            if ($data[$i]['ugd_active'] === 'Y') {
                $checked = true;
                $pageTable->addCellAttribute('ugd_active', $i, 'class', 'bg-green');
            }
            $check = $this->Field->getCheckBox('ugd_active[' . $i . ']', 'Y', $checked);
            $data[$i]['ugd_active'] = $check;
        }
        $pageTable->addRows($data);
        # Add special settings to the table
        $pageTable->addColumnAttribute('ugd_active', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('UmpUgdPtl', Trans::getWord('userGroup'));
        $portlet->addTable($pageTable);
        return $portlet;
    }

    /**
     * Function to load the user group data.
     *
     * @return array
     */
    protected function loadUserGroupData(): array
    {
        $wheres = [];
        $wheres[] = '((usg.usg_ss_id IS NULL) OR (usg.usg_ss_id = ' . $this->getIntParameter('ump_ss_id') . '))';
        $wheres[] = '(usg.usg_deleted_on IS NULL)';
        $wheres[] = "(usg.usg_active = 'Y')";
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = "SELECT usg.usg_id, usg.usg_name as ugd_group, ugd.ugd_id,
                        (CASE WHEN ugd.ugd_active IS NULL THEN 'N' ELSE ugd.ugd_active END) as ugd_active
                    FROM user_group as usg LEFT OUTER JOIN
                    (SELECT ugd_id, ugd_usg_id, (CASE WHEN ugd_deleted_on IS NULL THEN 'Y' ELSE 'N' END) as ugd_active
                        FROM user_group_detail
                        WHERE (ugd_ump_id = " . $this->getDetailReferenceValue() . ")) as ugd ON usg.usg_id = ugd.ugd_usg_id " . $strWheres;
        $query .= ' ORDER BY ugd_active DESC, usg.usg_name, usg.usg_id';
        $sqlResults = DB::select($query);
        $resutls = DataParser::arrayObjectToArray($sqlResults);
        return $resutls;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        $this->setEnableCloseButton(false);
        parent::loadDefaultButton();
        if ($this->isPopupLayout() === true) {
            $btnClose = new Button('btnClose', Trans::getWord('close'), 'button');
            $btnClose->setIcon(Icon::Close)->btnDanger()->pullRight()->btnMedium();
            $btnClose->addAttribute('onclick', 'App.closeWindow()');
            $this->View->addButton($btnClose);
        } else {
            if ($this->isValidParameter('ump_us_id') === true) {
                $btnClose = new HyperLink('hplClose', Trans::getWord('cancel'), url('user/detail?us_id=' . $this->getIntParameter('ump_us_id')));
            } else {
                $btnClose = new HyperLink('hplClose', Trans::getWord('cancel'), url('user'));
            }
            $btnClose->viewAsButton();
            $btnClose->setIcon(Icon::MailReply)->btnDanger()->pullRight()->btnMedium();
            $this->View->addButton($btnClose);
        }
    }
}
