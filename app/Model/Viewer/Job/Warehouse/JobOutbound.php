<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Viewer\Job\Warehouse;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Job\JobGoodsDao;
use App\Model\Dao\Job\Warehouse\Bundling\JobBundlingDao;
use App\Model\Dao\Job\Warehouse\JobInboundStockDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDetailDao;
use App\Model\Dao\Job\Warehouse\JobStockTransferDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Viewer\Job\BaseJobOrder;

/**
 * Class to handle the creation of detail JobOutbound page
 *
 * @package    app
 * @subpackage Model\Viewer\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobOutbound extends BaseJobOrder
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhOutbound', 'jo_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doActionStartPick') {
            # Update start Job
            $this->doStartJobOrder();
            if ($this->isValidParameter('jo_jtr_id') === true) {
                $jtrDao = new JobStockTransferDao();
                $jtrDao->doUpdateTransaction($this->getIntParameter('jo_jtr_id'), [
                    'jtr_start_out_on' => date('Y-m-d H:i:s'),
                ]);
            }
            # Update start store job.
            $jobColVal = [
                'job_start_store_on' => date('Y-m-d H:i:s'),
            ];
            $jobDao = new JobOutboundDao();
            $jobDao->doUpdateTransaction($this->getIntParameter('job_id'), $jobColVal);
            # Update job Action
            $this->doUpdateJobAction(1);
            # Do notification
            $this->doGenerateNotificationReceiver('outboundstartpick');
        } elseif ($this->getFormAction() === 'doInsertStorage') {
            $jodColVal = [
                'jod_job_id' => $this->getIntParameter('job_id'),
                'jod_jog_id' => $this->getIntParameter('jod_jog_id'),
                'jod_jid_id' => $this->getIntParameter('jod_jid_id'),
                'jod_whs_id' => $this->getIntParameter('jod_whs_id'),
                'jod_gd_id' => $this->getIntParameter('jod_gd_id'),
                'jod_gdu_id' => $this->getIntParameter('jod_gdu_id'),
                'jod_quantity' => $this->getFloatParameter('jod_quantity'),
                'jod_lot_number' => $this->getStringParameter('jod_lot_number'),
            ];
            $jodDao = new JobOutboundDetailDao();
            $jodDao->doInsertTransaction($jodColVal);
        } elseif ($this->getFormAction() === 'doDeleteStorage') {
            $data = JobOutboundDetailDao::getByReference($this->getIntParameter('jod_id_del'));
            if (empty($data) === false && empty($data['jod_jis_id']) === false && (int)$data['jod_jis_id'] > 0) {
                $jisDao = new JobInboundStockDao();
                $jisDao->doDeleteTransaction($data['jod_jis_id']);
            }
            $jodDao = new JobOutboundDetailDao();
            $jodDao->doDeleteTransaction($this->getIntParameter('jod_id_del'));
        } elseif ($this->getFormAction() === 'doLoadGoods') {
            $colValLoad = [
                'jod_qty_loaded' => $this->getFloatParameter('jodl_qty_loaded'),
            ];
            $jodDao = new JobOutboundDetailDao();
            $jodDao->doUpdateTransaction($this->getIntParameter('jodl_id'), $colValLoad);
        } elseif ($this->getFormAction() === 'doActionEndPick') {
            $jodData = JobOutboundDetailDao::loadSimpleDataByJobOutboundId($this->getIntParameter('job_id'));
            $jodDao = new JobOutboundDetailDao();
            $jisDao = new JobInboundStockDao();
            foreach ($jodData as $row) {
                if (empty($row['jod_jis_id']) === false) {
                    $jisId = $row['jod_jis_id'];
                    $jisDao->doUpdateTransaction($jisId, [
                        'jis_jid_id' => $row['jod_jid_id'],
                        'jis_quantity' => (float)$row['jod_quantity'] * -1,
                    ]);
                } else {
                    $jisDao->doInsertTransaction([
                        'jis_jid_id' => $row['jod_jid_id'],
                        'jis_quantity' => (float)$row['jod_quantity'] * -1,
                    ]);
                    $jisId = $jisDao->getLastInsertId();
                }

                $jodDao->doUpdateTransaction($row['jod_id'], [
                    'jod_jis_id' => $jisId,
                ]);
            }

            # Update start store job.
            $jobColVal = [
                'job_end_store_on' => date('Y-m-d H:i:s'),
            ];
            $jobDao = new JobOutboundDao();
            $jobDao->doUpdateTransaction($this->getIntParameter('job_id'), $jobColVal);
            # Update job Action
            $this->doUpdateJobAction(2);
            # Do notification
            $this->doGenerateNotificationReceiver('outboundcompletepick');
        } elseif ($this->getFormAction() === 'doActionArrive') {
            # Update actual time arrival job
            $jobColVal = [
                'job_ata_time' => $this->getStringParameter('job_ata_time'),
                'job_vendor_id' => $this->getStringParameter('job_vendor_id'),
                'job_driver' => $this->getStringParameter('job_driver'),
                'job_driver_phone' => $this->getStringParameter('job_driver_phone'),
                'job_ata_date' => $this->getStringParameter('job_ata_date'),
                'job_truck_number' => $this->getStringParameter('job_truck_number'),
                'job_container_number' => $this->getStringParameter('job_container_number'),
                'job_seal_number' => $this->getStringParameter('job_seal_number'),
            ];
            $jobDao = new JobOutboundDao();
            $jobDao->doUpdateTransaction($this->getIntParameter('job_id'), $jobColVal);
            # Update job Action
            $this->doUpdateJobAction();
            $this->doGenerateNotificationReceiver('jobtruckarrive');
        } elseif ($this->getFormAction() === 'doActionDocument') {
            $this->doUpdateJobAction();
        } elseif ($this->getFormAction() === 'doActionStartLoad') {
            $jobColVal = [
                'job_start_load_on' => date('Y-m-d H:i:s'),
            ];
            $jobDao = new JobOutboundDao();
            $jobDao->doUpdateTransaction($this->getIntParameter('job_id'), $jobColVal);
            $this->doUpdateJobAction(1);
            # Do notification
            $this->doGenerateNotificationReceiver('outboundstartload');
        } elseif ($this->getFormAction() === 'doActionEndLoad') {
            $detailData = JobOutboundDetailDao::loadSimpleDataByJobOutboundId($this->getIntParameter('job_id'));
            $jisDao = new JobInboundStockDao();
            $jodDao = new JobOutboundDetailDao();
            foreach ($detailData as $row) {
                $colValJis = [
                    'jis_jid_id' => $row['jod_jid_id'],
                    'jis_quantity' => (float)$row['jod_qty_loaded'] * -1,
                ];
                if (empty($row['jod_jis_id']) === true) {
                    $jisDao->doInsertTransaction($colValJis);
                    $jodDao->doUpdateTransaction($row['jod_id'], [
                        'jod_jis_id' => $jisDao->getLastInsertId(),
                    ]);
                } else {
                    $jisDao->doUpdateTransaction($row['jod_jis_id'], $colValJis);
                }
            }
            $jobColVal = [
                'job_end_load_on' => date('Y-m-d H:i:s'),
            ];
            $jobDao = new JobOutboundDao();
            $jobDao->doUpdateTransaction($this->getIntParameter('job_id'), $jobColVal);

            if ($this->isValidParameter('jo_jtr_id') === true) {
                $jtrDao = new JobStockTransferDao();
                $jtrDao->doUpdateTransaction($this->getIntParameter('jo_jtr_id'), [
                    'jtr_end_out_on' => date('Y-m-d H:i:s'),
                ]);
            }

            # Update job Action
            $this->doUpdateJobAction(2);
            # Do notification
            $this->doGenerateNotificationReceiver('outboundcompleteload');
        }
        parent::doUpdate();
    }


    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return JobOutboundDao::getByJoIdAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        parent::loadForm();
        $this->setJobHiddenData();
        # General tabs.
        $this->Tab->addPortlet('general', $this->getWarehouseFieldSet());
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getReferenceFieldSet());

        # goods tab
        if ($this->isValidParameter('job_start_load_on') === true && $this->isValidParameter('job_end_load_on') === false) {
            $this->Tab->addPortlet('goods', $this->getLoadingFieldSet());
            $this->Tab->setActiveTab('goods', true);
        }
        if ($this->isValidParameter('job_start_store_on') === true) {
            $this->Tab->addPortlet('goods', $this->getStorageFieldSet());
            if ($this->isValidParameter('job_end_store_on') === false) {
                $this->Tab->setActiveTab('goods', true);
            }
        }
        $this->Tab->addPortlet('goods', $this->getGoodsFieldSet());
        # include default portlet
        $this->includeAllDefaultPortlet();
    }


    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doActionStartPick') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doInsertStorage') {
            $this->Validation->checkRequire('job_id');
            $this->Validation->checkRequire('jod_jog_id');
            $this->Validation->checkRequire('jod_gdu_id');
            $this->Validation->checkRequire('jod_jid_id');
            $this->Validation->checkRequire('jod_whs_id');
            $this->Validation->checkRequire('jod_quantity');
            $this->Validation->checkRequire('jod_jog_quantity');
            $this->Validation->checkRequire('jod_jid_stock');
            if ($this->isValidParameter('jod_jog_quantity') === true) {
                $this->Validation->checkFloat('jod_quantity', 1, $this->getFloatParameter('jod_jog_quantity'));
            }
            if ($this->isValidParameter('jod_jid_stock') === true) {
                $this->Validation->checkFloat('jod_quantity', 1, $this->getFloatParameter('jod_jid_stock'));
            }
        } elseif ($this->getFormAction() === 'doDeleteStorage') {
            $this->Validation->checkRequire('jod_id_del');
        } elseif ($this->getFormAction() === 'doLoadGoods') {
            $this->Validation->checkRequire('jodl_id');
            $this->Validation->checkRequire('jodl_qty_loaded');
            $this->Validation->checkFloat('jodl_qty_loaded', 1, $this->getFloatParameter('jodl_quantity'));
        } elseif ($this->getFormAction() === 'doActionEndPick') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doActionArrive') {
            $this->loadActionValidationRole();
            $this->Validation->checkRequire('job_vendor_id');
            $this->Validation->checkRequire('job_driver', 1, 255);
            $this->Validation->checkRequire('job_ata_date');
            $this->Validation->checkDate('job_ata_date');
            $this->Validation->checkRequire('job_ata_time');
            $this->Validation->checkTime('job_ata_time');
        } elseif ($this->getFormAction() === 'doActionDocument') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doActionStartLoad') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doActionEndLoad') {
            $this->loadActionValidationRole();
        }
        parent::loadValidationRole();
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getWarehouseFieldSet(): Portlet
    {
        $etaTime = '';
        if ($this->isValidParameter('job_eta_date') === true) {
            if ($this->isValidParameter('job_eta_time') === true) {
                $etaTime = DateTimeParser::format($this->getStringParameter('job_eta_date') . ' ' . $this->getStringParameter('job_eta_time'), 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $etaTime = DateTimeParser::format($this->getStringParameter('job_eta_date'), 'Y-m-d', 'd M Y');
            }
        }
        $ataTime = '';
        if ($this->isValidParameter('job_ata_date') === true) {
            if ($this->isValidParameter('job_ata_time') === true) {
                $ataTime = DateTimeParser::format($this->getStringParameter('job_ata_date') . ' ' . $this->getStringParameter('job_ata_time'), 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $ataTime = DateTimeParser::format($this->getStringParameter('job_ata_date'), 'Y-m-d', 'd M Y');
            }
        }
        $driver = $this->getStringParameter('job_driver');
        if ($this->isValidParameter('job_driver_phone') === true) {
            $driver .= ' / ' . $this->getStringParameter('job_driver_phone');
        }
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('warehouse'),
                'value' => $this->getStringParameter('job_warehouse'),
            ],
            [
                'label' => Trans::getWord('eta'),
                'value' => $etaTime,
            ],
            [
                'label' => Trans::getWord('ata'),
                'value' => $ataTime,
            ],
            [
                'label' => Trans::getWord('consignee'),
                'value' => $this->getStringParameter('job_consignee'),
            ],
            [
                'label' => Trans::getWord('picConsignee'),
                'value' => $this->getStringParameter('job_pic_consignee'),
            ],
            [
                'label' => Trans::getWord('consigneeAddress'),
                'value' => $this->getStringParameter('job_consignee_address'),
            ],
            [
                'label' => Trans::getWord('transporter'),
                'value' => $this->getStringParameter('job_vendor'),
            ],
            [
                'label' => Trans::getWord('driver'),
                'value' => $driver,
            ],
            [
                'label' => Trans::getWord('truckPlate'),
                'value' => $this->getStringParameter('job_truck_number'),
            ],
            [
                'label' => Trans::getWord('containerNumber'),
                'value' => $this->getStringParameter('job_container_number'),
            ],
            [
                'label' => Trans::getWord('sealNumber'),
                'value' => $this->getStringParameter('job_seal_number'),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JobGeneralPtl', Trans::getWord('jobDetail'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
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
            'jog_serial_number' => Trans::getWord('id'),
            'jog_sku' => Trans::getWord('sku'),
            'jog_goods' => Trans::getWord('goods'),
            'jog_production_number' => Trans::getWord('productionNumber'),
            'jog_quantity' => Trans::getWord('qtyPlanning'),
            'jog_unit' => Trans::getWord('uom'),
            'total_volume' => Trans::getWord('totalVolume') . ' (M3)',
            'total_weight' => Trans::getWord('totalWeight') . ' (KG)',
        ]);
        $rows = [];
        $gdDao = new GoodsDao();
        foreach ($this->Goods as $row) {
            $row['jog_goods'] = $gdDao->formatFullName($row['jog_gdc_name'], $row['jog_br_name'], $row['jog_goods']);
            $volume = 0;
            if (empty($row['jog_gd_volume']) === false) {
                $volume = (float)$row['jog_gd_volume'];
            }
            $row['total_volume'] = $volume * (float)$row['jog_quantity'];

            $weight = 0;
            if (empty($row['jog_gd_weight']) === false) {
                $weight = (float)$row['jog_gd_weight'];
            }
            $row['total_weight'] = $weight * (float)$row['jog_quantity'];
            if (empty($row['jog_damage_id']) === false) {
                $row['jog_condition'] = new LabelDanger(Trans::getWord('damage'));
            } else {
                $row['jog_condition'] = new LabelSuccess(Trans::getWord('good'));
            }
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('jog_quantity', 'float');
        $table->setColumnType('total_volume', 'float');
        $table->setColumnType('total_weight', 'float');
        $table->setFooterType('jog_quantity', 'SUM');
        $table->setFooterType('total_volume', 'SUM');
        $table->setFooterType('total_weight', 'SUM');
        if ($this->isValidParameter('job_end_load_on') === true) {
            $table->addColumnAfter('jog_quantity', 'jog_qty_loaded', Trans::getWord('qtyLoaded'));
            $table->setColumnType('jog_qty_loaded', 'float');
            $table->setFooterType('jog_qty_loaded', 'SUM');
        }
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
    protected function getStorageFieldSet(): Portlet
    {
        $modal = $this->getStorageModal();
        $this->View->addModal($modal);
        $modalDelete = $this->getStorageDeleteModal();
        $this->View->addModal($modalDelete);

        $table = new Table('JoJwdTbl');
        $table->setHeaderRow([
            'jod_storage' => Trans::getWord('storage'),
            'jod_jog_number' => Trans::getWord('goodsId'),
            'jod_gd_sku' => Trans::getWord('sku'),
            'jod_goods' => Trans::getWord('goods'),
            'jod_lot_number' => Trans::getWord('lotNumber'),
            'jod_packing_number' => Trans::getWord('packingNumber'),
            'jod_jid_serial_number' => Trans::getWord('serialNumber'),
            'jod_quantity' => Trans::getWord('qtyPicking'),
            'jod_unit' => Trans::getWord('uom'),
            'jod_condition' => Trans::getWord('condition'),
        ]);
        $wheres = [];
        $wheres[] = '(jod.jod_job_id = ' . $this->getIntParameter('job_id') . ')';
        $wheres[] = '(jod.jod_deleted_on IS NULL)';
        $data = JobOutboundDetailDao::loadData($wheres);
        $rows = [];
        $gdDao = new GoodsDao();
        foreach ($data as $row) {
            if (empty($row['jid_gdt_id']) === true) {
                $row['jod_condition'] = new LabelSuccess(Trans::getWord('good'));
            } else {
                $row['jod_condition'] = $row['jod_gdt_description'] . ', ' . Trans::getWord('causedBy') . ' ' . $row['jod_gcd_description'];
            }
            $qtyLoaded = (float)$row['jod_qty_loaded'];
            if ($qtyLoaded > 0) {
                $row['jod_qty_return'] = (float)$row['jod_quantity'] - $qtyLoaded;
            }
            $row['jod_goods'] = $gdDao->formatFullName($row['jod_gdc_name'], $row['jod_br_name'], $row['jod_gd_name']);

            $row['jod_action'] = '';


            $btnDelMdl = new ModalButton('btnJodDelMdl' . $row['jod_id'], '', $modalDelete->getModalId());
            $btnDelMdl->setIcon(Icon::Trash)->btnDanger()->viewIconOnly();
            $btnDelMdl->setEnableCallBack('jobOutboundDetail', 'getByReferenceForDelete');
            $btnDelMdl->addParameter('jod_id', $row['jod_id']);
            $row['jod_action'] .= $btnDelMdl;
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('jod_quantity', 'float');
        $table->setFooterType('jod_quantity', 'SUM');
        $table->addColumnAttribute('jod_condition', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_lot_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_uom', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_jid_serial_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_storage', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_gd_sku', 'style', 'text-align: center;');
        if ($this->isValidParameter('job_end_load_on') === true) {
            $table->addColumnAfter('jod_quantity', 'jod_qty_loaded', Trans::getWord('qtyLoaded'));
            $table->addColumnAfter('jod_qty_loaded', 'jod_qty_return', Trans::getWord('qtyReturned'));
            $table->setColumnType('jod_qty_loaded', 'float');
            $table->setColumnType('jod_qty_return', 'float');
            $table->setFooterType('jod_qty_loaded', 'SUM');
            $table->setFooterType('jod_qty_return', 'SUM');
        }
        # Create a portlet box.
        $portlet = new Portlet('JoJodPtl', Trans::getWord('goodsTaken'));
        if ($this->isValidParameter('job_end_store_on') === false && $this->isAllowUpdateAction()) {
            $table->addColumnAfter('jod_condition', 'jod_action', Trans::getWord('action'));
            $table->addColumnAttribute('jod_action', 'style', 'text-align: center;');

            $btnCpMdl = new ModalButton('btnJoJodMdl', Trans::getWord('pickGoods'), $modal->getModalId());
            $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnCpMdl);
        }

        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get storage modal.
     *
     * @return Modal
     */
    protected function getStorageModal(): Modal
    {
        $modal = new Modal('JobJodMdl', Trans::getWord('pickGoods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doInsertStorage');
        $showModal = false;
        if ($this->getFormAction() === 'doInsertStorage' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);

        # Create Unit Field
        $jogField = $this->Field->getSingleSelectTable('jobGoods', 'jod_goods', $this->getParameterForModal('jod_goods', $showModal), 'loadDataForOutbound');
        $jogField->setHiddenField('jod_jog_id', $this->getParameterForModal('jod_jog_id', $showModal));
        $jogField->setTableColumns([
            'jog_serial_number' => Trans::getWord('goodsId'),
            'jog_gd_sku' => Trans::getWord('sku'),
            'jog_goods' => Trans::getWord('goods'),
            'jog_quantity' => Trans::getWord('quantity'),
            'jog_uom' => Trans::getWord('uom'),
            'jog_production_number' => Trans::getWord('productionNumber'),
        ]);
        $jogField->setAutoCompleteFields([
            'jod_jog_number' => 'jog_serial_number',
            'jod_gd_sku' => 'jog_gd_sku',
            'jod_br_name' => 'jog_br_name',
            'jod_gdc_name' => 'jog_gdc_name',
            'jod_jog_quantity' => 'jog_quantity',
            'jod_jog_production_number' => 'jog_production_number',
            'jod_jog_quantity_number' => 'jog_quantity_number',
            'jod_jog_uom' => 'jog_uom',
            'jod_gdu_id' => 'jog_gdu_id',
            'jod_gd_id' => 'jog_gd_id',
            'jod_gd_sn' => 'jog_gd_sn',
        ]);
        $jogField->setValueCode('jog_id');
        $jogField->setLabelCode('jog_goods');
        $jogField->addParameter('jog_jo_id', $this->getDetailReferenceValue());
        $jogField->addParameter('job_id', $this->getIntParameter('job_id'));
        $jogField->addClearField('jod_jid_stock');
        $jogField->addClearField('jod_wh_name');
        $jogField->addClearField('jod_jid_id');
        $jogField->setParentModal($modal->getModalId());
        $this->View->addModal($jogField->getModal());


        # Create Goods Field
        $jidField = $this->Field->getSingleSelectTable('jobInboundDetail', 'jod_whs_name', $this->getParameterForModal('jod_whs_name', $showModal), 'loadDataForOutbound');
        $jidField->setHiddenField('jod_jid_id', $this->getParameterForModal('jod_jid_id', $showModal));
        $jidField->setTableColumns([
            'jid_whs_name' => Trans::getWord('storage'),
            'jid_lot_number' => Trans::getWord('lotNumber'),
            'jid_serial_number' => Trans::getWord('serialNumber'),
            'jid_stock' => Trans::getWord('stockAvailable'),
            'jid_uom' => Trans::getWord('uom'),
            'jid_gdt_description' => Trans::getWord('damageType'),
        ]);
        $jidField->setAutoCompleteFields([
            'jod_whs_id' => 'jid_whs_id',
            'jod_jid_stock' => 'jid_stock',
            'jod_jid_stock_number' => 'jid_stock_number',
            'jod_jid_serial_number' => 'jid_serial_number',
        ]);
        $jidField->setFilters([
            'jid_whs_name' => Trans::getWord('storage'),
            'jid_serial_number' => Trans::getWord('serialNumber'),
        ]);
        $jidField->setValueCode('jid_id');
        $jidField->setLabelCode('jid_whs_name');

        $jidField->addParameterById('jid_gd_id', 'jod_gd_id', Trans::getWord('goods'));
        $jidField->addParameterById('jid_gdu_id', 'jod_gdu_id', Trans::getWord('uom'));
        $jidField->addOptionalParameterById('jid_lot_number', 'jod_jog_production_number');
        $jidField->addParameter('wh_id', $this->getIntParameter('job_wh_id'));
        $jidField->addParameter('job_id', $this->getIntParameter('job_id'));
        $jidField->addClearField('jod_jid_stock');
        $jidField->addClearField('jod_jid_stock_number');
        $jidField->addClearField('jod_jid_serial_number');
        $jidField->setParentModal($modal->getModalId());
        $this->View->addModal($jidField->getModal());


        # set readonly field.
        $jogNumberField = $this->Field->getText('jod_jog_number', $this->getParameterForModal('jod_jog_number', $showModal));
        $jogNumberField->setReadOnly();
        $jogSkuField = $this->Field->getText('jod_gd_sku', $this->getParameterForModal('jod_gd_sku', $showModal));
        $jogSkuField->setReadOnly();
        $brField = $this->Field->getText('jod_br_name', $this->getParameterForModal('jod_br_name', $showModal));
        $brField->setReadOnly();
        $gdcField = $this->Field->getText('jod_gdc_name', $this->getParameterForModal('jod_gdc_name', $showModal));
        $gdcField->setReadOnly();
        $productionField = $this->Field->getText('jod_jog_production_number', $this->getParameterForModal('jod_jog_production_number', $showModal));
        $productionField->setReadOnly();
        $jogQtyField = $this->Field->getNumber('jod_jog_quantity', $this->getParameterForModal('jod_jog_quantity', $showModal));
        $jogQtyField->setReadOnly();
        $jogUomField = $this->Field->getText('jod_jog_uom', $this->getParameterForModal('jod_jog_uom', $showModal));
        $jogUomField->setReadOnly();
        $jidStockField = $this->Field->getNumber('jod_jid_stock', $this->getParameterForModal('jod_jid_stock', $showModal));
        $jidStockField->setReadOnly();
        $jidSnField = $this->Field->getText('jod_jid_serial_number', $this->getParameterForModal('jod_jid_serial_number', $showModal));
        $jidSnField->setReadOnly();


        # Add field into field set.
        $fieldSet->addField(Trans::getWord('goods'), $jogField, true);
        $fieldSet->addField(Trans::getWord('storage'), $jidField, true);
        $fieldSet->addField(Trans::getWord('qtyRequired'), $jogQtyField);
        $fieldSet->addField(Trans::getWord('stockAvailable'), $jidStockField);
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('jod_quantity', $this->getParameterForModal('jod_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getWord('uom'), $jogUomField);
        $fieldSet->addField(Trans::getWord('sku'), $jogSkuField);
        $fieldSet->addField(Trans::getWord('goodsId'), $jogNumberField);
        $fieldSet->addField(Trans::getWord('lotNumber'), $productionField);
        $fieldSet->addField(Trans::getWord('serialNumber'), $jidSnField);
        $fieldSet->addHiddenField($this->Field->getHidden('jod_gd_id', $this->getParameterForModal('jod_gd_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jod_lot_number', $this->getParameterForModal('jod_lot_number', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jod_gdu_id', $this->getParameterForModal('jod_gdu_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jod_whs_id', $this->getParameterForModal('jod_whs_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jod_gd_sn', $this->getParameterForModal('jod_gd_sn', $showModal)));
        $fieldSet->setGridDimension(6, 6);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get storage delete modal.
     *
     * @return Modal
     */
    protected function getStorageDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JowJwdDelMdl', Trans::getWord('unpickGoods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteStorage');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteStorage' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('goodsId'), $this->Field->getText('jod_jog_number_del', $this->getParameterForModal('jod_jog_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('goods'), $this->Field->getText('jod_goods_del', $this->getParameterForModal('jod_goods_del', $showModal)));
        $fieldSet->addField(Trans::getWord('storage'), $this->Field->getText('jod_storage_del', $this->getParameterForModal('jod_storage_del', $showModal)));
        $fieldSet->addField(Trans::getWord('lotNumber'), $this->Field->getText('jod_lot_number_del', $this->getParameterForModal('jod_lot_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getText('jod_quantity_del', $this->getParameterForModal('jod_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getWord('uom'), $this->Field->getText('jod_unit_del', $this->getParameterForModal('jod_unit_del', $showModal)));
        $fieldSet->addField(Trans::getWord('serialNumber'), $this->Field->getText('jod_jid_serial_number_del', $this->getParameterForModal('jod_jid_serial_number_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jod_id_del', $this->getParameterForModal('jod_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->getStringParameter('jo_route') === 'joWhBundling' || $this->getStringParameter('jo_route') === 'joWhUnBundling') {
            $this->EnableAction = false;
        }

        if ($this->isValidParameter('job_end_load_on') === true && $this->isJobFinish() === false) {
            $pdfButton = new PdfButton('JiPrint', Trans::getWord('printPdf'), 'outboundgoods');
            $pdfButton->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
            $pdfButton->addParameter('jo_id', $this->getDetailReferenceValue());
            $this->View->addButton($pdfButton);
        }
        # button DO
        if (($this->isValidParameter('job_end_load_on') === true && $this->isJobFinish() === false) || ($this->isValidParameter('job_end_store_on') === true && $this->PageSetting->checkPageRight('AllowPrintDoAfterPicking') === true)) {
            $pdfButton = new PdfButton('JiDoPrint', Trans::getWord('printDo'), 'deliveryorder');
            $pdfButton->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
            $pdfButton->addParameter('jo_id', $this->getDetailReferenceValue());
            $this->View->addButton($pdfButton);
        }
        parent::loadDefaultButton();
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
        if ($this->getStringParameter('jo_route') === 'joWhBundling') {
            $jbData = JobBundlingDao::getByJobOrder($this->getDetailReferenceValue());
            $wheres[] = '(jog.jog_id <> ' . $jbData['jb_jog_id'] . ')';
        }
        if ($this->getStringParameter('jo_route') === 'joWhUnBundling') {
            $jbData = JobBundlingDao::getByJobOrder($this->getDetailReferenceValue());
            $wheres[] = '(jog.jog_id = ' . $jbData['jb_jog_id'] . ')';
        }
        $this->Goods = JobGoodsDao::loadDataForOutbound($wheres);
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getLoadingFieldSet(): Portlet
    {
        $table = new Table('JoJodLoadTbl');
        $table->setHeaderRow([
            'jodl_storage' => Trans::getWord('storage'),
            'jodl_gd_sku' => Trans::getWord('sku'),
            'jodl_goods' => Trans::getWord('goods'),
            'jodl_lot_number' => Trans::getWord('lotNumber'),
            'jodl_packing_number' => Trans::getWord('packingNumber'),
            'jodl_jid_serial_number' => Trans::getWord('serialNumber'),
            'jodl_quantity' => Trans::getWord('qtyPicking'),
            'jodl_qty_loaded' => Trans::getWord('qtyLoaded'),
            'jodl_qty_return' => Trans::getWord('qtyReturned'),
            'jodl_unit' => Trans::getWord('uom'),
            'jodl_gdt_code' => Trans::getWord('condition'),
        ]);
        $wheres = [];
        $wheres[] = '(jod.jod_job_id = ' . $this->getIntParameter('job_id') . ')';

        $data = JobOutboundDetailDao::getDataForLoading($wheres);
        $rows = [];
        $i = 0;
        $gdDao = new GoodsDao();
        foreach ($data as $newRow) {
            if (empty($newRow['jid_gdt_id']) === true) {
                $newRow['jodl_gdt_code'] = new LabelSuccess(Trans::getWord('good'));
            }
            $qtyLoaded = (float)$newRow['jodl_qty_loaded'];
            if ($qtyLoaded > 0) {
                $newRow['jodl_qty_return'] = (float)$newRow['jodl_quantity'] - $qtyLoaded;
            } else {
                $table->addCellAttribute('jodl_qty_loaded', $i, 'style', 'background-color: red; text-align: right; color: black;');
            }
            $newRow['jodl_goods'] = $gdDao->formatFullName($newRow['jodl_gdc_name'], $newRow['jodl_br_name'], $newRow['jodl_gd_name']);
            $rows[] = $newRow;
            $i++;
        }
        $table->addRows($rows);
        $table->setColumnType('jodl_quantity', 'float');
        $table->setFooterType('jodl_quantity', 'SUM');
        $table->addColumnAttribute('jodl_gdt_code', 'style', 'text-align: center;');
        $table->setColumnType('jodl_qty_loaded', 'float');
        $table->setColumnType('jodl_qty_return', 'float');
        $table->setFooterType('jodl_qty_loaded', 'SUM');
        $table->setFooterType('jodl_qty_return', 'SUM');
        # Create a portlet box.
        $portlet = new Portlet('JodLoadPtl', Trans::getWord('goodsLoaded'));
        if ($this->isAllowUpdateAction() === true) {
            $modal = $this->getGoodsLoadModal();
            $this->View->addModal($modal);
            $table->setUpdateActionByModal($modal, 'jobOutboundDetail', 'getByReferenceForLoading', ['jodl_id']);
        }

        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getGoodsLoadModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JodLoadingMdl', Trans::getWord('loadGoods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doLoadGoods');
        $showModal = false;
        if ($this->getFormAction() === 'doLoadGoods' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);

        # set readonly field.
        $storageField = $this->Field->getText('jodl_storage', $this->getParameterForModal('jodl_storage', $showModal));
        $storageField->setReadOnly();
        $goodsField = $this->Field->getText('jodl_goods', $this->getParameterForModal('jodl_goods', $showModal));
        $goodsField->setReadOnly();
        $jogNumberField = $this->Field->getText('jodl_jog_number', $this->getParameterForModal('jodl_jog_number', $showModal));
        $jogNumberField->setReadOnly();
        $skuField = $this->Field->getText('jodl_gd_sku', $this->getParameterForModal('jodl_gd_sku', $showModal));
        $skuField->setReadOnly();
        $productionField = $this->Field->getText('jodl_lot_number', $this->getParameterForModal('jodl_lot_number', $showModal));
        $productionField->setReadOnly();
        $qtyField = $this->Field->getNumber('jodl_quantity', $this->getParameterForModal('jodl_quantity', $showModal));
        $qtyField->setReadOnly();
        $uomField = $this->Field->getText('jodl_unit', $this->getParameterForModal('jodl_unit', $showModal));
        $uomField->setReadOnly();
        $gdtCodeField = $this->Field->getNumber('jodl_gdt_code', $this->getParameterForModal('jodl_gdt_code', $showModal));
        $gdtCodeField->setReadOnly();


        # Add field into field set.
        $fieldSet->addField(Trans::getWord('storage'), $storageField);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField);
        $fieldSet->addField(Trans::getWord('quantity'), $qtyField);
        $fieldSet->addField(Trans::getWord('qtyLoaded'), $this->Field->getNumber('jodl_qty_loaded', $this->getParameterForModal('jodl_qty_loaded', $showModal)), true);
        $fieldSet->addField(Trans::getWord('sku'), $skuField);
        $fieldSet->addField(Trans::getWord('uom'), $uomField);
        $fieldSet->addField(Trans::getWord('goodsId'), $jogNumberField);
        $fieldSet->addField(Trans::getWord('productionNumber'), $productionField);
        $fieldSet->addHiddenField($this->Field->getHidden('jodl_id', $this->getParameterForModal('jodl_id', $showModal)));
        $fieldSet->setGridDimension(6, 6);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }
    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function setJobHiddenData(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('job_id', $this->getIntParameter('job_id'));
        $content .= $this->Field->getHidden('job_wh_id', $this->getIntParameter('job_wh_id'));
        $this->View->addContent('JobHdFld', $content);
    }
}
