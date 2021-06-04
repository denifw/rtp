<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\System\Page;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\Page\PageDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\System\Page\PageRightDao;

/**
 * Class to handle the creation of detail Page page
 *
 * @package    app
 * @subpackage Model\Detail\Page
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Page extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'pg', 'pg_id');
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
            'pg_title' => $this->getStringParameter('pg_title'),
            'pg_description' => $this->getStringParameter('pg_description'),
            'pg_route' => $this->getStringParameter('pg_route'),
            'pg_mn_id' => $this->getStringParameter('pg_mn_id'),
            'pg_pc_id' => $this->getStringParameter('pg_pc_id'),
            'pg_icon' => $this->getStringParameter('pg_icon'),
            'pg_order' => $this->getStringParameter('pg_order'),
            'pg_default' => $this->getStringParameter('pg_default', 'N'),
            'pg_system' => $this->getStringParameter('pg_system', 'N'),
            'pg_active' => $this->getStringParameter('pg_active', 'Y')

        ];
        $pgDao = new PageDao();
        $pgDao->doInsertTransaction($colVal);
        $lastInsertId = $pgDao->getLastInsertId();
        if (empty($lastInsertId) === false && $this->getStringParameter('pg_system', 'N') !== 'Y') {
            $rights = $this->loadDefaultRightsForNewPage($lastInsertId);
            $prDao = new PageRightDao();
            foreach ($rights as $right) {
                $prDao->doInsertTransaction($right);
            }
        }

        return $lastInsertId;
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateRight') {
            $prColVal = [
                'pr_name' => $this->getStringParameter('pr_name'),
                'pr_description' => $this->getStringParameter('pr_description'),
                'pr_pg_id' => $this->getDetailReferenceValue(),
                'pr_default' => $this->getStringParameter('pr_default', 'N'),
                'pr_active' => $this->getStringParameter('pr_active', 'Y'),
            ];
            $prDao = new PageRightDao();
            if ($this->isValidParameter('pr_id') === true) {
                $prDao->doUpdateTransaction($this->getIntParameter('pr_id'), $prColVal);
            } else {
                $prDao->doInsertTransaction($prColVal);
            }
        } else {

            $colVal = [
                'pg_title' => $this->getStringParameter('pg_title'),
                'pg_description' => $this->getStringParameter('pg_description'),
                'pg_route' => $this->getStringParameter('pg_route'),
                'pg_mn_id' => $this->getStringParameter('pg_mn_id'),
                'pg_pc_id' => $this->getStringParameter('pg_pc_id'),
                'pg_icon' => $this->getStringParameter('pg_icon'),
                'pg_order' => $this->getIntParameter('pg_order'),
                'pg_default' => $this->getStringParameter('pg_default', 'N'),
                'pg_system' => $this->getStringParameter('pg_system', 'N'),
                'pg_active' => $this->getStringParameter('pg_active', 'Y')

            ];
            $pgDao = new PageDao();
            $pgDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return PageDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        if ($this->isUpdate() === true && $this->getStringParameter('pg_system') === 'N') {
            $this->Tab->addPortlet('rights', $this->getRightFieldSet());
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateRight') {
            $this->Validation->checkRequire('pr_name', 3, 125);
            $this->Validation->checkRequire('pr_description', 3, 255);
            $this->Validation->checkUnique('pr_name', 'page_right', [
                'pr_id' => $this->getStringParameter('pr_id', 0)
            ], [
                'pr_pg_id' => $this->getDetailReferenceValue()
            ]);
        } else {
            $this->Validation->checkRequire('pg_title', 2, 125);
            $this->Validation->checkRequire('pg_description', 2, 255);
            $this->Validation->checkRequire('pg_route', 2, 125);
            $this->Validation->checkRequire('pg_pc_id');
            $this->Validation->checkRequire('pc_code');
            if ($this->isValidParameter('pg_mn_id') === true) {
                $this->Validation->checkRequire('pg_order');
                $this->Validation->checkInt('pg_order', 1);
            }
            $this->Validation->checkUnique('pg_route', 'page', [
                'pg_id' => $this->getDetailReferenceValue()
            ], [
                'pg_pc_id' => $this->getStringParameter('pg_pc_id')
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
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create custom field.
        $menuField = $this->Field->getSingleSelect('mn', 'mn_name', $this->getStringParameter('mn_name'));
        $menuField->setHiddenField('pg_mn_id', $this->getStringParameter('pg_mn_id'));
        $menuField->setDetailReferenceCode('mn_id');
        # Page Category Field.
        # Create custom field.
        $categoryField = $this->Field->getSingleSelect('pc', 'pc_name', $this->getStringParameter('pc_name'));
        $categoryField->setHiddenField('pg_pc_id', $this->getStringParameter('pg_pc_id'));
        $categoryField->setDetailReferenceCode('pc_id');
        $categoryField->setAutoCompleteFields([
            'pc_code' => 'pc_code'
        ]);
        # add field.
        $fieldSet->addField(Trans::getWord('title'), $this->Field->getText('pg_title', $this->getStringParameter('pg_title')), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('pg_description', $this->getStringParameter('pg_description')), true);
        $fieldSet->addField(Trans::getWord('category'), $categoryField, true);
        $fieldSet->addField(Trans::getWord('menu'), $menuField);
        $fieldSet->addField(Trans::getWord('route'), $this->Field->getText('pg_route', $this->getStringParameter('pg_route')), true);
        $fieldSet->addField(Trans::getWord('sortNumber'), $this->Field->getText('pg_order', $this->getStringParameter('pg_order')));
        $fieldSet->addField(Trans::getWord('icon'), $this->Field->getText('pg_icon', $this->getStringParameter('pg_icon')));
        $fieldSet->addField(Trans::getWord('default'), $this->Field->getYesNo('pg_default', $this->getStringParameter('pg_default')));
        $fieldSet->addField(Trans::getWord('system'), $this->Field->getYesNo('pg_system', $this->getStringParameter('pg_system')));
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('pg_active', $this->getStringParameter('pg_active')));
        }
        $fieldSet->addHiddenField($this->Field->getHidden('pc_code', $this->getStringParameter('pc_code')));

        # Create a portlet box.
        $portlet = new Portlet('pgGeneralPortlet', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }


    /**
     * Function to get the right Field Set.
     *
     * @return Portlet
     */
    private function getRightFieldSet(): Portlet
    {
        # load Right modal
        $prModal = $this->loadRightModal();
        $this->View->addModal($prModal);

        # Create Table
        $rightTable = new Table('pgRightTable');
        $rightTable->setHeaderRow([
            'pr_name' => Trans::getWord('rights'),
            'pr_description' => Trans::getWord('description'),
            'pr_default' => Trans::getWord('default'),
            'pr_active' => Trans::getWord('active'),
        ]);
        $rightData = PageRightDao::getByPageId($this->getDetailReferenceValue());
        $rightTable->addRows($rightData);
        # Add special settings to the table
        $rightTable->setUpdateActionByModal($prModal, 'pageRight', 'getByIdForModal', ['pr_id']);
        $rightTable->setColumnType('pr_default', 'yesno');
        $rightTable->setColumnType('pr_active', 'yesno');
        # Create a portlet box.
        $portlet = new Portlet('pgRightPortlet', Trans::getWord('rights'));
        $portlet->addTable($rightTable);
        # Add button new right into the portlet
        $btnRightMdl = new ModalButton('btnPrMdl', Trans::getWord('addRights'), $prModal->getModalId());
        $btnRightMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
        $portlet->addButton($btnRightMdl);

        return $portlet;
    }


    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function loadRightModal(): Modal
    {
        $modal = new Modal('PgPrMdl', Trans::getWord('rights'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateRight');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateRight' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('pr_name', $this->getParameterForModal('pr_name', $showModal)), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('pr_description', $this->getParameterForModal('pr_description', $showModal)), true);
        $fieldSet->addField(Trans::getWord('default'), $this->Field->getYesNo('pr_default', $this->getParameterForModal('pr_default', $showModal)));
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('pr_active', $this->getParameterForModal('pr_active', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('pr_id', $this->getParameterForModal('pr_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to load the default right for new page.
     *
     * @param string $pgId To store the page id.
     *
     * @return array
     */
    private function loadDefaultRightsForNewPage(string $pgId): array
    {
        $pageCategory = $this->getStringParameter('pc_code');
        if ($pageCategory === 'listing') {
            $result = [
                [
                    'pr_pg_id' => $pgId,
                    'pr_name' => 'AllowInsert',
                    'pr_description' => 'Allow user to insert new data.',
                    'pr_default' => 'Y',
                    'pr_active' => 'Y',
                ],
                [
                    'pr_pg_id' => $pgId,
                    'pr_name' => 'AllowUpdate',
                    'pr_description' => 'Allow user to update data.',
                    'pr_default' => 'Y',
                    'pr_active' => 'Y',
                ],
                [
                    'pr_pg_id' => $pgId,
                    'pr_name' => 'AllowExportXls',
                    'pr_description' => 'Allow user to export the data to excel file.',
                    'pr_default' => 'Y',
                    'pr_active' => 'Y',
                ]
            ];
        } elseif ($pageCategory === 'detail') {
            $result = [
                [
                    'pr_pg_id' => $pgId,
                    'pr_name' => 'AllowInsert',
                    'pr_description' => 'Allow user to insert new data.',
                    'pr_default' => 'Y',
                    'pr_active' => 'Y',
                ],
                [
                    'pr_pg_id' => $pgId,
                    'pr_name' => 'AllowUpdate',
                    'pr_description' => 'Allow user to update the data.',
                    'pr_default' => 'Y',
                    'pr_active' => 'Y',
                ],
                [
                    'pr_pg_id' => $pgId,
                    'pr_name' => 'AllowDelete',
                    'pr_description' => 'Allow user to delete the data.',
                    'pr_default' => 'Y',
                    'pr_active' => 'Y',
                ],
            ];
        } elseif ($pageCategory === 'view') {
            $result = [
                [
                    'pr_pg_id' => $pgId,
                    'pr_name' => 'AllowUpdate',
                    'pr_description' => 'Allow user to update the data.',
                    'pr_default' => 'N',
                    'pr_active' => 'Y',
                ]
            ];
        } elseif ($pageCategory === 'statistic') {
            $result = [
                [
                    'pr_pg_id' => $pgId,
                    'pr_name' => 'AllowExportXls',
                    'pr_description' => 'Allow user to export the data to excel file.',
                    'pr_default' => 'Y',
                    'pr_active' => 'Y',
                ]
            ];
        } else {
            $result = [];
        }

        return $result;
    }


}
