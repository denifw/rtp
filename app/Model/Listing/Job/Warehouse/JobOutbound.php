<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Listing\Job\Warehouse;

use App\Frame\Document\ParseExcel;
use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Job\JobActionDao;
use App\Model\Dao\Job\JobGoodsDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Dao\Master\WarehouseDao;
use App\Model\Dao\Relation\ContactPersonDao;
use App\Model\Dao\Relation\OfficeDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Dao\Setting\Action\SystemActionDao;
use App\Model\Dao\System\Service\ServiceTermDao;
use App\Model\Listing\Job\BaseJobOrder;

/**
 * Class to control the system of JobOutbound.
 *
 * @package    app
 * @subpackage Model\Listing\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobOutbound extends BaseJobOrder
{

    /**
     * JobOutbound constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhOutbound', $parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'jo_customer', $this->getStringParameter('jo_customer'), 'loadGoodsOwnerData');
        $relField->setHiddenField('jo_rel_id', $this->getIntParameter('jo_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setEnableDetailButton(false);
        $relField->setEnableNewButton(false);
        # Warehouse Field
        $whField = $this->Field->getSingleSelect('warehouse', 'job_warehouse', $this->getStringParameter('job_warehouse'));
        $whField->setHiddenField('job_wh_id', $this->getIntParameter('job_wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);

        $statusField = $this->Field->getSelect('jo_status', $this->getStringParameter('jo_status'));
        $statusField->addOption(Trans::getWord('draft'), '1');
        $statusField->addOption(Trans::getWord('publish'), '2');
        $statusField->addOption(Trans::getWord('inProgress'), '3');
        $statusField->addOption(Trans::getWord('complete'), '4');
        $statusField->addOption(Trans::getWord('canceled'), '5');
        $statusField->addOption(Trans::getWord('hold'), '6');

        $consigneeField = $this->Field->getSingleSelect('relation', 'job_consignee', $this->getStringParameter('job_consignee'));
        $consigneeField->setHiddenField('job_rel_id', $this->getIntParameter('job_rel_id'));
        $consigneeField->addParameter('rel_ss_id', $this->User->getSsId());
        $consigneeField->setEnableDetailButton(false);
        $consigneeField->setEnableNewButton(false);


        $this->ListingForm->addField(Trans::getWord('jobNumber'), $this->Field->getText('jo_number', $this->getStringParameter('jo_number')));
        $this->ListingForm->addField(Trans::getWord('arriveDateFrom'), $this->Field->getCalendar('arrive_date_from', $this->getStringParameter('arrive_date_from')));
        $this->ListingForm->addField(Trans::getWord('loadDateFrom'), $this->Field->getCalendar('load_date_from', $this->getStringParameter('load_date_from')));
        $this->ListingForm->addField(Trans::getWord('completeDateFrom'), $this->Field->getCalendar('complete_date_from', $this->getStringParameter('complete_date_from')));
        $this->ListingForm->addField(Trans::getWord('warehouse'), $whField);
        $this->ListingForm->addField(Trans::getWord('arriveDateUntil'), $this->Field->getCalendar('arrive_date_until', $this->getStringParameter('arrive_date_until')));
        $this->ListingForm->addField(Trans::getWord('loadDateUntil'), $this->Field->getCalendar('load_date_until', $this->getStringParameter('load_date_until')));
        $this->ListingForm->addField(Trans::getWord('completeDateUntil'), $this->Field->getCalendar('complete_date_until', $this->getStringParameter('complete_date_until')));
        $this->ListingForm->addField(Trans::getWord('customer'), $relField);
        $this->ListingForm->addField(Trans::getWord('reference'), $this->Field->getText('jo_reference', $this->getStringParameter('jo_reference')));
        $this->ListingForm->addField(Trans::getWord('consignee'), $consigneeField);
        $this->ListingForm->addField(Trans::getWord('status'), $statusField);
    }


    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        # set header column table
        $this->ListingTable->setHeaderRow([
            'jo_number' => Trans::getWord('jobNumber'),
            'jo_customer' => Trans::getWord('customer'),
            'jo_customer_ref' => Trans::getWord('customerRef'),
            'jo_order_date' => Trans::getWord('orderDate'),
            'job_warehouse' => Trans::getWord('warehouse'),
            'job_consignee' => Trans::getWord('consignee'),
            'job_ata' => Trans::getWord('ata'),
            'jo_status' => Trans::getWord('lastStatus'),
        ]);
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['jo_id']);
        $this->ListingTable->addColumnAttribute('jo_status', 'style', 'text-align: center');
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['jo_id']);
        }

    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return JobOutboundDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = JobOutboundDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());

        return $this->doPrepareData($data);
    }

    /**
     * Function to do prepare data.
     *
     * @param array $data To store the data.
     *
     * @return array
     */
    private function doPrepareData(array $data): array
    {
        $result = [];
        $joDao = new JobOrderDao();
        foreach ($data as $row) {
            $ata = '';
            if (empty($row['job_ata_date']) === false) {
                if (empty($row['job_ata_time']) === false) {
                    $ata = DateTimeParser::format($row['job_ata_date'] . ' ' . $row['job_ata_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
                } else {
                    $ata = DateTimeParser::format($row['job_ata_date'], 'Y-m-d', 'd M Y');
                }
            }
            $row['job_ata'] = $ata;
            if (empty($row['jo_order_date']) === false) {
                $row['jo_order_date'] = DateTimeParser::format($row['jo_order_date'], 'Y-m-d', 'd M Y');
            }
            # References
            $references = [
                [
                    'label' => 'Truck Number',
                    'value' => $row['job_truck_number'],
                ],
                [
                    'label' => 'Container',
                    'value' => $row['job_container_number'],
                ],
                [
                    'label' => 'Seal',
                    'value' => $row['job_seal_number'],
                ]
            ];
            $row['jo_customer_ref'] = $joDao->concatReference($row, 'jo', $references);

            $row['jo_status'] = $joDao->generateStatus([
                'is_hold' => !empty($row['joh_id']),
                'is_deleted' => !empty($row['jo_deleted_on']),
                'is_finish' => !empty($row['jo_finish_on']),
                'is_document' => !empty($row['jo_document_on']),
                'is_start' => !empty($row['jo_start_on']),
                'jac_id' => $row['jo_action_id'],
                'jae_style' => $row['jo_action_style'],
                'jac_action' => $row['jo_action'],
                'jo_srt_id' => $row['jo_srt_id'],
                'is_publish' => !empty($row['jo_publish_on']),
            ]);
            $result[] = $row;
        }

        return $result;

    }

    /**
     * Function to get the where condition.
     *
     * @return array
     */
    private function getWhereCondition(): array
    {
        # Set where conditions
        $wheres = $this->getJoConditions();
        if ($this->isValidParameter('arrive_date_from') === true) {
            if ($this->isValidParameter('arrive_date_until') === true) {
                $wheres[] = "(job.job_ata_date >= '" . $this->getStringParameter('arrive_date_from') . "')";
            } else {
                $wheres[] = "(job.job_ata_date = '" . $this->getStringParameter('arrive_date_from') . "')";
            }
        }
        if ($this->isValidParameter('arrive_date_until') === true) {
            if ($this->isValidParameter('arrive_date_from') === true) {
                $wheres[] = "(job.job_ata_date <= '" . $this->getStringParameter('arrive_date_until') . "')";
            } else {
                $wheres[] = "(job.job_ata_date = '" . $this->getStringParameter('arrive_date_until') . "')";
            }
        }

        if ($this->isValidParameter('load_date_from') === true) {
            if ($this->isValidParameter('load_date_until') === true) {
                $wheres[] = "(job.job_start_load_on >= '" . $this->getStringParameter('load_date_from') . " 00:01:00')";
            } else {
                $wheres[] = "(job.job_start_load_on >= '" . $this->getStringParameter('load_date_from') . " 00:01:00')";
                $wheres[] = "(job.job_start_load_on <= '" . $this->getStringParameter('load_date_from') . " 23:59:00')";
            }
        }
        if ($this->isValidParameter('load_date_until') === true) {
            if ($this->isValidParameter('load_date_from') === true) {
                $wheres[] = "(job.job_start_load_on <= '" . $this->getStringParameter('load_date_until') . " 23:59:00')";
            } else {
                $wheres[] = "(job.job_start_load_on >= '" . $this->getStringParameter('load_date_until') . " 00:01:00')";
                $wheres[] = "(job.job_start_load_on <= '" . $this->getStringParameter('load_date_until') . " 23:59:00')";
            }
        }

        if ($this->isValidParameter('job_rel_id') === true) {
            $wheres[] = '(job.job_rel_id = ' . $this->getIntParameter('job_rel_id') . ')';
        }

        if ($this->isValidParameter('job_wh_id') === true) {
            $wheres[] = '(job.job_wh_id = ' . $this->getIntParameter('job_wh_id') . ')';
        }
        return $wheres;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        parent::loadDefaultButton();
        if ($this->PageSetting->checkPageRight('AllowImportExcel') === true) {
            $modal = $this->getUploadJobFromExcelModal();
            $this->View->addModal($modal);
            $btnUpload = new ModalButton('btnMdlUpExl', Trans::getWord('uploadJob'), $modal->getModalId());
            $btnUpload->setIcon(Icon::FileExcelO)->btnSuccess()->pullRight();
            $this->View->addButtonAtTheBeginning($btnUpload);
        }
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getUploadJobFromExcelModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JobUpExlMdl', Trans::getWord('uploadJob'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUploadJobExcel');
        $showModal = false;
        if ($this->getFormAction() === 'doUploadJobExcel' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 12);
        $whField = $this->Field->getSingleSelect('warehouse', 'warehouse', $this->getParameterForModal('warehouse', $showModal));
        $whField->setHiddenField('wh_id', $this->getParameterForModal('wh_id', $showModal));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableDetailButton(false);
        $whField->setEnableNewButton(false);
        $relationField = $this->Field->getSingleSelect('relation', 'rel_name', $this->getParameterForModal('rel_name', $showModal));
        $relationField->setHiddenField('rel_id', $this->getParameterForModal('rel_id', $showModal));
        $relationField->setDetailReferenceCode('rel_id');
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setEnableNewButton(false);
        $relationField->setEnableDetailButton(false);
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('warehouse'), $whField, true);
        $fieldSet->addField(Trans::getWord('ecommerce'), $relationField, true);
        $fieldSet->addField(Trans::getWord('file'), $this->Field->getFile('job_out_file', $this->getParameterForModal('job_out_file', $showModal)), true);

        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to export data into excel file.
     *
     * @return void
     */
    public function doUploadJobExcel(): void
    {
        if ($this->isValidParameter('wh_id') === false) {
            $this->addErrorMessage(Trans::getWord('required', 'validation', '', [
                'attribute' => 'Warehouse'
            ]));
        }
        if ($this->isValidParameter('rel_id') === false) {
            $this->addErrorMessage(Trans::getWord('required', 'validation', '', [
                'attribute' => 'Ecommerce'
            ]));
        }
        if ($this->isValidParameter('job_out_file') === false) {
            $this->addErrorMessage(Trans::getWord('required', 'validation', '', [
                'attribute' => 'File'
            ]));
        }
        $file = $this->getFileParameter('job_out_file');
        if ($file !== null) {
            $resource = mb_strtolower($this->getStringParameter('rel_name'));
            $headers = [];
            $startingRows = '';
            $sheetName = '';
            if ($resource === 'tokopedia') {
                $headers = [
                    'invoice' => 'C',
                    'payment_date' => 'D',
                    'product_id' => 'I',
                    'quantity' => 'H',
                    'recipient' => 'N',
                    'recipient_number' => 'O',
                    'recipient_address' => 'P',
                    'courier' => 'Q',
                ];
                $startingRows = '5';
                $sheetName = 'Sheet1';
            } elseif ($resource === 'shopee') {
                $headers = [
                    'invoice' => 'A',
                    'payment_date' => 'J',
                    'product_id' => 'K',
                    'quantity' => 'W',
                    'recipient' => 'AM',
                    'recipient_number' => 'AN',
                    'recipient_address' => 'AO',
                    'courier' => 'E',
                ];
                $startingRows = '2';
                $sheetName = 'orders';
            } elseif ($resource === 'web klikseafood.com') {
                $headers = [
                    'invoice' => 'C',
                    'payment_date' => 'D',
                    'product_id' => 'I',
                    'quantity' => 'H',
                    'recipient' => 'N',
                    'recipient_number' => 'O',
                    'recipient_address' => 'P',
                    'courier' => 'Q',
                ];
                $startingRows = '5';
                $sheetName = 'Sheet1';
            }
            $fileName = 'job_' . time() . '.' . $file->getClientOriginalExtension();
            $parseExcel = new ParseExcel($file, $fileName, $sheetName);
            $parseExcel->setHeaderRow($headers);
            if ($parseExcel->IsSuccessStored !== false && empty($headers) === false && empty($startingRows) === false) {
                $data = $parseExcel->getAllSheetCells($startingRows);
                # Do validate data
                $errors = $this->doValidateDataImportExcel($data);
                if (empty($errors) === true) {
                    $invoiceList = [];
                    $jobIds = [];
                    foreach ($data as $row) {
                        # Check is invoice number in customer reff job
                        $isCustomerRefExist = JobOrderDao::isCustomerRefExist($row['invoice']);
                        if ($isCustomerRefExist === false) {
                            if (in_array($row['invoice'], $invoiceList, true) === false) {
                                $invoiceList[] = $row['invoice'];
                                # Process customer
                                $customerData = $this->doProcessCustomer($row);
                                # Process transporter
                                $transporterData = $this->doProcessTransporter($row);
                                # Process Job Order
                                if (empty($customerData) === false && empty($transporterData) === false) {
                                    $jobId = $this->doProcessJob($row);
                                    $jobIds[$row['invoice']] = $jobId;
                                    # Process Job Outbound
                                    $this->doProcessJobOutbound($jobId, $customerData, $transporterData, $row);
                                    # Process Job Goods
                                    $actions = SystemActionDao::getByServiceTermIdAndSystemId(2, $this->User->getSsId());
                                    # Insert Job Action
                                    $jacDao = new JobActionDao();
                                    $i = 1;
                                    foreach ($actions as $action) {
                                        $jacColVal = [
                                            'jac_jo_id' => $jobId,
                                            'jac_ac_id' => $action['sac_ac_id'],
                                            'jac_order' => $i,
                                            'jac_active' => 'Y',
                                        ];
                                        $jacDao->doInsertTransaction($jacColVal);
                                        $i++;
                                    }
                                    # Insert Job Goods
                                    $this->doProcessJobGoods($jobId, $row);
                                }
                            }
                        } elseif ($isCustomerRefExist === true && empty($jobIds[$row['invoice']]) === false) {
                            $this->doProcessJobGoods($jobIds[$row['invoice']], $row);
                        }
                    }
                } else {
                    $this->View->addErrorMessage(implode('<br />', $errors));
                }
            } else {
                Message::throwMessage(Trans::getWord('unableUploadJogExcel'), 'ERROR');
            }
        }
    }

    /**
     * Function to validate data excel before insert.
     *
     * @param array $data
     *
     * @return array
     */
    private function doValidateDataImportExcel(array $data): array
    {
        $errors = [];
        foreach ($data as $row) {
            if (empty($row['recipient']) === true || empty($row['recipient_number']) === true) {
                $errors[] = Trans::getWord('invalidRecipientImport', 'message', '', [
                    'invoice' => $row['invoice'],
                    'line_number' => $row['line_number'],
                ]);
            }
            if (empty($row['invoice']) === true || empty($row['payment_date']) === true) {
                $errors[] = Trans::getWord('invalidJobOutboundImport', 'message', '', [
                    'invoice' => $row['invoice'],
                    'line_number' => $row['line_number'],
                ]);
            }
            if (empty($row['product_id']) === false) {
                $goodsData = GoodsDao::getBySkuAndSsId($row['product_id'], $this->User->getSsId());
                if (empty($goodsData) === true) {
                    $errors[] = Trans::getWord('invalidJobGoodsImport', 'message', '', [
                        'sku' => $row['product_id'],
                        'invoice' => $row['invoice'],
                        'line_number' => $row['line_number'],
                    ]);
                }
            } else {
                $errors[] = Trans::getWord('emptySkuProduct', 'message', '', [
                    'invoice' => $row['invoice'],
                    'line_number' => $row['line_number'],
                ]);
            }
        }

        return $errors;
    }

    /**
     * Function do process job.
     *
     * @param int $idJobOrder
     * @param array $data
     *
     * @return void
     */
    private function doProcessJobGoods(int $idJobOrder, array $data): void
    {
        if (empty($data['product_id']) === false) {
            $goodsData = GoodsDao::getBySkuAndSsId($data['product_id'], $this->User->getSsId());
            if (empty($goodsData) === false) {
                $colVal = [
                    'jog_jo_id' => $idJobOrder,
                    'jog_gd_id' => $goodsData['gd_id'],
                    'jog_name' => $data['product_id'],
                    'jog_quantity' => $data['quantity'],
                    'jog_gdu_id' => $goodsData['gdu_id']
                ];
                $jogDao = new JobGoodsDao();
                $jogDao->doInsertTransaction($colVal);
            }
        } else {
            $errors[] = Trans::getWord('emptySkuProduct', 'message', '', [
                'invoice' => $data['invoice'],
                'line_number' => $data['line_number'],
            ]);
        }
    }

    /**
     * Function do process job.
     *
     * @param int $idJobOrder
     * @param array $customerData
     * @param array $transporterData
     * @param array $data
     *
     * @return void
     */
    private function doProcessJobOutbound(int $idJobOrder, array $customerData, array $transporterData, array $data): void
    {
        $inFormat = '';
        $resource = mb_strtolower($this->getStringParameter('rel_name'));
        if ($resource === 'tokopedia') {
            $inFormat = 'd-m-Y H:i:s';
        } elseif ($resource === 'shopee') {
            $inFormat = 'Y-m-d H:i';
        } elseif ($resource === 'web klikseafood.com') {
            $inFormat = 'd-m-Y H:i:s';
        }
        $etaDate = DateTimeParser::format($data['payment_date'], $inFormat, 'Y-m-d');
        $etaTime = DateTimeParser::format($data['payment_date'], $inFormat, 'H:i:s');
        $colVal = [
            'job_jo_id' => $idJobOrder,
            'job_wh_id' => $this->getIntParameter('wh_id'),
            'job_eta_date' => $etaDate,
            'job_eta_time' => $etaTime,
            'job_rel_id' => $customerData['rel_id'],
            'job_of_id' => $customerData['of_id'],
            'job_cp_id' => $customerData['cp_id'],
            'job_vendor_id' => $transporterData['rel_id'],
        ];
        $jobOutDao = new JobOutboundDao();
        $jobOutDao->doInsertTransaction($colVal);
    }

    /**
     * Function do process job.
     *
     * @param array $data
     *
     * @return int
     */
    private function doProcessJob(array $data): int
    {
        $sn = new SerialNumber($this->User->getSsId());
        $number = $sn->loadNumber('JobOrder', $this->User->Relation->getOfficeId(), $this->User->getRelId(), 1, 2);
        $whData = WarehouseDao::getByReference($this->getIntParameter('wh_id'));
        $inFormat = '';
        $resource = mb_strtolower($this->getStringParameter('rel_name'));
        if ($resource === 'tokopedia') {
            $inFormat = 'd-m-Y H:i:s';
        } elseif ($resource === 'shopee') {
            $inFormat = 'Y-m-d H:i';
        } elseif ($resource === 'web klikseafood.com') {
            $inFormat = 'd-m-Y H:i:s';
        }
        $srt = ServiceTermDao::getByRoute('joWhOutbound');
        $etaDate = DateTimeParser::format($data['payment_date'], $inFormat, 'Y-m-d');
        $colVal = [
            'jo_ss_id' => $this->User->getSsId(),
            'jo_number' => $number,
            'jo_srv_id' => $srt['srt_srv_id'],
            'jo_srt_id' => $srt['srt_id'],
            'jo_order_date' => $etaDate,
            'jo_rel_id' => $this->User->getRelId(),
            'jo_customer_ref' => $data['invoice'],
            'jo_order_of_id' => $whData['wh_of_id'],
        ];
        $jobDao = new JobOrderDao();
        $jobDao->doInsertTransaction($colVal);

        return $jobDao->getLastInsertId();
    }

    /**
     * Function do process customer
     *
     * @param array $data
     *
     * @return array
     */
    private function doProcessCustomer(array $data): array
    {
        $idCustomer = $this->getIntParameter('rel_id');
        # Check Customer exist by phone number
        $customerData = RelationDao::loadByCpNameAndNumber($this->User->getSsId(), $idCustomer, $data['recipient'], $data['recipient_number']);
        if (empty($customerData) === true) {
            # Insert office
            $colOff = [
                'of_rel_id' => $idCustomer,
                'of_name' => $data['recipient'],
                'of_main' => 'N',
                'of_invoice' => 'N',
                'of_address' => $data['recipient_address']
            ];
            $offDao = new OfficeDao();
            $offDao->doInsertTransaction($colOff);
            $idOff = $offDao->getLastInsertId();
            # Insert contact person
            $sn = new SerialNumber($this->User->getSsId());
            $cpNumber = $sn->loadNumber('ContactPerson', $idOff, $idCustomer);
            $colCp = [
                'cp_number' => $cpNumber,
                'cp_name' => $data['recipient'],
                'cp_phone' => $data['recipient_number'],
                'cp_of_id' => $idOff
            ];
            $cpDao = new ContactPersonDao();
            $cpDao->doInsertTransaction($colCp);
            $idCp = $cpDao->getLastInsertId();
            $customerData = [
                'rel_id' => $idCustomer,
                'of_id' => $idOff,
                'cp_id' => $idCp,
            ];
        }


        return $customerData;
    }

    /**
     * Function do process transporter
     *
     * @param array $data
     *
     * @return array
     */
    private function doProcessTransporter(array $data): array
    {
        # If courier use the relation of online sho/ecommerce as transporter.
        if (empty($data['courier']) === false) {
            # Check is transporter exist
            $transporterData = RelationDao::loadByName($this->User->getSsId(), $data['courier']);
            if (empty($transporterData) === true) {
                $sn = new SerialNumber($this->User->getSsId());
                $relNumber = $sn->loadNumber('Relation', $this->User->Relation->getOfficeId());
                $colRel = [
                    'rel_ss_id' => $this->User->getSsId(),
                    'rel_number' => $relNumber,
                    'rel_name' => $data['courier'],
                    'rel_short_name' => $data['courier'],
                ];
                $relDao = new RelationDao();
                $relDao->doInsertTransaction($colRel);
                $idRel = $relDao->getLastInsertId();
                # Insert office
                $colOff = [
                    'of_rel_id' => $idRel,
                    'of_name' => $data['courier'],
                    'of_main' => 'N',
                    'of_invoice' => 'N',
                    'of_address' => ''
                ];
                $ofDao = new OfficeDao();
                $ofDao->doInsertTransaction($colOff);
                $idOff = $ofDao->getLastInsertId();
                # Insert contact person
                $sn = new SerialNumber($this->User->getSsId());
                $cpNumber = $sn->loadNumber('ContactPerson', $idOff, $idRel);
                $colCp = [
                    'cp_number' => $cpNumber,
                    'cp_name' => $data['courier'],
                    'cp_of_id' => $idOff
                ];
                $cpDao = new ContactPersonDao();
                $cpDao->doInsertTransaction($colCp);
                $idCp = $cpDao->getLastInsertId();
                $transporterData = [
                    'rel_id' => $idRel,
                    'of_id' => $idOff,
                    'cp_id' => $idCp,
                ];
            }
        } else {
            $transporterData = [
                'rel_id' => $this->getIntParameter('rel_id')
            ];
        }

        return $transporterData;
    }
}
