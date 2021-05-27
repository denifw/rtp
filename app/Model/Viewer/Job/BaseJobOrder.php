<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Viewer\Job;


use App\Frame\Document\Excel;
use App\Frame\Document\ExcelTable;
use App\Frame\Document\FileUpload;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractViewerModel;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\System\Notification\JobNotificationBuilder;
use App\Model\Dao\CustomerService\SalesGoodsPositionDao;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\CustomerService\SalesOrderGoodsDao;
use App\Model\Dao\Finance\CashAndBank\CashAdvanceDao;
use App\Model\Dao\Finance\Purchase\JobDepositDao;
use App\Model\Dao\Job\JobActionDao;
use App\Model\Dao\Job\JobActionEventDao;
use App\Model\Dao\Job\JobGoodsDao;
use App\Model\Dao\Job\JobOfficerDao;
use App\Model\Dao\Job\JobNotificationReceiverDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\JobPurchaseDao;
use App\Model\Dao\Job\JobSalesDao;
use App\Model\Dao\Setting\Action\SystemActionEventDao;
use App\Model\Dao\Setting\ServiceTermDocumentDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\Document\DocumentTypeDao;
use App\Model\Dao\User\UserMappingDao;
use App\Model\Viewer\Job\Delivery\Action\DeliveryAction;
use App\Model\Viewer\Job\Inklaring\Action\InklaringAction;
use App\Model\Viewer\Job\Warehouse\Action\InboundAction;
use App\Model\Viewer\Job\Warehouse\Action\OpnameAction;
use App\Model\Viewer\Job\Warehouse\Action\OutboundAction;
use App\Model\Viewer\Job\Warehouse\Action\BundlingAction;
use App\Model\Viewer\Job\Warehouse\Action\StockAdjustmentAction;
use App\Model\Viewer\Job\Warehouse\Action\StockMovementAction;
use App\Model\Viewer\Job\Warehouse\Action\UnBundlingAction;

