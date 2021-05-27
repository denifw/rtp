<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\User;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Gui\TableDatas;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Setting\DashboardDetailDao;
use App\Model\Dao\System\DashboardItemDao;
use App\Model\Dao\System\SystemSettingDao;
use App\Model\Dao\User\UserGroupApiAccessDao;
use App\Model\Dao\User\UserGroupDao;
use App\Model\Dao\User\UserGroupDashboardItemDao;
use App\Model\Dao\User\UserGroupDetailDao;
use App\Model\Dao\User\UserGroupNotificationDao;
use App\Model\Dao\User\UserGroupPageDao;
use App\Model\Dao\User\UserGroupRightDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of detail UserGroup page
 *
 * @package    app
 * @subpackage Model\Detail\System\Access
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class UserGroup extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'userGroup', 'usg_id');
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
            'usg_ss_id' => $this->getIntParameter('usg_ss_id'),
            'usg_name' => $this->getStringParameter('usg_name'),
            'usg_active' => $this->getStringParameter('usg_active', 'Y'),
        ];
        $usgDao = new UserGroupDao();
        $usgDao->doInsertTransaction($colVal);

        return $usgDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doCopyData') {
            $this->doCopyData();
        } else {
            $colVal = [
                'usg_ss_id' => $this->getIntParameter('usg_ss_id'),
                'usg_name' => $this->getStringParameter('usg_name'),
                'usg_active' => $this->getStringParameter('usg_active', 'Y'),
            ];
            $usgDao = new UserGroupDao();
            $usgDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);

            # Start Update User Group detail
            $umpIds = $this->getArrayParameter('ump_id');
            $ugdIds = $this->getArrayParameter('ugd_id');
            $ugdActives = $this->getArrayParameter('ugd_active');
            if (count($ugdIds) > 0) {
                $ugdDao = new UserGroupDetailDao();
                foreach ($ugdIds as $key => $value) {
                    if (array_key_exists($key, $ugdActives) === true && $ugdActives[$key] === 'Y') {
                        if (empty($value) === true) {
                            $colValRl = [
                                'ugd_usg_id' => $this->getDetailReferenceValue(),
                                'ugd_ump_id' => $umpIds[$key]
                            ];
                            $ugdDao->doInsertTransaction($colValRl);
                        } else {
                            $colValRl = [
                                'ugd_usg_id' => $this->getDetailReferenceValue(),
                                'ugd_ump_id' => $umpIds[$key],
                                'ugd_deleted_on' => null,
                                'ugd_deleted_by' => null
                            ];
                            $ugdDao->doUpdateTransaction($value, $colValRl);
                        }
                    } elseif (empty($value) === false) {
                        $ugdDao->doDeleteTransaction($value);
                    }
                }
            }
            # End Update User Group detail

            # Start Update User Group Page
            $pgIds = $this->getArrayParameter('pg_id');
            $ugpIds = $this->getArrayParameter('ugp_id');
            $ugpActives = $this->getArrayParameter('ugp_active');
            if (count($ugpIds) > 0) {
                $ugpDao = new UserGroupPageDao();
                foreach ($ugpIds as $key => $value) {
                    if (array_key_exists($key, $ugpActives) === true && $ugpActives[$key] === 'Y') {
                        if (empty($value) === true) {
                            $colValRl = [
                                'ugp_usg_id' => $this->getDetailReferenceValue(),
                                'ugp_pg_id' => $pgIds[$key]
                            ];
                            $ugpDao->doInsertTransaction($colValRl);
                        } else {
                            $ugpDao->doUndoDeleteTransaction($value);
                        }
                    } elseif (empty($value) === false) {
                        $ugpDao->doDeleteTransaction($value);
                    }
                }
            }
            # End Update User Group Page
            # Start Update User Group Page Right
            $prIds = $this->getArrayParameter('pr_id');
            $ugrIds = $this->getArrayParameter('ugr_id');
            $ugrActives = $this->getArrayParameter('ugr_active');
            if (count($ugrIds) > 0) {
                $ugrDao = new UserGroupRightDao();
                foreach ($ugrIds as $key => $value) {
                    if (array_key_exists($key, $ugrActives) === true && $ugrActives[$key] === 'Y') {
                        if (empty($value) === true) {
                            $colValRl = [
                                'ugr_usg_id' => $this->getDetailReferenceValue(),
                                'ugr_pr_id' => $prIds[$key]
                            ];
                            $ugrDao->doInsertTransaction($colValRl);
                        } else {
                            $colValRl = [
                                'ugr_usg_id' => $this->getDetailReferenceValue(),
                                'ugr_pr_id' => $prIds[$key],
                                'ugr_deleted_on' => null,
                                'ugr_deleted_by' => null
                            ];
                            $ugrDao->doUpdateTransaction($value, $colValRl);
                        }
                    } elseif (empty($value) === false) {
                        $ugrDao->doDeleteTransaction($value);
                    }
                }
            }
            # End Update User Group Page Right

            # Start Update User Group Api access
            $aaIds = $this->getArrayParameter('aa_id');
            $ugaIds = $this->getArrayParameter('uga_id');
            $ugaActives = $this->getArrayParameter('uga_active');
            if (count($ugaIds) > 0) {
                $ugaDao = new UserGroupApiAccessDao();
                foreach ($ugaIds as $key => $value) {
                    if (array_key_exists($key, $ugaActives) === true && $ugaActives[$key] === 'Y') {
                        if (empty($value) === true) {
                            $colValUga = [
                                'uga_usg_id' => $this->getDetailReferenceValue(),
                                'uga_aa_id' => $aaIds[$key]
                            ];
                            $ugaDao->doInsertTransaction($colValUga);
                        } else {
                            $colValUga = [
                                'uga_usg_id' => $this->getDetailReferenceValue(),
                                'uga_aa_id' => $aaIds[$key],
                                'uga_deleted_on' => null,
                                'uga_deleted_by' => null
                            ];
                            $ugaDao->doUpdateTransaction($value, $colValUga);
                        }
                    } elseif (empty($value) === false) {
                        $ugaDao->doDeleteTransaction($value);
                    }
                }
            }
            # End Update User Group Page Right
            # Start Update User Group Dashboard
            $dsiIds = $this->getArrayParameter('dsi_id');
            $ugdsIds = $this->getArrayParameter('ugds_id');
            $ugdsActives = $this->getArrayParameter('ugds_active');
            $dsiModuleIds = $this->getArrayParameter('dsi_module_id');
            if (count($ugdsIds) > 0) {
                $ugdsDao = new UserGroupDashboardItemDao();
                foreach ($ugdsIds as $key => $value) {
                    if (array_key_exists($key, $ugdsActives) === true && $ugdsActives[$key] === 'Y') {
                        if (empty($value) === true) {
                            $colValRl = [
                                'ugds_usg_id' => $this->getDetailReferenceValue(),
                                'ugds_dsi_id' => $dsiIds[$key]
                            ];
                            $ugdsDao->doInsertTransaction($colValRl);
                            # Add dashboard
                            $this->doInsertDashboard($dsiIds[$key], $dsiModuleIds[$key]);
                        } else {
                            $ugdsDao->doUndoDeleteTransaction($value);
                        }
                    } elseif (empty($value) === false) {
                        $ugdsDao->doDeleteTransaction($value);
                    }
                }
            }
            # End Update User Group Dashboard
            # Start Update User Group Notification
            $ntIds = $this->getArrayParameter('nt_id');
            $ugnIds = $this->getArrayParameter('ugn_id');
            $ugnActives = $this->getArrayParameter('ugn_active');
            if (count($ugnIds) > 0) {
                $ugnDao = new UserGroupNotificationDao();
                foreach ($ugnIds as $key => $value) {
                    if (array_key_exists($key, $ugnActives) === true && $ugnActives[$key] === 'Y') {
                        if (empty($value) === true) {
                            $colValUgn = [
                                'ugn_usg_id' => $this->getDetailReferenceValue(),
                                'ugn_nt_id' => $ntIds[$key]
                            ];
                            $ugnDao->doInsertTransaction($colValUgn);
                        } else {
                            $colValUgn = [
                                'ugn_usg_id' => $this->getDetailReferenceValue(),
                                'ugn_nt_id' => $ntIds[$key],
                                'ugn_deleted_on' => null,
                                'ugn_deleted_by' => null
                            ];
                            $ugnDao->doUpdateTransaction($value, $colValUgn);
                        }
                    } elseif (empty($value) === false) {
                        $ugnDao->doDeleteTransaction($value);
                    }
                }
            }
            # End Update User Group Notification
        }

    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    private function doCopyData(): void
    {
        $usgDao = new UserGroupDao();
        $ugpDao = new UserGroupPageDao();
        $ugrDao = new UserGroupRightDao();
        $ugaDao = new UserGroupApiAccessDao();
        $ugdsDao = new UserGroupDashboardItemDao();
        $ugnDao = new UserGroupNotificationDao();

        $colVal = [
            'usg_ss_id' => $this->getIntParameter('usg_ss_id_cp'),
            'usg_name' => $this->getStringParameter('usg_name_cp'),
            'usg_active' => 'Y',
        ];
        $pageData = UserGroupPageDao::getByUserGroup($this->getDetailReferenceValue());
        $rightData = UserGroupRightDao::getByUserGroup($this->getDetailReferenceValue());
        $apiData = UserGroupApiAccessDao::getByUserGroup($this->getDetailReferenceValue());
        $notificationData = UserGroupNotificationDao::getByUserGroup($this->getDetailReferenceValue());
        $dashboardData = UserGroupDashboardItemDao::getByUserGroup($this->getDetailReferenceValue());
        $usgDao->doInsertTransaction($colVal);
        $usgId = $usgDao->getLastInsertId();
        # Insert User Group Page
        foreach ($pageData as $row) {
            $ugpDao->doInsertTransaction([
                'ugp_usg_id' => $usgId,
                'ugp_pg_id' => $row['ugp_pg_id']
            ]);
        }
        # Insert User Group Right
        foreach ($rightData as $row) {
            $ugrDao->doInsertTransaction([
                'ugr_usg_id' => $usgId,
                'ugr_pr_id' => $row['ugr_pr_id']
            ]);
        }
        # Insert User Group Api
        foreach ($apiData as $row) {
            $ugaDao->doInsertTransaction([
                'uga_usg_id' => $usgId,
                'uga_aa_id' => $row['uga_aa_id']
            ]);
        }
        # Insert User Group Dashboard
        foreach ($dashboardData as $row) {
            $ugdsDao->doInsertTransaction([
                'ugds_usg_id' => $usgId,
                'ugds_dsi_id' => $row['ugds_dsi_id']
            ]);
        }
        # Insert User Group Notification
        foreach ($notificationData as $row) {
            $ugnDao->doInsertTransaction([
                'ugn_usg_id' => $usgId,
                'ugn_nt_id' => $row['ugn_nt_id']
            ]);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return UserGroupDao::getByReference($this->getDetailReferenceValue());
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
            $this->Tab->addPortlet('general', $this->getUserFieldSet());
            $this->Tab->addPortlet('page', $this->getPageFieldSet());
            $this->Tab->addPortlet('rights', $this->getRightFieldSet());
            $this->Tab->addPortlet('apiAccess', $this->getApiFieldSet());
            $this->Tab->addPortlet('notification', $this->getNotificationFieldSet());
            $this->Tab->addPortlet('dashboard', $this->getDashboardFieldSet());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doCopyData') {
            $this->Validation->checkRequire('usg_name_cp', 4, 125);
            $this->Validation->checkRequire('usg_ss_id_cp');
            $this->Validation->checkAdvanceUnique('usg_name_cp', 'user_group', 'usg_name', [
                'usg_id' => $this->getDetailReferenceValue()
            ], [
                'usg_ss_id' => $this->getIntParameter('usg_ss_id_cp')
            ]);
        } else {
            $this->Validation->checkRequire('usg_name', 4, 125);
            $this->Validation->checkUnique('usg_name', 'user_group', [
                'usg_id' => $this->getDetailReferenceValue()
            ], [
                'usg_ss_id' => $this->getIntParameter('usg_ss_id')
            ]);
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(4);
        $ssField = $this->Field->getSelect('usg_ss_id', $this->getIntParameter('usg_ss_id'));
        $ssField->addOptions(SystemSettingDao::loadAllData(), 'ss_relation', 'ss_id');

        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('usg_name', $this->getStringParameter('usg_name')), true);
        $fieldSet->addField(Trans::getWord('systemSetting'), $ssField);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('usg_active', $this->getStringParameter('usg_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('UsgGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }


    /**
     * Function to get the page Field Set.
     *
     * @return Portlet
     */
    private function getPageFieldSet(): Portlet
    {
        # Create a table.
        $pageTable = new TableDatas('UsgPageTbl');
        $pageTable->setHeaderRow([
            'ugp_id' => '',
            'pg_id' => '',
            'pg_title' => Trans::getWord('name'),
            'pg_description' => Trans::getWord('description'),
            'pg_category' => Trans::getWord('category'),
            'pg_default' => Trans::getWord('default'),
            'ugp_active' => Trans::getWord('active'),
        ]);
        $pageTable->setRowsPerPage(30);
        $pages = UserGroupPageDao::loadUserGroupPage($this->getDetailReferenceValue());
        $index = 0;
        $results = [];
        foreach ($pages as $row) {
            $row['ugp_id'] = $this->Field->getHidden('ugp_id[' . $index . ']', $row['ugp_id']);
            $row['pg_id'] = $this->Field->getHidden('pg_id[' . $index . ']', $row['pg_id']);
            $checked = false;
            if ($row['ugp_active'] === 'Y') {
                $checked = true;
                $pageTable->addCellAttribute('ugp_active', $index, 'class', 'bg-green');
            }
            $check = $this->Field->getCheckBox('ugp_active[' . $index . ']', 'Y', $checked);
            if ($row['pg_default'] === 'Y') {
                $check->setReadOnly();
            }
            $row['ugp_active'] = $check;
            $results[] = $row;
            $index++;
        }
        $pageTable->addRows($results);
        # Add special settings to the table
        $pageTable->addColumnAttribute('ugp_active', 'style', 'text-align: center;');
        $pageTable->setColumnType('pg_default', 'yesno');
        # Create a portlet box.
        $portlet = new Portlet('UsgPagePtl', Trans::getWord('pages'));
        $portlet->addTable($pageTable);

        return $portlet;
    }

    /**
     * Function to get the page Field Set.
     *
     * @return Portlet
     */
    private function getUserFieldSet(): Portlet
    {
        # Create a table.
        $pageTable = new TableDatas('UsgDetailTbl');
        $pageTable->setHeaderRow([
            'ugd_id' => '',
            'ump_id' => '',
            'ump_ss_name' => Trans::getWord('system'),
            'ump_rel_name' => Trans::getWord('relation'),
            'ump_us_name' => Trans::getWord('name'),
            'ump_us_username' => Trans::getWord('email'),
            'ugd_active' => Trans::getWord('active'),
        ]);
        $pageTable->setRowsPerPage(30);
        $data = UserGroupPageDao::loadUserGroupDetail($this->getDetailReferenceValue(), $this->getIntParameter('usg_ss_id', 0));
        $index = 0;
        $results = [];
        foreach ($data as $row) {
            $row['ugd_id'] = $this->Field->getHidden('ugd_id[' . $index . ']', $row['ugd_id']);
            $row['ump_id'] = $this->Field->getHidden('ump_id[' . $index . ']', $row['ump_id']);
            $checked = false;
            if ($row['ugd_active'] === 'Y') {
                $checked = true;
                $pageTable->addCellAttribute('ugd_active', $index, 'class', 'bg-green');
            }
            $check = $this->Field->getCheckBox('ugd_active[' . $index . ']', 'Y', $checked);
            $row['ugd_active'] = $check;
            $results[] = $row;
            $index++;
        }
        $pageTable->addRows($results);
        # Add special settings to the table
        $pageTable->addColumnAttribute('ugd_active', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('UsgDetailPtl', Trans::getWord('users'));
        $portlet->addTable($pageTable);

        return $portlet;
    }

    /**
     * Function to get the page right Field Set.
     *
     * @return Portlet
     */
    private function getRightFieldSet(): Portlet
    {
        # Create a table.
        $pageRightTable = new TableDatas('UsgRightTbl');
        $pageRightTable->setHeaderRow([
            'ugr_id' => '',
            'pr_id' => '',
            'pr_page' => Trans::getWord('page'),
            'pr_name' => Trans::getWord('name'),
            'pr_default' => Trans::getWord('default'),
            'ugr_active' => Trans::getWord('active'),
        ]);

        #Add data.
        $rights = UserGroupRightDao::loadUserGroupRight($this->getDetailReferenceValue());
        $index = 0;
        $results = [];
        foreach ($rights as $row) {
            $row['ugr_id'] = $this->Field->getHidden('ugr_id[' . $index . ']', $row['ugr_id']);
            $row['pr_id'] = $this->Field->getHidden('pr_id[' . $index . ']', $row['pr_id']);
            $checked = false;
            if ($row['ugr_active'] === 'Y') {
                $checked = true;
                $pageRightTable->addCellAttribute('ugr_active', $index, 'class', 'bg-green');
            }
            $check = $this->Field->getCheckBox('ugr_active[' . $index . ']', 'Y', $checked);
            # IF right default then set readonly.
            if ($row['pr_default'] === 'Y') {
                $check->setReadOnly();
            }
            $row['ugr_active'] = $check;
            $results[] = $row;
            $index++;
        }
        $pageRightTable->addRows($results);
        $pageRightTable->setRowsPerPage(30);
        # Add special settings to the table
        $pageRightTable->addColumnAttribute('ugr_active', 'style', 'text-align: center;');
        $pageRightTable->setColumnType('pr_default', 'yesno');
        # Create a portlet box.
        $portlet = new Portlet('UsgRightPtl', Trans::getWord('rights'));
        $portlet->addTable($pageRightTable);

        return $portlet;
    }

    /**
     * Function to get the page right Field Set.
     *
     * @return Portlet
     */
    private function getApiFieldSet(): Portlet
    {
        # Create a table.
        $apiAccessTbl = new Table('UsgAaTbl');
        $apiAccessTbl->setHeaderRow([
            'uga_id' => '',
            'aa_id' => '',
            'aa_name' => Trans::getWord('name'),
            'aa_description' => Trans::getWord('description'),
            'aa_default' => Trans::getWord('default'),
            'uga_active' => Trans::getWord('active'),
        ]);

        #Add data.
        $access = UserGroupApiAccessDao::loadUserGroupApiAccess($this->getDetailReferenceValue());
        $index = 0;
        $results = [];
        foreach ($access as $row) {
            $row['uga_id'] = $this->Field->getHidden('uga_id[' . $index . ']', $row['uga_id']);
            $row['aa_id'] = $this->Field->getHidden('aa_id[' . $index . ']', $row['aa_id']);

            $checked = false;
            if ($row['uga_active'] === 'Y') {
                $checked = true;
                $apiAccessTbl->addCellAttribute('uga_active', $index, 'class', 'bg-green');
            }
            $check = $this->Field->getCheckBox('uga_active[' . $index . ']', 'Y', $checked);
            # IF right default then set readonly.
            if ($row['aa_default'] === 'Y') {
                $check->setReadOnly();
            }
            $row['uga_active'] = $check;
            $results[] = $row;
            $index++;
        }
        $apiAccessTbl->addRows($results);
        # Add special settings to the table
        $apiAccessTbl->addColumnAttribute('uga_active', 'style', 'text-align: center;');
        $apiAccessTbl->setColumnType('aa_default', 'yesno');
        # Create a portlet box.
        $portlet = new Portlet('UsgApiPtl', Trans::getWord('apiAccess'));
        $portlet->addTable($apiAccessTbl);

        return $portlet;
    }

    /**
     * Function to get the dashboard Field Set.
     *
     * @return Portlet
     */
    private function getDashboardFieldSet(): Portlet
    {
        # Create a table.
        $dashboardTable = new Table('UsgDashboardTbl');
        $dashboardTable->setHeaderRow([
            'ugds_id' => '',
            'dsi_id' => '',
            'dsi_module_id' => '',
            'dsi_title' => Trans::getWord('title'),
            'dsi_code' => Trans::getWord('code'),
            'dsi_module_name' => Trans::getWord('module'),
            'dsi_path' => Trans::getWord('path'),
            'dsi_description' => Trans::getWord('description'),
            'ugds_active' => Trans::getWord('active'),
        ]);
        # Prepare data.
        $dashboardData = DashboardItemDao::loadUserGroupDashboard($this->getDetailReferenceValue());
        $index = 0;
        $results = [];
        foreach ($dashboardData as $row) {
            $row['ugds_id'] = $this->Field->getHidden('ugds_id[' . $index . ']', $row['ugds_id']);
            $row['dsi_id'] = $this->Field->getHidden('dsi_id[' . $index . ']', $row['dsi_id']);
            $row['dsi_module_id'] = $this->Field->getHidden('dsi_module_id[' . $index . ']', $row['dsi_module_id']);
            $checked = false;
            if ($row['ugds_active'] === 'Y') {
                $checked = true;
                $dashboardTable->addCellAttribute('ugds_active', $index, 'class', 'bg-green');
            }
            $row['ugds_active'] = $this->Field->getCheckBox('ugds_active[' . $index . ']', 'Y', $checked);
            $results[] = $row;
            $index++;
        }
        $dashboardTable->addRows($results);
        # Add special settings to the table
        $dashboardTable->addColumnAttribute('ugds_active', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('UsgDashboardPtl', Trans::getWord('dashboard'));
        $portlet->addTable($dashboardTable);

        return $portlet;
    }

    /**
     * Function to get the notification Field Set.
     *
     * @return Portlet
     */
    private function getNotificationFieldSet(): Portlet
    {
        # Create a table.
        $pageNotificationTable = new Table('UsgNotifTbl');
        $pageNotificationTable->setHeaderRow([
            'ugn_id' => '',
            'nt_id' => '',
            'nt_code' => Trans::getWord('code'),
            'nt_module' => Trans::getWord('module'),
            'nt_description' => Trans::getWord('description'),
            'ugn_active' => Trans::getWord('active'),
        ]);
        # Prepare data.
        $notifications = UserGroupNotificationDao::loadUserGroupNotification($this->getDetailReferenceValue());
        $index = 0;
        $results = [];
        foreach ($notifications as $row) {
            $row['ugn_id'] = $this->Field->getHidden('ugn_id[' . $index . ']', $row['ugn_id']);
            $row['nt_id'] = $this->Field->getHidden('nt_id[' . $index . ']', $row['nt_id']);

            $checked = false;
            if ($row['ugn_active'] === 'Y') {
                $checked = true;
                $pageNotificationTable->addCellAttribute('ugn_active', $index, 'class', 'bg-green');
            }
            $row['ugn_active'] = $this->Field->getCheckBox('ugn_active[' . $index . ']', 'Y', $checked);
            $results[] = $row;
            $index++;
        }
        $pageNotificationTable->addRows($results);
        # Add special settings to the table
        $pageNotificationTable->addColumnAttribute('ugn_active', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('UsgNotificationPtl', Trans::getWord('notification'));
        $portlet->addTable($pageNotificationTable);

        return $portlet;
    }

    /**
     * Function insert dashboard.
     *
     * @param int $dsiId The dashboard item reference.
     * @param int $dsiModule The dashboard item module.
     */
    private function doInsertDashboard(int $dsiId, int $dsiModule): void
    {
        # Check dashboard item exist in user group
        $wheres = [];
        $wheres[] = 'dsi.dsi_id NOT IN (SELECT dsd_dsi_id FROM dashboard_detail)';
        $wheres[] = SqlHelper::generateNumericCondition('dsi.dsi_id', $dsiId);
        $dsiData = DashboardItemDao::getItemNotExistInUserDashboard($wheres);
        if (empty($dsiData) === false) {
            $wheres = [];
            $wheres[] = SqlHelper::generateNumericCondition('usg.usg_id', $this->getDetailReferenceValue());
            $wheres[] = SqlHelper::generateNumericCondition('sty.sty_id', $dsiModule);
            $wheres[] = '(dsh.dsh_deleted_on IS NULL)';
            if ($this->isValidParameter('usg_ss_id')) {
                $wheres[] = SqlHelper::generateNumericCondition('dsh.dsh_ss_id', $this->getIntParameter('usg_ss_id'));
            }
            $strWhere = '';
            if (empty($wheres) === false) {
                $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            }
            # Insert dashboard to user that in the user group detail.
            $query = 'SELECT us.us_id, us.us_name, dsh.dsh_id
                      FROM user_group as usg
                      INNER JOIN user_group_detail as ugd on ugd.ugd_usg_id = usg.usg_id
                      INNER JOIN user_mapping as ump on ump.ump_id = ugd.ugd_ump_id
                      INNER JOIN users as us on us.us_id = ump.ump_us_id
                      INNER JOIN dashboard as dsh on dsh.dsh_us_id = us.us_id
                      INNER JOIN dashboard_detail as dsd on dsd.dsd_dsh_id = dsh.dsh_id
                      INNER JOIN dashboard_item as dsi on dsi.dsi_id = dsd.dsd_dsi_id
                      INNER JOIN system_type as sty on sty.sty_id = dsi.dsi_module_id' . $strWhere . '
                      GROUP BY us.us_id, us.us_name, dsh.dsh_id
                      ORDER BY us.us_id';
            $sqlResults = DB::select($query);
            $results = DataParser::arrayObjectToArray($sqlResults);
            foreach ($results as $row) {
                $colVal = [
                    'dsd_dsh_id' => $row['dsh_id'],
                    'dsd_dsi_id' => $dsiId,
                    'dsd_title' => $dsiData['dsi_title'],
                    'dsd_grid_large' => $dsiData['dsi_grid_large'],
                    'dsd_grid_medium' => $dsiData['dsi_grid_medium'],
                    'dsd_grid_small' => $dsiData['dsi_grid_small'],
                    'dsd_grid_xsmall' => $dsiData['dsi_grid_xsmall'],
                    'dsd_height' => $dsiData['dsi_height'],
                    'dsd_color' => $dsiData['dsi_color'],
                    'dsd_order' => $dsiData['dsi_order'],
                    'dsd_parameter' => $dsiData['dsi_parameter'],
                ];
                $dsdDao = new DashboardDetailDao();
                $dsdDao->doInsertTransaction($colVal);
            }
        }
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        $modalCopy = $this->getBaseCopyModal();
        $this->View->addModal($modalCopy);
        $btnCp = new ModalButton('btnCopy', Trans::getWord('copy'), $modalCopy->getModalId());
        $btnCp->setIcon(Icon::Copy)->btnDark()->pullRight()->btnMedium();
        $this->View->addButton($btnCp);
        parent::loadDefaultButton();
    }


    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    private function getBaseCopyModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('BaseCopyMdl', Trans::getWord('copyData'));
        $modal->setFormSubmit($this->getMainFormId(), 'doCopyData');
        $showModal = false;
        if ($this->getFormAction() === 'doCopyData' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Add field into field set.
        $ssField = $this->Field->getSelect('usg_ss_id_cp', $this->getParameterForModal('usg_ss_id_cp', $showModal));
        $ssField->addOptions(SystemSettingDao::loadAllData(), 'ss_relation', 'ss_id');
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('usg_name_cp', $this->getParameterForModal('usg_name_cp', $showModal)), true);
        $fieldSet->addField(Trans::getWord('systemSetting'), $ssField, true);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

}
