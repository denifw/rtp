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
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\SubmitButton;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Setting\DashboardDao;
use App\Model\Dao\Setting\DashboardDetailDao;
use App\Model\Dao\System\Access\UserGroupDetailDao;
use App\Model\Dao\System\Access\UserMappingDao;
use App\Model\Dao\System\Access\UsersDao;
use App\Model\Dao\System\Access\UserTokenDao;
use App\Model\Mail\EmailConfirmation;
use Illuminate\Support\Facades\Mail;

/**
 * Class to handle the creation of detail User page
 *
 * @package    app
 * @subpackage Model\Detail\System\Access
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class User extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'us', 'us_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $usColVal = [
            'us_name' => $this->getStringParameter('us_name'),
            'us_username' => $this->getStringParameter('us_username'),
            'us_password' => md5($this->getStringParameter('us_username')),
            'us_confirm' => 'N',
            'us_system' => 'N',
            'us_lg_id' => $this->getStringParameter('us_lg_id'),
            'us_menu_style' => $this->getStringParameter('us_menu_style'),
            'us_active' => 'Y',
        ];
        $usDao = new UsersDao();
        $usDao->doInsertTransaction($usColVal);
        $userId = $usDao->getLastInsertId();

        $umpColVal = [
            'ump_us_id' => $userId,
            'ump_ss_id' => $this->getStringParameter('ump_ss_id'),
            'ump_rel_id' => $this->getStringParameter('ump_rel_id'),
            'ump_cp_id' => $this->getStringParameter('ump_cp_id'),
            'ump_confirm' => 'N',
            'ump_default' => 'Y',
            'ump_active' => 'Y',
        ];
        $umpDao = new UserMappingDao();
        $umpDao->doInsertTransaction($umpColVal);

        $ugdColVal = [
            'ugd_usg_id' => $this->getStringParameter('ugd_usg_id'),
            'ugd_ump_id' => $umpDao->getLastInsertId(),
        ];
        $ugdDao = new UserGroupDetailDao();
        $ugdDao->doInsertTransaction($ugdColVal);

        # Create user token for email confirmation user.
        $utDao = new UserTokenDao();
        $token = $utDao->generateTokenByUserAndSystem($userId, $this->getStringParameter('ump_ss_id'), 'EMAIL_CONFIRMATION');
        $expiredDate = $utDao->getExpiredDate('EMAIL_CONFIRMATION');
        $utColVal = [
            'ut_us_id' => $usDao->getLastInsertId(),
            'ut_ss_id' => $this->getStringParameter('ump_ss_id'),
            'ut_token' => $token,
            'ut_type' => 'EMAIL_CONFIRMATION',
            'ut_expired_on' => $expiredDate
        ];
        $utDao->doInsertTransaction($utColVal);
        # Send email confirmation.
        $usColVal['expired_date'] = $expiredDate;
        Mail::to($usColVal['us_username'])->send(new EmailConfirmation($usColVal, $token));
        # Check if the email fail.
        $fails = Mail::failures();
        if (empty($fails) === false) {
            $this->addErrorMessage(Trans::getWord('successAddUserAndFailSendingEmail', 'message'));
        } else {
            $this->addSuccessMessage(Trans::getWord('successAddUserAndWaitingConfirmation', 'message'));
        }
        return $userId;
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $usColVal = [
                'us_name' => $this->getStringParameter('us_name'),
                'us_username' => $this->getStringParameter('us_username'),
                'us_allow_mail' => $this->getStringParameter('us_allow_mail'),
                'us_lg_id' => $this->getStringParameter('us_lg_id'),
                'us_menu_style' => $this->getStringParameter('us_menu_style'),
                'us_active' => $this->getStringParameter('us_active'),
            ];
            $usDao = new UsersDao();
            $usDao->doUpdateTransaction($this->getDetailReferenceValue(), $usColVal);
        } else if ($this->getFormAction() === 'doSendEmailConfirmation') {
            $userTokenDao = new UserTokenDao();
            $userToken = $userTokenDao->getUserTokenByType($this->getDetailReferenceValue(), $this->getStringParameter('ump_ss_id'), 'EMAIL_CONFIRMATION');
            if (empty($userToken) === false) {
                $userTokenDao->doDeleteTransaction($userToken['ut_id']);
            }
            $token = $userTokenDao->generateTokenByUserAndSystem($this->getDetailReferenceValue(), $this->getStringParameter('ump_ss_id'), 'EMAIL_CONFIRMATION');
            $userToken = [
                'ut_us_id' => $this->getDetailReferenceValue(),
                'ut_ss_id' => $this->getStringParameter('ump_ss_id'),
                'ut_token' => $token,
                'ut_type' => 'EMAIL_CONFIRMATION',
                'ut_expired_on' => $userTokenDao->getExpiredDate('EMAIL_CONFIRMATION')
            ];
            $userTokenDao->doInsertTransaction($userToken);

            $user = [
                'us_name' => $this->getStringParameter('us_name'),
                'us_username' => $this->getStringParameter('us_username'),
                'expired_date' => $userToken['ut_expired_on'],
            ];

            Mail::to($this->getStringParameter('us_username'))->send(new EmailConfirmation($user, $userToken['ut_token']));
            # Check if the email fail.
            $fails = Mail::failures();
            if (empty($fails) === false) {
                $this->addErrorMessage(Trans::getWord('successAddUserAndFailSendingEmail', 'message'));
            } else {
                $this->addSuccessMessage(Trans::getWord('successAddUserAndWaitingConfirmation', 'message'));
            }
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
        $this->Tab->addPortlet('general', $this->getUserPortlet());
        if ($this->isInsert() === true) {
            $this->Tab->addPortlet('general', $this->getInsertMappingPortlet());
        } elseif ($this->getStringParameter('us_confirm') === 'N') {
            $wheres = [];
            $wheres[] = SqlHelper::generateStringCondition('ump_us_id', $this->getDetailReferenceValue());
            $ump = UserMappingDao::loadData($wheres);
            if (count($ump) === 1) {
                $this->setParameters($ump[0]);
            }
            $this->addWarningMessage('Warning!!!, ' . Trans::getWord('userWithoutEmailConfirmation', 'message'));
            $btn = new SubmitButton('btnReminder', Trans::getWord('sendReminder'), 'doSendEmailConfirmation', $this->getMainFormId());
            $btn->setIcon(Icon::ShareSquare)->btnPrimary()->pullRight();
            $this->View->addButton($btn);
            $this->Tab->addContent('general', $this->getHiddenMappingFieldSet()->createFieldSet());
        } else {
//            $this->Tab->addPortlet('general', $this->getMappingFieldSet());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('us_name', 3, 255);
        $this->Validation->checkRequire('us_username', 3, 255);
        $this->Validation->checkEmail('us_username');
        $this->Validation->checkUnique('us_username', 'users', [
            'us_id' => $this->getDetailReferenceValue()
        ]);
        if ($this->isInsert() === true) {
            $this->Validation->checkRequire('ugd_usg_id');
            $this->Validation->checkRequire('ump_rel_id');
            $this->Validation->checkRequire('ump_ss_id');
            $this->Validation->checkRequire('ump_cp_id');
            $this->Validation->checkUnique('ump_cp_id', 'user_mapping', [
                'ump_id' => $this->getStringParameter('ump_id')
            ], [
                'ump_ss_id' => $this->getStringParameter('ump_ss_id')
            ]);
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getUserPortlet(): Portlet
    {
        # create language field.
        $lgField = $this->Field->getSingleSelect('lg', 'us_language', $this->getStringParameter('us_language'));
        $lgField->setHiddenField('us_lg_id', $this->getStringParameter('us_lg_id'));
        $lgField->setEnableNewButton(false);
        # Create Menu Field
        $menuField = $this->Field->getSelect('us_menu_style', $this->getStringParameter('us_menu_style'));
        $menuField->addOption(Trans::getWord('normal'), 'nav-md');
        $menuField->addOption(Trans::getWord('small'), 'nav-sm');

        $usernameField = $this->Field->getText('us_username', $this->getStringParameter('us_username'));
        if ($this->isUpdate() === true) {
            $usernameField->setReadOnly();
        }

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);

        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('us_name', $this->getStringParameter('us_name')), true);
        $fieldSet->addField(Trans::getWord('email'), $usernameField, true);
        $fieldSet->addField(Trans::getWord('language'), $lgField, true);
        $fieldSet->addField(Trans::getWord('menuStyle'), $menuField, true);
        # Create portlet
        $portlet = new Portlet('UsGeneralPtl', Trans::getWord('user'));
        if ($this->isUpdate() === true) {
            $fieldSet->setGridDimension(6, 6);
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('us_active', $this->getStringParameter('us_active')));
        } else {
            $fieldSet->setGridDimension(6, 12, 12);
            $portlet->setGridDimension(6, 6, 12);
        }

        # Create a portlet box.
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getInsertMappingPortlet(): Portlet
    {
        # Create Fields.

        $ssField = $this->Field->getSingleSelect('ss', 'ump_system', $this->getStringParameter('ump_system'));
        $ssField->setHiddenField('ump_ss_id', $this->getStringParameter('ump_ss_id'));
        $ssField->setEnableDetailButton(false);
        $ssField->setEnableNewButton(false);
        $ssField->addClearField('ump_relation');
        $ssField->addClearField('ump_rel_id');
        $ssField->addClearField('ump_contact');
        $ssField->addClearField('ump_cp_id');
        $ssField->addClearField('ugd_group');
        $ssField->addClearField('ugd_usg_id');

        $relField = $this->Field->getSingleSelect('rel', 'ump_relation', $this->getStringParameter('ump_relation'));
        $relField->setHiddenField('ump_rel_id', $this->getStringParameter('ump_rel_id'));
        $relField->addParameterById('rel_ss_id', 'ump_ss_id', Trans::getWord('systemName'));
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);
        $relField->addClearField('ump_contact');
        $relField->addClearField('us_contact');

        $contactField = $this->Field->getSingleSelect('cp', 'ump_contact', $this->getStringParameter('ump_contact'), 'loadNotUserData');
        $contactField->setHiddenField('ump_cp_id', $this->getStringParameter('ump_cp_id'));
        $contactField->setDetailReferenceCode('cp_id');
        $contactField->addParameterById('cp_rel_id', 'ump_rel_id', Trans::getWord('relation'));
        $contactField->addParameterById('ump_ss_id', 'ump_ss_id', Trans::getWord('systemName'));

        # Create User group page
        $userGroupField = $this->Field->getSingleSelect('usg', 'ugd_group', $this->getStringParameter('ugd_group'));
        $userGroupField->setHiddenField('ugd_usg_id', $this->getStringParameter('ugd_usg_id'));
        $userGroupField->addParameterById('usg_ss_id', 'ump_ss_id', Trans::getWord('systemName'));
        $userGroupField->setEnableNewButton(false);
        $userGroupField->setEnableDetailButton(false);

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);

        $fieldSet->addField(Trans::getWord('systemName'), $ssField, true);
        $fieldSet->addField(Trans::getWord('relation'), $relField, true);
        $fieldSet->addField(Trans::getWord('contactPerson'), $contactField, true);
        $fieldSet->addField(Trans::getWord('userAccess'), $userGroupField, true);

        # Create a portlet box.
        $portlet = new Portlet('UsMappingPtl', Trans::getWord('userMapping'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6, 12);

        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\FieldSet
     */
    private function getHiddenMappingFieldSet(): FieldSet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->addHiddenField($this->Field->getHidden('ump_ss_id', $this->getIntParameter('ump_ss_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('ump_rel_id', $this->getIntParameter('ump_rel_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('ump_cp_id', $this->getIntParameter('ump_cp_id')));

        return $fieldSet;
    }


    /**
     * Function to get the page Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getMappingFieldSet(): Portlet
    {
        # Create a table.
        $pageTable = new Table('UsUmpTbl');
        $pageTable->setHeaderRow([
            'ump_system' => Trans::getWord('relation'),
            'ump_office' => Trans::getWord('office'),
            'ump_contact' => Trans::getWord('contactPerson'),
            'ump_confirm' => Trans::getWord('verified'),
            'ump_default' => Trans::getWord('default'),
            'ump_active' => Trans::getWord('active'),
        ]);

        $data = UserMappingDao::loadData(['(ump_us_id = ' . $this->getDetailReferenceValue() . ')']);
        $pageTable->addRows($data);
        # Add special settings to the table
        $pageTable->setColumnType('ump_confirm', 'yesno');
        $pageTable->setColumnType('ump_default', 'yesno');
        $pageTable->setColumnType('ump_active', 'yesno');
        $pageTable->setUpdateActionByHyperlink('userMapping/detail', ['ump_id']);
        # Create a portlet box.
        $portlet = new Portlet('UsUmpPtl', Trans::getWord('mapping'));
        $portlet->addTable($pageTable);
        $btn = new HyperLink('NewUmpBtn', Trans::getWord('new'), url('userMapping/detail'));
        $btn->viewAsButton();
        $btn->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btn);

        return $portlet;
    }

    /**
     * Function to insert dashboard user
     *
     * @param int $usId The id user.
     * @return void
     */
    private function doInsertDashboard(int $usId): void
    {
        $wheres[] = '(ugds.ugds_usg_id = ' . $this->getIntParameter('ugd_usg_id') . ')';
        $wheres[] = '(ugds.ugds_deleted_on IS NULL)';
        $orders[] = 'sty.sty_name';
        $orders[] = 'dsi.dsi_order';
        $ugdsData = UserGroupDashboardItemDao::loadData($wheres, $orders);
        $moduleIds = [];
        $orderDsh = 1;
        foreach ($ugdsData as $data) {
            if (array_key_exists($data['ugds_module_id'], $moduleIds) === false) {
                # Create Dashboard
                $dashboardName = $data['ugds_module_name'];
                if (empty($data['ugds_module_name']) === true) {
                    $dashboardName = Trans::getWord('general');
                }
                $colDsh = [
                    'dsh_ss_id' => $this->getIntParameter('ump_ss_id'),
                    'dsh_us_id' => $usId,
                    'dsh_name' => $dashboardName,
                    'dsh_description' => $dashboardName,
                    'dsh_order' => $orderDsh,
                ];
                $dshDao = new DashboardDao();
                $dshDao->doInsertTransaction($colDsh);
                $dshId = $dshDao->getLastInsertId();
                $moduleIds[$data['ugds_module_id']] = $dshId;
                $orderDsh++;
            } else {
                $dshId = $moduleIds[$data['ugds_module_id']];
            }
            $colDsd = [
                'dsd_dsh_id' => $dshId,
                'dsd_dsi_id' => $data['dsi_id'],
                'dsd_title' => $data['dsi_title'],
                'dsd_grid_large' => $data['dsi_grid_large'],
                'dsd_grid_medium' => $data['dsi_grid_medium'],
                'dsd_grid_small' => $data['dsi_grid_small'],
                'dsd_grid_xsmall' => $data['dsi_grid_xsmall'],
                'dsd_height' => $data['dsi_height'],
                'dsd_color' => $data['dsi_color'],
                'dsd_order' => $data['dsi_order'],
                'dsd_parameter' => $data['dsi_parameter'],
            ];
            $dsdDao = new DashboardDetailDao();
            $dsdDao->doInsertTransaction($colDsd);
        }
    }

}