/**
 * Class to handle the creation of detail BaseJobOrder page
 *
 * @package    app
 * @subpackage Model\Viewer\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class BaseJobOrder extends AbstractViewerModel
{
    /**
     * Property to store the actions of the job.
     *
     * @var AbstractBaseJobAction
     */
    protected $JobAction;

    /**
     * Property to store the actions of the job.
     *
     * @var array $CurrentAction
     */
    protected $CurrentAction = [];

    /**
     * Property to Enable action.
     *
     * @var bool $EnableAction
     */
    protected $EnableAction = true;


    /**
     * Property to store the goods of the job.
     *
     * @var array $Goods
     */
    protected $Goods = [];

    /**
     * Property to store the cash advance data.
     *
     * @var array $CashAdvance
     */
    protected $CashAdvance = [];


    /**
     * Property to store the cash advance data.
     *
     * @var array $JobSales
     */
    protected $JobSales = [];


    /**
     * Property to store the cash advance data.
     *
     * @var array $JobPurchase
     */
    protected $JobPurchase = [];

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdateEvent') {
            $jaeDescription = $this->getStringParameter('jae_description');
            if ($this->isValidParameter('jae_sae_id') === true) {
                $sae = SystemActionEventDao::getByReference($this->getIntParameter('jae_sae_id'));
                $jaeDescription = $sae['sae_description'];
            }
            $jaeColVal = [
                'jae_jac_id' => $this->getIntParameter('jac_id'),
                'jae_sae_id' => $this->getIntParameter('jae_sae_id'),
                'jae_description' => $jaeDescription,
                'jae_remark' => $this->getStringParameter('jae_remark'),
                'jae_date' => $this->getStringParameter('jae_date'),
                'jae_time' => $this->getStringParameter('jae_time'),
                'jae_active' => 'Y',
            ];
            $jaeDao = new JobActionEventDao();
            $jaeDao->doInsertTransaction($jaeColVal);
            $jaeId = $jaeDao->getLastInsertId();
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'jo_jae_id' => $jaeId,
            ]);
            $file = $this->getFileParameter('jae_image');
            $docId = null;
            if ($file !== null) {
                $dct = DocumentTypeDao::getByCode('joborder', 'actionevent');
                $fileName = $jaeDescription;
                if (mb_strlen($jaeDescription) > 20) {
                    $fileName = substr($jaeDescription, 0, 20);
                }
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $dct['dct_id'],
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => $jaeId,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => $fileName,
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'Y',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                $docId = $docDao->getLastInsertId();
                $upload = new FileUpload($docDao->getLastInsertId());
                $upload->upload($file);
                $upload->uploadThumbnail(200);
            }
            if ($docId !== null) {
                $jaeDao->doUpdateTransaction($jaeId, [
                    'jae_doc_id' => $docId,
                ]);
            }
        } else if ($this->getFormAction() === 'doFinishJob') {
            $joColVal = [
                'jo_finish_by' => $this->User->getId(),
                'jo_finish_on' => date('Y-m-d H:i:s'),
            ];
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), $joColVal);
            # Do Notification
            $this->doGenerateNotificationReceiver('jobfinish');
        } else if ($this->getFormAction() === 'doCompleteDocumentJob') {
            $joColVal = [
                'jo_document_by' => $this->User->getId(),
                'jo_document_on' => date('Y-m-d H:i:s'),
            ];
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), $joColVal);
        } else if ($this->getFormAction() === 'doUpdateDocument') {
            # Upload Document.
            $file = $this->getFileParameter('doc_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('doc_dct_id'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => null,
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => $this->getStringParameter('doc_description'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'Y',
                ];
                $docDao = new DocumentDao();
                $docDao->doInsertTransaction($colVal);
                $upload = new FileUpload($docDao->getLastInsertId());
                $upload->upload($file);
            }
        } else if ($this->getFormAction() === 'doDeleteDocument') {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('doc_id_del'));
        } elseif ($this->getFormAction() === 'doExportTimeShtXls') {
            $this->doExportTimeShtXls();
        }
    }

    /**
     * Function to do the update of the transaction.;
     * @return void
     */
    protected function doUpdateSalesGoodsPosition(): void
    {
        # Update Sales Order Goods Position
        $sgpData = SalesGoodsPositionDao::getByJobId($this->getDetailReferenceValue());
        $sogDao = new SalesOrderGoodsDao();
        foreach ($sgpData as $row) {
            $sogDao->doUpdateTransaction($row['sgp_sog_id'], [
                'sog_sgp_id' => $row['sgp_id']
            ]);
        }
    }

    /**
     * Function to do the update of the transaction.;
     * @return void
     */
    protected function doCompleteSalesGoodsPosition(): void
    {
        # Update Sales Order Goods Position
        $sgpData = SalesGoodsPositionDao::getByJobId($this->getDetailReferenceValue());
        $sgpDao = new SalesGoodsPositionDao();
        foreach ($sgpData as $row) {
            $sgpDao->doUpdateTransaction($row['sgp_id'], [
                'sgp_complete_on' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Function to do the update of the transaction.;
     * @param string $dateTime To store date time data.
     * @return void
     */
    protected function doStartJobOrder(string $dateTime = ''): void
    {
        if (empty($dateTime) === true) {
            $dateTime = $this->getStringParameter('jac_date', date('Y-m-d')) . ' ' . $this->getStringParameter('jac_time', date('H:i')) . ':00';
        }
        # Update start Job
        $joDao = new JobOrderDao();
        $joDao->doUpdateTransaction($this->getDetailReferenceValue(), [
            'jo_start_by' => $this->User->getId(),
            'jo_start_on' => $dateTime,
        ]);

        if ($this->isValidSoId() === true && $this->isSoInProgress() === false) {
            $soDao = new SalesOrderDao();
            $soDao->doUpdateTransaction($this->getSoId(), [
                'so_start_by' => $this->User->getId(),
                'so_start_on' => $dateTime,
            ]);
        }
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @param int $type TO set the type of update.
     *                  0. is for start and end data.
     *                  1. is only for start data information
     *                  2. is only for end data information.
     *
     * @return void
     */
    protected function doUpdateJobAction($type = 0): void
    {
        if ($type === 1) {
            $jacColVal = [
                'jac_start_by' => $this->User->getId(),
                'jac_start_on' => date('Y-m-d H:i:s'),
                'jac_start_date' => $this->getStringParameter('jac_date'),
                'jac_start_time' => $this->getStringParameter('jac_time'),
            ];
        } else if ($type === 2) {
            $jacColVal = [
                'jac_end_by' => $this->User->getId(),
                'jac_end_on' => date('Y-m-d H:i:s'),
                'jac_end_date' => $this->getStringParameter('jac_date'),
                'jac_end_time' => $this->getStringParameter('jac_time'),
            ];
        } else {
            $jacColVal = [
                'jac_start_by' => $this->User->getId(),
                'jac_start_on' => date('Y-m-d H:i:s'),
                'jac_start_date' => $this->getStringParameter('jac_date'),
                'jac_start_time' => $this->getStringParameter('jac_time'),
                'jac_end_by' => $this->User->getId(),
                'jac_end_on' => date('Y-m-d H:i:s'),
                'jac_end_date' => $this->getStringParameter('jac_date'),
                'jac_end_time' => $this->getStringParameter('jac_time'),
            ];
        }
        $jacDao = new JobActionDao();
        $jacDao->doUpdateTransaction($this->getIntParameter('action_id'), $jacColVal);

        # Upload File if Exist;

        $jaeColVal = [
            'jae_jac_id' => $this->getIntParameter('action_id'),
            'jae_description' => $this->getStringParameter('action_event'),
            'jae_date' => $this->getStringParameter('jac_date'),
            'jae_time' => $this->getStringParameter('jac_time'),
            'jae_active' => 'Y',
        ];
        $jaeDao = new JobActionEventDao();
        $jaeDao->doInsertTransaction($jaeColVal);
        $jaeId = $jaeDao->getLastInsertId();
        $joDao = new JobOrderDao();
        $joDao->doUpdateTransaction($this->getDetailReferenceValue(), [
            'jo_jae_id' => $jaeId,
        ]);
        # Do Upload Document
        $file = $this->getFileParameter('jac_image');
        $docId = null;
        if ($file !== null) {
            $dct = DocumentTypeDao::getByCode('joborder', 'actionevent');
            $fileName = $this->getStringParameter('action_event');
            if (mb_strlen($fileName) > 20) {
                $fileName = substr($fileName, 0, 20);
            }
            $colVal = [
                'doc_ss_id' => $this->User->getSsId(),
                'doc_dct_id' => $dct['dct_id'],
                'doc_group_reference' => $this->getDetailReferenceValue(),
                'doc_type_reference' => $jaeId,
                'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                'doc_description' => $fileName,
                'doc_file_size' => $file->getSize(),
                'doc_file_type' => $file->getClientOriginalExtension(),
                'doc_public' => 'Y',
            ];
            $docDao = new DocumentDao();
            $docDao->doInsertTransaction($colVal);
            $docId = $docDao->getLastInsertId();
            $upload = new FileUpload($docId);
            $upload->upload($file);
            $upload->uploadThumbnail(200);
        }
        if ($docId !== null) {
            $jaeDao->doUpdateTransaction($jaeId, [
                'jae_doc_id' => $docId,
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
        return [];
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->setOfficerParameter();
        # Load Cash Advance
        $this->JobSales = JobSalesDao::getByJobId($this->getDetailReferenceValue());
        $this->JobPurchase = JobPurchaseDao::getByJobId($this->getDetailReferenceValue());
        $this->CashAdvance = CashAdvanceDao::getByJobId($this->getDetailReferenceValue());

        # Load Current Action
        $this->CurrentAction = JobActionDao::getLastActiveActionByJobId($this->getDetailReferenceValue());
        # Override title page
        $this->overridePageTitle();

        # Load goods data.
        $this->loadGoodsData();
        if ($this->isJobDeleted() === true) {
            $this->setDisableUpdate();
            $this->View->addErrorMessage(Trans::getWord('jobCanceledReason', 'message', '', [
                'user' => $this->getStringParameter('jo_deleted_by'),
                'time' => DateTimeParser::format($this->getStringParameter('jo_deleted_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                'reason' => $this->getStringParameter('jo_deleted_reason')
            ]));
        }
        # Show delete reason
        if ($this->isJobHold() === true) {
            $this->setDisableUpdate();
            $date = DateTimeParser::format($this->getStringParameter('jo_hold_on'), 'Y-m-d H:i:s', 'H:i - d M Y');
            $this->View->addWarningMessage(Trans::getWord('joHoldReason', 'message', '', ['date' => $date, 'reason' => $this->getStringParameter('jo_hold_reason')]));
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    protected function loadActionValidationRole(): void
    {
        $this->Validation->checkRequire('action_event');
        $this->Validation->checkRequire('action_id');
        $this->Validation->checkRequire('jac_date');
        $this->Validation->checkRequire('jac_time');
        $this->Validation->checkDate('jac_date');
        $this->Validation->checkTime('jac_time');
        if ($this->isValidParameter('jac_image') === true) {
            $this->Validation->checkImage('jac_image');
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdateEvent') {
            $this->Validation->checkRequire('jac_id');
            $this->Validation->checkRequire('jac_ac_id');
            if ($this->isValidParameter('jae_sae_id') === false) {
                $this->Validation->checkRequire('jae_description', 5, 255);
            }
            $this->Validation->checkMaxLength('jae_remark', 255);
            if ($this->isValidParameter('jae_image') === true) {
                $this->Validation->checkImage('jae_image');
            }
            $this->Validation->checkRequire('jae_date');
            $this->Validation->checkRequire('jae_time');
            $this->Validation->checkDate('jae_date');
            $this->Validation->checkTime('jae_time');
        } else if ($this->getFormAction() === 'doUpdateDocument') {
            $this->Validation->checkRequire('doc_dct_id');
            $this->Validation->checkRequire('doc_file');
            $this->Validation->checkFile('doc_file');
        } else if ($this->getFormAction() === 'doDeleteDocument') {
            $this->Validation->checkRequire('doc_id_del');
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getGeneralFieldSet(): Portlet
    {
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('customer'),
                'value' => $this->getStringParameter('jo_customer'),
            ],
            [
                'label' => Trans::getWord('picCustomer'),
                'value' => $this->getStringParameter('jo_pic_customer'),
            ],
            [
                'label' => Trans::getWord('orderOffice'),
                'value' => $this->getStringParameter('jo_order_office'),
            ],
            [
                'label' => Trans::getWord('orderDate'),
                'value' => DateTimeParser::format($this->getStringParameter('jo_order_date'), 'Y-m-d', 'd M Y'),
            ],
            [
                'label' => Trans::getWord('jobManager'),
                'value' => $this->getStringParameter('jo_manager'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JoGeneralPtl', Trans::getWord('customer'));
        $portlet->addText($content);
        $portlet->addText($this->Field->getHidden('jo_order_of_id', $this->getIntParameter('jo_order_of_id')));
        $portlet->addText($this->Field->getHidden('jo_rel_id', $this->getIntParameter('jo_rel_id')));
        $portlet->addText($this->Field->getHidden('jo_srv_id', $this->getIntParameter('jo_srv_id')));
        $portlet->addText($this->Field->getHidden('jo_service_term', $this->getStringParameter('jo_service_term')));
        $portlet->addText($this->Field->getHidden('jo_srt_id', $this->getIntParameter('jo_srt_id')));
        $portlet->addText($this->Field->getHidden('jo_aju_ref', $this->getStringParameter('jo_aju_ref')));
        $portlet->addText($this->Field->getHidden('jo_so_id', $this->getIntParameter('jo_so_id')));
        $portlet->addText($this->Field->getHidden('so_start_on', $this->getIntParameter('so_start_on')));
        $portlet->addText($this->Field->getHidden('jo_jtr_id', $this->getIntParameter('jo_jtr_id')));
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    protected function getReferenceFieldSet(): Portlet
    {
        $data = [];
        if ($this->isValidParameter('jo_so_id') === true) {
            $data[] = [
                'label' => Trans::getWord('soNumber'),
                'value' => $this->getStringParameter('so_number'),
            ];
        }
        $data[] = [
            'label' => Trans::getWord('customerRef'),
            'value' => $this->getStringParameter('jo_customer_ref'),
        ];
        $data[] = [
            'label' => Trans::getWord('blRef'),
            'value' => $this->getStringParameter('jo_bl_ref'),
        ];
        $data[] = [
            'label' => Trans::getWord('ajuRef'),
            'value' => $this->getStringParameter('jo_aju_ref'),
        ];
        $data[] = [
            'label' => Trans::getWord('packingListRef'),
            'value' => $this->getStringParameter('jo_packing_ref'),
        ];
        $data[] = [
            'label' => Trans::getWord('sppbRef'),
            'value' => $this->getStringParameter('jo_sppb_ref'),
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('JoGReferencePtl', Trans::getWord('reference'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to get the vendor Portlet.
     *
     * @return Portlet
     */
    protected function getVendorPortlet(): Portlet
    {
        $data = [
            [
                'label' => Trans::getWord('jobManager'),
                'value' => $this->getStringParameter('jo_manager'),
            ],
            [
                'label' => Trans::getWord('vendor'),
                'value' => $this->getStringParameter('jo_vendor'),
            ],
            [
                'label' => Trans::getWord('picVendor'),
                'value' => $this->getStringParameter('jo_pic_vendor'),
            ],
            [
                'label' => Trans::getWord('vendorReference'),
                'value' => $this->getStringParameter('jo_vendor_ref'),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('JoVendorPtl', Trans::getWord('vendor'));
        $portlet->addText($content);
        $portlet->setGridDimension(4, 4, 4);
        return $portlet;
    }

    /**
     * Function to get Goods delete modal.
     *
     * @return void
     */
    protected function overridePageTitle(): void
    {
        $title = $this->getStringParameter('jo_number');
        $data = [
            'is_deleted' => $this->isValidParameter('jo_deleted_on'),
            'is_hold' => $this->isValidParameter('jo_joh_id'),
            'is_finish' => $this->isValidParameter('jo_finish_on'),
            'is_document' => $this->isValidParameter('jo_document_on'),
            'is_start' => $this->isValidParameter('jo_start_on'),
            'is_publish' => $this->isValidParameter('jo_publish_on'),
        ];
        if (empty($this->CurrentAction) === false) {
            $data = array_merge($data, [
                'jac_id' => $this->CurrentAction['jac_id'],
                'jae_style' => $this->CurrentAction['jac_style'],
                'jac_action' => $this->CurrentAction['jac_action'],
                'jae_description' => $this->CurrentAction['jae_description'],
                'jo_srt_id' => $this->CurrentAction['ac_srt_id'],
            ]);
        }
        $joDao = new JobOrderDao();
        $title .= ' | ' . $joDao->generateStatus($data);
        $this->View->setDescription($title);
    }

    /**
     * Function to load goods data.
     *
     * @return void
     */
    protected function loadGoodsData(): void
    {
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $this->Goods = JobGoodsDao::loadData($wheres);
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getGoodsFieldSet(): Portlet
    {
        $table = new Table('JoJogTbl');
        $table->setHeaderRow([
            'jog_name' => Trans::getWord('goods'),
            'jog_quantity' => Trans::getWord('quantity'),
            'jog_unit' => Trans::getWord('uom'),
            'jog_length' => Trans::getWord('length') . ' (M)',
            'jog_width' => Trans::getWord('width') . ' (M)',
            'jog_height' => Trans::getWord('height') . ' (M)',
            'jog_volume' => Trans::getWord('volume') . ' (M3)',
            'jog_gross_weight' => Trans::getWord('grossWeight') . ' (KG)',
            'jog_net_weight' => Trans::getWord('netWeight') . ' (KG)',
        ]);

        $table->addRows($this->Goods);
        $table->setColumnType('jog_quantity', 'float');
        $table->setColumnType('jog_length', 'float');
        $table->setColumnType('jog_width', 'float');
        $table->setColumnType('jog_height', 'float');
        $table->setColumnType('jog_volume', 'float');
        $table->setColumnType('jog_gross_weight', 'float');
        $table->setColumnType('jog_net_weight', 'float');
        # Create a portlet box.
        $portlet = new Portlet('JoJogPtl', Trans::getWord('goods'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getTimeSheetFieldSet(): Portlet
    {
        $table = new Table('JoJaeTbl');
        $table->setHeaderRow([
            'jae_action' => Trans::getWord('action'),
            'jae_event' => Trans::getWord('event'),
            'jae_remark' => Trans::getWord('remark'),
            'jae_time' => Trans::getWord('time'),
            'jae_creator' => Trans::getWord('reportedBy'),
            'jae_created_on' => Trans::getWord('reportedOn'),
            'image' => Trans::getWord('image'),
        ]);
        $table->addRows($this->loadTimeSheetData());
        $table->addColumnAttribute('image', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('JoJaePtl', Trans::getWord('timeSheet'));
        # Add export PDF button
//        dd($this->getAllParameters());
        $btnPdf = new PdfButton('btnExportTimeShtPdf', Trans::getWord('printPdf'), 'worksheet');
        $btnPdf->setIcon(Icon::FilePdfO)->btnPrimary()->pullRight()->btnMedium();
        $btnPdf->addParameter('jac_jo_id', $this->getDetailReferenceValue());
        $btnPdf->addParameter('jo_srv_code', $this->getStringParameter('jo_srv_code'));
        $btnPdf->addParameter('jo_srt_route', $this->getStringParameter('jo_srt_route'));
        $portlet->addButton($btnPdf);
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return array
     */
    private function loadTimeSheetData(): array
    {
        $result = [];
        $imageNotFoundPath = asset('images/image-not-found.jpg');
        $events = JobActionEventDao::loadEventByJobId($this->getDetailReferenceValue());
        if ($this->isValidParameter('jo_finish_on') === true) {
            $time = DateTimeParser::format($this->getStringParameter('jo_finish_on'), 'Y-m-d H:i:s', 'H:i - d M Y');
            $result[] = [
                'jae_action' => Trans::getWord('finish'),
                'jae_event' => '',
                'jae_remark' => '',
                'jae_time' => $time,
                'jae_creator' => $this->getStringParameter('jo_finish_by'),
                'jae_created_on' => $time,
                'image' => '<img style="text-align: center" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . Trans::getWord('finish') . '"/>',
            ];
        }
        if ($this->isValidParameter('jo_document_on') === true) {
            $time = DateTimeParser::format($this->getStringParameter('jo_document_on'), 'Y-m-d H:i:s', 'H:i - d M Y');
            $result[] = [
                'jae_action' => Trans::getWord('documentComplete'),
                'jae_event' => '',
                'jae_remark' => '',
                'jae_time' => $time,
                'jae_creator' => $this->getStringParameter('jo_document_by'),
                'jae_created_on' => $time,
                'image' => '<img style="text-align: center" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . Trans::getWord('documentComplete') . '"/>',
            ];
        }
        $docDao = new DocumentDao();
        foreach ($events as $row) {
            $image = '<img style="text-align: center" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . $row['jae_description'] . '"/>';
            if (empty($row['doc_id']) === false) {
                $path = $docDao->getDocumentPath($row);
                $image = '<img onclick="App.popup(\'' . url('/download?doc_id=' . $row['doc_id']) . '\')" style="text-align: center" class="img-responsive avatar-view" src="' . $path . '" alt="Event" title="' . $row['jae_description'] . '"/>';
            }
            $time = '';
            if (empty($row['jae_date']) === false) {
                if (empty($row['jae_time']) === false) {
                    $time = DateTimeParser::format($row['jae_date'] . ' ' . $row['jae_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
                } else {
                    $time = DateTimeParser::format($row['jae_date'], 'Y-m-d', 'd M Y');
                }
            }


            $result[] = [
                'jae_action' => Trans::getWord($row['jae_action'] . $this->getIntParameter('jo_srt_id') . '.description', 'action'),
                'jae_event' => $row['jae_description'],
                'jae_remark' => $row['remark'],
                'jae_time' => $time,
                'jae_creator' => $row['jae_created_by'],
                'jae_created_on' => DateTimeParser::format($row['jae_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y'),
                'image' => $image,
            ];
        }
        if ($this->isValidParameter('jo_publish_on') === true) {
            $time = DateTimeParser::format($this->getStringParameter('jo_publish_on'), 'Y-m-d H:i:s', 'H:i - d M Y');
            $result[] = [
                'jae_action' => Trans::getWord('published'),
                'jae_event' => '',
                'jae_remark' => '',
                'jae_time' => $time,
                'jae_creator' => $this->getStringParameter('jo_publish_by'),
                'jae_created_on' => $time,
                'image' => '<img style="text-align: center" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . Trans::getWord('published') . '"/>',
            ];
        }
        $time = DateTimeParser::format($this->getStringParameter('jo_created_on'), 'Y-m-d H:i:s', 'H:i - d M Y');
        $result[] = [
            'jae_action' => Trans::getWord('draft'),
            'jae_event' => '',
            'jae_remark' => '',
            'jae_time' => $time,
            'jae_creator' => $this->getStringParameter('jo_created_by'),
            'jae_created_on' => $time,
            'image' => '<img style="text-align: center" class="img-responsive avatar-view" src="' . $imageNotFoundPath . '" alt="Event" title="' . Trans::getWord('created') . '"/>',
        ];


        return $result;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isAllowUpdateAction()) {
            if ($this->EnableAction === true) {
                $this->manageAction();
            }
            if ($this->JobAction !== null) {
                $this->JobAction->addActionView($this->View);
            }
            if (empty($this->CurrentAction) === false && $this->JobAction !== null) {
                if ($this->JobAction->hasNextAction() === true) {
                    # Show Button Event
                    $eventModal = $this->getEventModal();
                    $this->View->addModal($eventModal);
                    $btnEvent = new ModalButton('btnJoJae', Trans::getWord('event'), $eventModal->getModalId());
                    $btnEvent->setIcon(Icon::Tasks)->btnInfo()->pullRight()->btnMedium();
                    $this->View->addButton($btnEvent);
                } else {
                    if ($this->isValidParameter('jo_document_on') === false) {
                        # Show Document Button
                        $modal = $this->getCompleteDocumentModal();
                        $this->View->addModal($modal);
                        $btnDoc = new ModalButton('btnDocJo', Trans::getWord('documentation'), $modal->getModalId());
                        $btnDoc->setIcon(Icon::FileO)->btnWarning()->pullRight()->btnMedium();
                        $this->View->addButton($btnDoc);
                    } else {
                        # Show Finish Button
                        $modal = $this->getJobFinishModal();
                        $this->View->addModal($modal);
                        $btnComplete = new ModalButton('btnFinishJo', Trans::getWord('finish'), $modal->getModalId());
                        $btnComplete->setIcon(Icon::CheckSquareO)->btnSuccess()->pullRight()->btnMedium();
                        $this->View->addButton($btnComplete);
                    }

                }
            }
        }
        # Show SO Button
        if ($this->isValidSoId() === true) {
            $btnSo = new HyperLink('hplSo', $this->getStringParameter('so_number', 'SO'), url('so/detail?so_id=' . $this->getSoId()));
            $btnSo->viewAsButton();
            $btnSo->setIcon(Icon::Eye)->btnInfo()->pullRight()->btnMedium();
            $this->View->addButton($btnSo);
        }
        parent::loadDefaultButton();
    }


    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    private function getJobFinishModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoFinishMdl', Trans::getWord('finishConfirmation'));
        $valid = $this->doValidateFinishJobAction();
        if ($valid === false) {
            $p = new Paragraph(Trans::getMessageWord('unableToFinishJobBeforeInvoice'));
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setTitle(Trans::getWord('warning'));
            $modal->setDisableBtnOk();
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
        } else {
            $text = Trans::getWord('finishJobConfirmation', 'message');
            $modal->setFormSubmit($this->getMainFormId(), 'doFinishJob');
            $modal->setBtnOkName(Trans::getWord('yesFinish'));
            $p = new Paragraph($text);
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
        }

        return $modal;
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return bool
     */
    private function doValidateFinishJobAction(): bool
    {
        $valid = true;
        foreach ($this->JobSales as $row) {
            if (empty($row['jos_sid_id']) === true || empty($row['jos_si_number']) === true) {
                $valid = false;
            }
        }
        foreach ($this->JobPurchase as $row) {
            if (empty($row['jop_cad_id']) === true && (empty($row['jop_pid_id']) === true || empty($row['jop_pi_number']) === true)) {
                $valid = false;
            } elseif (empty($row['jop_cad_id']) === false && empty($row['jop_ca_settlement_on']) === true) {
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    private function getCompleteDocumentModal(): Modal
    {
        $message = $this->doValidateRequiredDocument();
        # Create Fields.
        if (empty($message) === true) {
            $modal = new Modal('JoCompleteDocMdl', Trans::getWord('completeDocument'));
            $text = Trans::getWord('completeDocumentConfirmation', 'message');

            $modal->setFormSubmit($this->getMainFormId(), 'doCompleteDocumentJob');
            $modal->setBtnOkName(Trans::getWord('yesComplete'));
            $p = new Paragraph($text);
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
        } else {
            $modal = new Modal('JoCompleteDocMdl', Trans::getWord('missingRequiredDocument', 'message'));
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
            $p = new Paragraph($message);
            $p->setAsLabelLarge()->setAlignCenter();
            $modal->addText($p);
            $modal->setDisableBtnOk();
        }

        return $modal;
    }


    /**
     * Function to get publish confirmation modal.
     *
     * @return string
     */
    private function doValidateRequiredDocument(): string
    {

        $docs = ServiceTermDocumentDao::loadDocumentByGroupAndServiceTerm($this->User->getSsId(), $this->getDetailReferenceValue(), $this->getIntParameter('jo_srt_id'));
        $complete = true;
        foreach ($docs as $row) {
            if ((int)$row['total'] === 0) {
                $complete = false;
            }
        }
        if ($complete === false) {
            $table = new Table('ValDocJobTbl');
            $table->setHeaderRow([
                'dct_description' => Trans::getWord('description'),
                'dct_required' => Trans::getWord('required'),
                'total' => Trans::getWord('registered'),
            ]);
            $rows = [];
            $i = 0;
            foreach ($docs as $row) {
                $required = 0;
                if ($row['dct_master'] === 'Y') {
                    $required = 1;
                }
                $row['dct_required'] = $required;
                if ((int)$row['total'] !== $required) {
                    $table->addCellAttribute('total', $i, 'style', 'background-color: red; color: white; font-weight: bold; text-align: right;');
                }
                $i++;
                $rows[] = $row;
            }
            $table->addRows($rows);
            $table->setColumnType('dct_required', 'integer');
            $table->setColumnType('total', 'integer');

            return $table->createTable();
        }

        return '';
    }


    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getEventModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoJaeMdl', Trans::getWord('event'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateEvent');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateEvent' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        } else {
            if ($this->isValidParameter('jae_date') === false) {
                $this->setParameter('jae_date', date('Y-m-d'));
            }
            if ($this->isValidParameter('jae_time') === false) {
                $this->setParameter('jae_time', date('H:i'));
            }
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Create action Field
        $actionField = $this->Field->getText('joc_action', $this->CurrentAction['jac_action']);
        $actionField->setReadOnly();

        # Create action Field
        $eventField = $this->Field->getSingleSelect('systemActionEvent', 'jae_event', $this->getParameterForModal('jae_event', $showModal));
        $eventField->setHiddenField('jae_sae_id', $this->getParameterForModal('jae_sae_id', $showModal));
        $eventField->addParameter('sac_ss_id', $this->User->getSsId());
        $eventField->addParameter('sac_ac_id', $this->CurrentAction['jac_ac_id']);
        $eventField->setEnableDetailButton(false);
        $eventField->setEnableNewButton(false);
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('action'), $actionField, true);
        $fieldSet->addField(Trans::getWord('event'), $eventField);
        $fieldSet->addField(Trans::getWord('other'), $this->Field->getTextArea('jae_description', $this->getParameterForModal('jae_description', $showModal)));
        $fieldSet->addField(Trans::getWord('remark'), $this->Field->getTextArea('jae_remark', $this->getParameterForModal('jae_remark', $showModal)));
        $fieldSet->addField(Trans::getWord('actualDate'), $this->Field->getCalendar('jae_date', $this->getParameterForModal('jae_date', true)));
        $fieldSet->addField(Trans::getWord('actualTime'), $this->Field->getTime('jae_time', $this->getParameterForModal('jae_time', true)));
        $fieldSet->addField(Trans::getWord('image'), $this->Field->getFile('jae_image', ''));
        $fieldSet->addHiddenField($this->Field->getHidden('jac_ac_id', $this->CurrentAction['jac_ac_id']));
        $fieldSet->addHiddenField($this->Field->getHidden('jac_id', $this->CurrentAction['jac_id']));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return void
     */
    protected function manageAction(): void
    {
        $service = $this->getStringParameter('jo_srv_code');
        $serviceTerm = $this->getStringParameter('jo_srt_route');
        if ($service === 'warehouse') {
            switch ($serviceTerm) {
                case 'joWhInbound':
                    $this->JobAction = new InboundAction($this);
                    break;
                case 'joWhOutbound':
                    $this->JobAction = new OutboundAction($this);
                    break;
                case 'joWhOpname':
                    $this->JobAction = new OpnameAction($this);
                    break;
                case 'joWhStockAdjustment':
                    $this->JobAction = new StockAdjustmentAction($this);
                    break;
                case 'joWhStockMovement':
                    $this->JobAction = new StockMovementAction($this);
                    break;
                case 'joWhBundling':
                    $this->JobAction = new BundlingAction($this);
                    break;
                case 'joWhUnBundling':
                    $this->JobAction = new UnBundlingAction($this);
                    break;
            }
        } else if ($service === 'inklaring') {
            $this->JobAction = new InklaringAction($this);
        } else if ($service === 'delivery') {
            $this->JobAction = new DeliveryAction($this);
        }
    }


    /**
     * Function to get the bank Field Set.
     *
     * @return Portlet
     */
    protected function getDocumentFieldSet(): Portlet
    {
        # create modal.
        $docDeleteModal = $this->getDocumentDeleteModal();
        $this->View->addModal($docDeleteModal);
//        $isOfficer = false;
        # Create table.
        $docTable = new Table('JoDocTbl');
        $docTable->setHeaderRow([
            'dct_description' => Trans::getWord('type'),
            'doc_description' => Trans::getWord('description'),
            'doc_creator' => Trans::getWord('uploader'),
            'doc_created_on' => Trans::getWord('uploadedOn'),
            'download' => Trans::getWord('download'),
        ]);
//        if ($this->isOfficer() === true) {
//            $isOfficer = true;
//            // $docTable->setDeleteActionByModal($docDeleteModal, 'document', 'getByReferenceForDelete', ['doc_id']);
//        }

        $wheres = [];
        if ($this->getStringParameter('jo_srv_code') === 'inklaring') {
            $joWhere = "((dcg.dcg_code = 'joborder') AND (doc.doc_group_reference = " . $this->getDetailReferenceValue() . '))';
            $soWhere = "((dcg.dcg_code = 'salesorder') AND (doc.doc_group_reference = " . $this->getIntParameter('jik_so_id') . '))';
            $wheres[] = '(' . $joWhere . ' OR ' . $soWhere . ')';
        } else {
            $wheres[] = "(dcg.dcg_code = 'joborder')";
            $wheres[] = '(doc.doc_group_reference = ' . $this->getDetailReferenceValue() . ')';
        }
        $wheres[] = "(dct.dct_master = 'Y')";
        $wheres[] = '(doc.doc_deleted_on IS NULL)';
        $data = DocumentDao::loadData($wheres);
        $results = [];
        foreach ($data as $row) {
            $btn = new Button('btnDocDownloadMdl' . $row['doc_id'], '');
            $btn->setIcon(Icon::Download)->btnWarning()->viewIconOnly();
            $btn->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $row['doc_id']) . "')");
            $row['download'] = $btn;
            $row['doc_created_on'] = DateTimeParser::format($row['doc_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y');

            if ((int)$row['doc_group_reference'] === $this->getDetailReferenceValue()) {
                $btnDel = new ModalButton('btnDocDel' . $row['doc_id'], '', $docDeleteModal->getModalId());
                $btnDel->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
                $btnDel->setEnableCallBack('document', 'getByReferenceForDelete');
                $btnDel->addParameter('doc_id', $row['doc_id']);
                $row['action'] = $btnDel;
            }
            $results[] = $row;
        }
        $docTable->addRows($results);
        # Create a portlet box.
        $docTable->addColumnAttribute('download', 'style', 'text-align: center');
        $portlet = new Portlet('JoDocPtl', Trans::getWord('document'));
        $portlet->addTable($docTable);
        if ($this->isAllowUpdateAction()) {
            $docTable->addColumnAtTheEnd('action', Trans::getWord('action'));
            $docTable->addColumnAttribute('action', 'style', 'text-align: center');
            # create modal.
            $docModal = $this->getDocumentModal();
            $this->View->addModal($docModal);
            $btnDocMdl = new ModalButton('btnDocMdl', Trans::getWord('upload'), $docModal->getModalId());
            $btnDocMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnDocMdl);
        }

        return $portlet;
    }

    /**
     * Function to get the relation bank modal.
     *
     * @return Modal
     */
    private function getDocumentModal(): Modal
    {
        $modal = new Modal('JoDocMdl', Trans::getWord('documents'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateDocument');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateDocument' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create document type field.
        $dctFields = $this->Field->getSingleSelect('documentType', 'dct_code', $this->getParameterForModal('dct_code', $showModal));
        $dctFields->setHiddenField('doc_dct_id', $this->getParameterForModal('doc_dct_id', $showModal));
        $dctFields->addParameter('dcg_code', 'joborder');
        $dctFields->addParameter('dct_master', 'Y');
        $dctFields->setEnableDetailButton(false);
        $dctFields->setEnableNewButton(false);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('documentType'), $dctFields, true);
        $fieldSet->addField(Trans::getWord('file'), $this->Field->getFile('doc_file', $this->getParameterForModal('doc_file', $showModal)), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('doc_description', $this->getParameterForModal('doc_description', $showModal)), true);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get the relation bank modal.
     *
     * @return Modal
     */
    private function getDocumentDeleteModal(): Modal
    {
        $modal = new Modal('JoDocDelMdl', Trans::getWord('deleteDocument'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteDocument');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteDocument' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create document type field.
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('documentType'), $this->Field->getText('dct_code_del', $this->getParameterForModal('dct_code_del', $showModal)));
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('doc_description_del', $this->getParameterForModal('doc_description_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('doc_id_del', $this->getParameterForModal('doc_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

//
//    /**
//     * Function to get the relation bank modal.
//     *
//     * @return bool
//     */
//    private function isAllSoJobCompleted(): bool
//    {
//        $complete = false;
//        $wheres = [];
//        $wheres[] = '(jo_so_id =' . $this->getIntParameter('jo_so_id') . ')';
//        $wheres[] = '(jo_finish_on IS NULL)';
//        $wheres[] = '(jo_deleted_on IS NULL)';
//        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
//        $query = 'SELECT jo_id
//                    FROM job_order ' . $strWhere;
//        $sqlResult = DB::select($query);
//        if (empty($sqlResult) === true) {
//            $complete = true;
//        }
//
//        return $complete;
//    }


    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isJobDeleted(): bool
    {

        return $this->isValidParameter('jo_deleted_on');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isJobPublished(): bool
    {

        return $this->isValidParameter('jo_publish_on');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isJobFinish(): bool
    {
        return $this->isValidParameter('jo_finish_on');
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isJobHold(): bool
    {
        return $this->isValidParameter('jo_joh_id');
    }

    /**
     * Function to load default button
     *
     * @return bool
     */
    protected function isOfficer(): bool
    {
        return $this->PageSetting->checkPageRight('AllowUpdateAction') === true || $this->getStringParameter('jo_officer', 'N') === 'Y';
    }

    /**
     * Function to load default button
     *
     * @return bool
     */
    protected function isAllowUpdateAction(): bool
    {
        return $this->isOfficer() && !$this->isJobDeleted() && $this->isJobPublished() && !$this->isJobFinish() && !$this->isJobHold();
    }

    /**
     * Function to get the sales Field Set.
     *
     * @return Portlet
     */
    protected function getSalesFieldSet(): Portlet
    {
        $table = new Table('JoSalesTbl');
        $table->setHeaderRow([
            'jos_relation' => Trans::getFinanceWord('billTo'),
            'jos_description' => Trans::getFinanceWord('description'),
            'jos_quantity' => Trans::getFinanceWord('quantity'),
            'jos_rate' => Trans::getFinanceWord('rate'),
            'jos_exchange_rate' => Trans::getFinanceWord('exchangeRate'),
            'jos_tax_name' => Trans::getFinanceWord('tax'),
            'jos_total' => Trans::getFinanceWord('total'),
            'jos_type' => Trans::getFinanceWord('type'),
            'jos_quotation_number' => Trans::getFinanceWord('quotation'),
//            'jos_action' => Trans::getFinanceWord('invoice'),
        ]);
        $rows = [];
        $number = new NumberFormatter($this->User);
        $i = 0;
        foreach ($this->JobSales as $row) {
            if ($row['jos_type'] === 'S') {
                $row['jos_type'] = new LabelPrimary(Trans::getFinanceWord('revenue'));
            } else {
                $row['jos_type'] = new LabelDark(Trans::getFinanceWord('reimburse'));
            }
            $action = '';
            if (empty($row['jos_si_id']) === false) {
                $url = url('/salesInvoice/detail?si_id=' . $row['jos_si_id']);
                $siButton = new HyperLink('JoSiBtn' . $row['jos_id'], '', $url);
                $siButton->viewAsButton();
                $siButton->setIcon(Icon::Money)->btnSuccess()->viewIconOnly();
                $action .= $siButton . ' ';
            }
            $row['jos_action'] = $action;
            $row['jos_description'] = $row['jos_cc_code'] . ' - ' . $row['jos_description'];
            $row['jos_quantity'] = $number->doFormatFloat($row['jos_quantity']) . ' ' . $row['jos_uom_code'];
            $row['jos_rate'] = $row['jos_cur_iso'] . ' ' . $number->doFormatFloat($row['jos_rate']);
            if (empty($row['jos_exchange_rate']) === false) {
                $row['jos_exchange_rate'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jos_exchange_rate']);
                $row['jos_total'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jos_total']);
            } else {
                $row['jos_total'] = $row['jos_cur_iso'] . ' ' . $number->doFormatFloat($row['jos_total']);
                $table->addCellAttribute('jos_exchange_rate', $i, 'style', 'background-color: red;');
            }
            if (empty($row['jos_tax_id']) === true) {
                $table->addCellAttribute('jos_tax_name', $i, 'style', 'background-color: red;');
            }
            $rows[] = $row;
            $i++;
        }
        $table->addRows($rows);
        $table->addColumnAttribute('jos_quantity', 'style', 'text-align: right;');
        $table->addColumnAttribute('jos_exchange_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('jos_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('jos_total', 'style', 'text-align: right;');
        $table->addColumnAttribute('jos_cur_iso', 'style', 'text-align: center;');
        $table->addColumnAttribute('jos_tax_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('jos_type', 'style', 'text-align: center;');
        $table->addColumnAttribute('jos_quotation_number', 'style', 'text-align: center;');
//        $table->addColumnAttribute('jos_action', 'style', 'text-align: center;');

        $portlet = new Portlet('JoSalesPtl', Trans::getWord('sales'));
        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get the purchase Field Set.
     *
     * @return Portlet
     */
    protected function getPurchaseFieldSet(): Portlet
    {
        $table = new Table('JoPurchaseTbl');
        $table->setHeaderRow([
            'jop_relation' => Trans::getFinanceWord('billTo'),
            'jop_description' => Trans::getFinanceWord('description'),
            'jop_quantity' => Trans::getFinanceWord('quantity'),
            'jop_rate' => Trans::getFinanceWord('rate'),
            'jop_exchange_rate' => Trans::getFinanceWord('exchangeRate'),
            'jop_tax_name' => Trans::getFinanceWord('tax'),
            'jop_total' => Trans::getFinanceWord('total'),
            'jop_type' => Trans::getFinanceWord('type'),
            'jop_quotation_number' => Trans::getFinanceWord('quotation'),
            'jop_receipt' => Trans::getFinanceWord('receipt'),
//            'jop_action' => Trans::getFinanceWord('invoice'),
        ]);
        $rows = [];
        $number = new NumberFormatter($this->User);
        $i = 0;
        foreach ($this->JobPurchase as $row) {
            $row['jop_description'] = $row['jop_cc_code'] . ' - ' . $row['jop_description'];
            $row['jop_quantity'] = $number->doFormatFloat($row['jop_quantity']) . ' ' . $row['jop_uom_code'];
            $row['jop_rate'] = $row['jop_cur_iso'] . ' ' . $number->doFormatFloat($row['jop_rate']);
            if (empty($row['jop_exchange_rate']) === false) {
                $row['jop_exchange_rate'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jop_exchange_rate']);
                $row['jop_total'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jop_total']);
            } else {
                $row['jop_total'] = $row['jop_cur_iso'] . ' ' . $number->doFormatFloat($row['jop_total']);
                $table->addCellAttribute('jop_exchange_rate', $i, 'style', 'background-color: red;');
            }
            if (empty($row['jop_tax_id']) === true) {
                $table->addCellAttribute('jop_tax_name', $i, 'style', 'background-color: red;');
            }
            if ($row['jop_type'] === 'P') {
                $row['jop_type'] = new LabelPrimary(Trans::getFinanceWord('cogs'));
            } else {
                $row['jop_type'] = new LabelDark(Trans::getFinanceWord('reimburse'));
            }

            $action = '';
            if (empty($row['jop_pi_id']) === false) {
                $url = url('/purchaseInvoice/detail?pi_id=' . $row['jop_pi_id']);
                $piButton = new HyperLink('JoPiBtn' . $row['jop_id'], '', $url);
                $piButton->viewAsButton();
                $piButton->setIcon(Icon::Money)->btnSuccess()->viewIconOnly();
                $action .= $piButton;
            }
            $row['jop_action'] = $action;
            if (empty($row['jop_doc_id']) === false) {
                $btnDown = new Button('btnRecDown' . $row['jop_id'], '');
                $btnDown->setIcon(Icon::Download)->btnPrimary()->viewIconOnly();
                $btnDown->addAttribute('onclick', "App.popup('" . url('/download?doc_id=' . $row['jop_doc_id']) . "')");
                $row['jop_receipt'] = $btnDown;
            }

            $rows[] = $row;
            $i++;
        }
        $table->addRows($rows);
        $table->addColumnAttribute('jop_quantity', 'style', 'text-align: right;');
        $table->addColumnAttribute('jop_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('jop_total', 'style', 'text-align: right;');
        $table->addColumnAttribute('jop_exchange_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('jop_tax_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('jop_type', 'style', 'text-align: center;');
        $table->addColumnAttribute('jop_receipt', 'style', 'text-align: center;');
        $table->addColumnAttribute('jop_quotation_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jop_action', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('JoPurchasePtl', Trans::getWord('purchase'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the purchase Field Set.
     *
     * @return Portlet
     */
    protected function getFinanceMarginFieldSet(): Portlet
    {
        $table = new Table('JoFinMgnTbl');
        $table->setHeaderRow([
            'fn_description' => Trans::getFinanceWord('description'),
            'fn_planning' => Trans::getFinanceWord('planning'),
            'fn_invoice' => Trans::getFinanceWord('invoiced'),
            'fn_pay' => Trans::getFinanceWord('paid'),
        ]);
        $table->setDisableLineNumber();
        $data = JobOrderDao::loadFinanceMarginData($this->getDetailReferenceValue());
        $table->addRows($data);
        $table->setColumnType('fn_planning', 'float');
        $table->setColumnType('fn_invoice', 'float');
        $table->setColumnType('fn_pay', 'float');
        # Create a portlet box.
        $portlet = new Portlet('JoFinMgnPtl', Trans::getFinanceWord('grossMargin'));
        $portlet->addTable($table);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    private function isAllowToSeeSalesInformation(): bool
    {
        return $this->isThirdPartyUser() === false || $this->isCustomerUser() === true;
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    private function isAllowToSeePurchaseInformation(): bool
    {
        return $this->isThirdPartyUser() === false || $this->isVendorUser() === true;
    }

    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    private function isAllowToSeeMarginInformation(): bool
    {
        return $this->isThirdPartyUser() === false;
    }

    /**
     * Function to get cash advance portlet.
     *
     * @return Portlet
     */
    protected function getCashAdvancePortlet(): Portlet
    {
        $totalCa = 0.0;
        if (empty($this->CashAdvance['ca_receive_on']) === false) {
            if (empty($this->CashAdvance['ca_settlement_on']) === true) {
                $totalCa = (float)$this->CashAdvance['ca_amount'] + (float)$this->CashAdvance['ca_reserve_amount'];
            } else {
                $totalCa = (float)$this->CashAdvance['ca_actual_amount'] + (float)$this->CashAdvance['ca_ea_amount'];
            }
        }
        $dtParser = new DateTimeParser();
        $number = new NumberFormatter($this->User);
        $data = [
            [
                'label' => Trans::getFinanceWord('accountName'),
                'value' => $this->CashAdvance['ca_ba_code'] . ' - ' . $this->CashAdvance['ca_ba_description'],
            ],
            [
                'label' => Trans::getFinanceWord('eCardAccount'),
                'value' => $this->CashAdvance['ca_ea_code'] . ' - ' . $this->CashAdvance['ca_ea_description'],
            ],
            [
                'label' => Trans::getFinanceWord('date'),
                'value' => $dtParser->formatDate($this->CashAdvance['ca_date']),
            ],
            [
                'label' => Trans::getFinanceWord('receiver'),
                'value' => $this->CashAdvance['ca_cp_name'],
            ],
            [
                'label' => Trans::getFinanceWord('receiveOn'),
                'value' => $dtParser->formatDateTime($this->CashAdvance['ca_receive_on']),
            ],
            [
                'label' => Trans::getFinanceWord('settlementDate'),
                'value' => $dtParser->formatDateTime($this->CashAdvance['ca_settlement_on']),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $title = $this->CashAdvance['ca_number'];
        $title .= ' ( ' . $this->CashAdvance['ca_currency'] . ' ' . $number->doFormatFloat($totalCa) . ' )';
        $portlet = new Portlet('JoCaPtl', $title);
        if ($this->isUserJobManager() === true) {
            $url = url('/ca/detail?ca_id=' . $this->CashAdvance['ca_id']);
            $caButton = new HyperLink('BtnJoCa', Trans::getFinanceWord('cashPayment'), $url);
            $caButton->viewAsButton();
            $caButton->setIcon(Icon::Eye)->btnSuccess()->pullRight()->btnMedium();
            $portlet->addButton($caButton);
        }
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

    /**
     * Function to get the purchase Field Set.
     *
     * @return Portlet
     */
    protected function getDepositPortlet(): Portlet
    {
        $table = new Table('JoJdTbl');
        $table->setHeaderRow([
            'jd_number' => Trans::getFinanceWord('number'),
            'jd_relation' => Trans::getFinanceWord('relation'),
            'jd_cc_name' => Trans::getFinanceWord('description'),
            'jd_ref' => Trans::getFinanceWord('reference'),
            'jd_amount' => Trans::getFinanceWord('amount'),
            'jd_date' => Trans::getFinanceWord('date'),
            'jd_status' => Trans::getFinanceWord('status'),
            'jd_action' => Trans::getFinanceWord('view'),
        ]);
        $rows = [];
        $wheres = [];
        $wheres[] = '(jd.jd_deleted_on IS NULL)';
        $wheres[] = '(jd.jd_jo_id = ' . $this->getDetailReferenceValue() . ')';
        $data = JobDepositDao::loadData($wheres);
        $dtParser = new DateTimeParser();
        $jdDao = new JobDepositDao();
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $url = url('/jd/detail?jd_id=' . $row['jd_id']);
            $jdBtn = new HyperLink('JoJdBtn' . $row['jd_id'], '', $url);
            $jdBtn->viewAsButton();
            $jdBtn->setIcon(Icon::Eye)->btnSuccess()->viewIconOnly();
            $row['jd_action'] = $jdBtn;
            $row['jd_ref'] = StringFormatter::generateKeyValueTableView([
                [
                    'label' => Trans::getFinanceWord('relation'),
                    'value' => $row['jd_rel_ref'],
                ],
                [
                    'label' => Trans::getFinanceWord('payment'),
                    'value' => $row['jd_paid_ref'],
                ],
                [
                    'label' => Trans::getFinanceWord('settlement'),
                    'value' => $row['jd_settle_ref'],
                ],
            ]);
            $row['jd_date'] = StringFormatter::generateKeyValueTableView([
                [
                    'label' => Trans::getFinanceWord('deposit'),
                    'value' => $dtParser->formatDate($row['jd_date']),
                ],
                [
                    'label' => Trans::getFinanceWord('refund'),
                    'value' => $dtParser->formatDate($row['jd_return_date']),
                ],
            ]);
            $amounts = [
                [
                    'label' => Trans::getFinanceWord('deposit'),
                    'value' => $number->doFormatFloat((float)$row['jd_amount']),
                ],
                [
                    'label' => Trans::getFinanceWord('claim'),
                    'value' => $number->doFormatFloat((float)$row['jd_claim_amount']),
                ],
            ];
            if (empty($row['jd_settle_on']) === false) {
                $refund = (float)$row['jd_amount'] - (float)$row['jd_claim_amount'];
                $amounts[] = [
                    'label' => Trans::getFinanceWord('refund'),
                    'value' => $number->doFormatFloat($refund),
                ];
            }
            $row['jd_amount'] = StringFormatter::generateKeyValueTableView($amounts);

            $row['jd_status'] = $jdDao->generateStatus([
                'is_deleted' => !empty($row['jd_deleted_on']),
                'is_return' => !empty($row['jd_return_on']),
                'is_settle' => !empty($row['jd_settle_on']),
                'is_paid' => !empty($row['jd_paid_on']),
                'is_approved' => !empty($row['jd_approved_on']),
                'is_requested' => !empty($row['jda_id']),
                'is_rejected' => !empty($row['jda_deleted_on']),
            ]);
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->addColumnAttribute('jd_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jd_status', 'style', 'text-align: center;');
        $table->addColumnAttribute('jd_action', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('JoJdPtl', Trans::getFinanceWord('deposit'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to add all default portlet
     *
     * @return void
     */
    protected function includeAllDefaultPortlet(): void
    {
        if ($this->isAllowToSeeSalesInformation()) {
            $this->Tab->addPortlet('finance', $this->getSalesFieldSet());
        }
        if ($this->isAllowToSeePurchaseInformation()) {
            $this->Tab->addPortlet('finance', $this->getPurchaseFieldSet());
            if ($this->isAllowToSeeDepositInformation()) {
                $this->Tab->addPortlet('finance', $this->getDepositPortlet());
            }
            if (empty($this->CashAdvance) === false) {
                $this->Tab->addPortlet('finance', $this->getCashAdvancePortlet());
            }
        }
        if ($this->isAllowToSeeMarginInformation()) {
            $this->Tab->addPortlet('finance', $this->getFinanceMarginFieldSet());
        }
        // # document tab
        $this->Tab->addPortlet('document', $this->getDocumentFieldSet());
        if ($this->isValidParameter('jo_publish_on') === true) {
            $this->Tab->addPortlet('timeSheet', $this->getTimeSheetFieldSet());
        }
        $this->setJoHiddenData();
        $this->setSoHiddenData();
    }

    /**
     * Function to check if user is customer
     *
     * @return bool
     */
    protected function isCustomerUser(): bool
    {
        return $this->User->getRelId() === $this->getIntParameter('jo_rel_id');
    }

    /**
     * Function to check if user is vendor
     *
     * @return bool
     */
    protected function isVendorUser(): bool
    {
        return $this->User->getRelId() === $this->getIntParameter('jo_vendor_id');
    }

    /**
     * Function to check if user is third party user.
     *
     * @return bool
     */
    protected function isThirdPartyUser(): bool
    {
        return $this->PageSetting->checkPageRight('ThirdPartyAccess');
    }

    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function setJoHiddenData(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('joh_id', $this->getIntParameter('joh_id'));
        $content .= $this->Field->getHidden('jo_publish_on', $this->getStringParameter('jo_publish_on'));
        $content .= $this->Field->getHidden('jo_finish_on', $this->getStringParameter('jo_finish_on'));
        $content .= $this->Field->getHidden('jo_order_of_id', $this->getIntParameter('jo_order_of_id', $this->User->Relation->getOfficeId()));
        $content .= $this->Field->getHidden('jo_srv_id', $this->getIntParameter('jo_srv_id'));
        $content .= $this->Field->getHidden('jo_srv_code', $this->getStringParameter('jo_srv_code'));
        $content .= $this->Field->getHidden('jo_srt_id', $this->getIntParameter('jo_srt_id'));
        $content .= $this->Field->getHidden('jo_srt_pol', $this->getStringParameter('jo_srt_pol'));
        $content .= $this->Field->getHidden('jo_srt_pod', $this->getStringParameter('jo_srt_pod'));
        $content .= $this->Field->getHidden('jo_srt_container', $this->getStringParameter('jo_srt_container'));
        $content .= $this->Field->getHidden('jo_srt_route', $this->getStringParameter('jo_srt_route'));
        $content .= $this->Field->getHidden('jo_srt_load', $this->getStringParameter('jo_srt_load'));
        $content .= $this->Field->getHidden('jo_srt_unload', $this->getStringParameter('jo_srt_unload'));
        $content .= $this->Field->getHidden('jo_jtr_id', $this->getIntParameter('jo_jtr_id'));
        $content .= $this->Field->getHidden('jo_number', $this->getStringParameter('jo_number'));
        $content .= $this->Field->getHidden('jo_customer', $this->getStringParameter('jo_customer'));
        $content .= $this->Field->getHidden('jo_customer_ref', $this->getStringParameter('jo_customer_ref'));
        $content .= $this->Field->getHidden('jo_service_term', $this->getStringParameter('jo_service_term'));
        $content .= $this->Field->getHidden('jo_manager_id', $this->getIntParameter('jo_manager_id'));
        $this->View->addContent('JoHdFld', $content);

    }

    /**
     * Function to set so hidden data.
     *
     * @return void
     */
    private function setSoHiddenData(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('so_id', $this->getIntParameter('so_id'));
        $content .= $this->Field->getHidden('so_start_on', $this->getStringParameter('so_start_on'));
        $content .= $this->Field->getHidden('so_plb', $this->getStringParameter('so_plb'));
        $content .= $this->Field->getHidden('so_customer_ref', $this->getStringParameter('jo_customer_ref'));
        $this->View->addContent('SoHdFld', $content);

    }

    protected function doExportTimeShtXls(): void
    {
        $excel = new Excel();
        $excel->addSheet('timeSheet', Trans::getWord('timeSheet'));
        $excel->setFileName($this->getStringParameter('jo_number') . ' ' . Trans::getWord('timeSheet') . '.xlsx');
        $sheet = $excel->getSheet('timeSheet', true);
        $portlet = $this->getTimeSheetFieldSet();
        $excelTable = new ExcelTable($excel, $sheet);
        $excelTable->setTable($portlet->Body[0]);
        $excelTable->writeTable();
        $excel->setActiveSheet('timeSheet');
        $excel->createExcel();
    }

    /**
     * Function to check is this container job or not
     *
     * @return bool
     */
    protected function isContainerJob(): bool
    {
        return $this->getStringParameter('jo_srt_container', 'N') === 'Y';
    }

    /**
     * Function to check is valid SO Id
     *
     * @return bool
     */
    protected function isValidSoId(): bool
    {
        return $this->isValidParameter('so_id');
    }

    /**
     * Function to check is so started
     *
     * @return bool
     */
    protected function isSoInProgress(): bool
    {
        return $this->isValidParameter('so_start_on');
    }

    /**
     * Function to get so Id
     *
     * @return int
     */
    protected function getSoId(): ?int
    {
        return $this->getIntParameter('so_id');
    }

    /**
     * Function to get so Id
     *
     * @return void
     */
    protected function setOfficerParameter(): void
    {
        $officer = 'N';
        if ($this->User->getId() === $this->getIntParameter('jo_manager_id')) {
            $officer = 'Y';
        } else {
            $data = JobOfficerDao::getByJobOrderAndUser($this->getDetailReferenceValue(), $this->User->getId());
            if (empty($data) === false) {
                $officer = 'Y';
            }
        }
        $this->setParameter('jo_officer', $officer);
    }

    /**
     * Function do generate notification receiver
     *
     * @param string $notificationCode The notification code.
     *
     * @return void
     */
    protected function doGenerateNotificationReceiver(string $notificationCode = ''): void
    {
        # The job manager
        $mainReceiver = [];
        $manager = UserMappingDao::getByUserIdAndSystemId($this->getIntParameter('jo_manager_id'), $this->User->getSsId());
        if (empty($manager) === false) {
            $mainReceiver[] = $manager['ump_cp_id'];
        }
        # Get officer user
        $officers = JobOfficerDao::loadByJobOrderIdAndSystemSettings($this->getDetailReferenceValue(), $this->User->getSsId());
        foreach ($officers as $officer) {
            $mainReceiver[] = $officer['joo_cp_id'];
        }
        # Get user group notification
        $jobDao = new JobOrderDao();
        $moduleNotification = $jobDao->getJobNotificationModule($this->getStringParameter('jo_srv_code'));
        $jnrData = JobNotificationReceiverDao::loadDataByUserGroupNotification($this->getDetailReferenceValue(), $notificationCode, $moduleNotification, $this->User->getSsId());
        $receiverExist = [];
        foreach ($jnrData as $row) {
            $receiverExist[] = $row['jnr_cp_id'];
        }
        # Merge receiver
        $receivers = array_unique(array_merge($mainReceiver, $receiverExist));
        # Process notification
        if (empty($notificationCode) === false) {
            $nf = new JobNotificationBuilder($this, $notificationCode, $moduleNotification, $receivers);
            $nf->doNotify();
        }
    }


    /**
     * Function to get storage delete modal.
     *
     * @return bool
     */
    protected function isAllowToSeeDepositInformation(): bool
    {
        return $this->PageSetting->checkPageRight('AllowSeeDeposit');
    }

    /**
     * Function to add all default portlet
     *
     * @return bool
     */
    protected function isUserJobManager(): bool
    {
        return $this->getIntParameter('jo_manager_id') === $this->User->getId();
    }

}
