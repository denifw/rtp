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

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Table;
use App\Frame\Gui\TableDatas;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\System\Access\UserGroupApiAccessDao;
use App\Model\Dao\System\Access\UserGroupDao;
use App\Model\Dao\System\Access\UserGroupDetailDao;
use App\Model\Dao\System\Access\UserGroupPageDao;
use App\Model\Dao\System\Access\UserGroupRightDao;

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
        parent::__construct(get_class($this), 'usg', 'usg_id');
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
            'usg_ss_id' => $this->getStringParameter('usg_ss_id'),
            'usg_name' => $this->getStringParameter('usg_name'),
            'usg_active' => 'Y',
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
        $colVal = [
            'usg_ss_id' => $this->getStringParameter('usg_ss_id'),
            'usg_name' => $this->getStringParameter('usg_name'),
            'usg_active' => $this->getStringParameter('usg_active'),
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
                        $ugdDao->doUndoDeleteTransaction($value);
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
                        $ugrDao->doUndoDeleteTransaction($value);
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
                        $ugaDao->doUndoDeleteTransaction($value);
                    }
                } elseif (empty($value) === false) {
                    $ugaDao->doDeleteTransaction($value);
                }
            }
        }
        # End Update User Group Api access
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
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('usg_name', 4, 125);
        $this->Validation->checkUnique('usg_name', 'user_group', [
            'usg_id' => $this->getDetailReferenceValue()
        ], [
            'usg_ss_id' => $this->getIntParameter('usg_ss_id')
        ]);
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

        $ssField = $this->Field->getSingleSelect('ss', 'usg_system', $this->getStringParameter('usg_system'));
        $ssField->setHiddenField('usg_ss_id', $this->getStringParameter('usg_ss_id'));
        $ssField->setEnableNewButton(false);

        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('usg_name', $this->getStringParameter('usg_name')), true);
        $fieldSet->addField(Trans::getWord('systemName'), $ssField);
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
        $data = UserGroupDetailDao::loadUserGroupFormData($this->getDetailReferenceValue(), $this->getStringParameter('usg_ss_id'));
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
        $pages = UserGroupPageDao::loadUserGroupFormData($this->getDetailReferenceValue());
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
        $rights = UserGroupRightDao::loadUserGroupFormData($this->getDetailReferenceValue());
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
        $access = UserGroupApiAccessDao::loadUserGroupFormData($this->getDetailReferenceValue());
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
}
