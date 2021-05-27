<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Viewer\Job\Warehouse\Bundling;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Buttons\SubmitButton;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Table;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Job\JobActionDao;
use App\Model\Dao\Job\JobGoodsDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\Warehouse\Bundling\JobBundlingDao;
use App\Model\Dao\Job\Warehouse\Bundling\JobBundlingDetailDao;
use App\Model\Dao\Job\Warehouse\Bundling\JobBundlingMaterialDao;
use App\Model\Dao\Job\Warehouse\JobInboundDao;
use App\Model\Dao\Job\Warehouse\JobInboundDetailDao;
use App\Model\Dao\Job\Warehouse\JobInboundReceiveDao;
use App\Model\Dao\Job\Warehouse\JobInboundStockDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDao;
use App\Model\Dao\Job\Warehouse\JobOutboundDetailDao;
use App\Model\Dao\Master\Goods\GoodsDao;
use App\Model\Dao\Master\Goods\GoodsMaterialDao;
use App\Model\Helper\Job\Warehouse\InboundReceiveSn;
use App\Model\Viewer\Job\BaseJobOrder;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of detail JoInbound page
 *
 * @package    app
 * @subpackage Model\Viewer\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobUnBundling extends BaseJobOrder
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'joWhUnBundling', 'jo_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doActionStartPicking') {
            # Insert Job Goods data
            $materials = GoodsMaterialDao::getByGdId($this->getIntParameter('jog_gd_id'));
            $gdDao = new GoodsDao();
            $jogDao = new JobGoodsDao();
            $sn = new SerialNumber($this->User->getSsId());
            foreach ($materials as $row) {
                # Insert Job Goods
                $snGoods = $sn->loadNumber( 'JobOrderGoods', $this->getIntParameter('jo_order_of_id'), $this->getIntParameter('jo_rel_id'), $this->getIntParameter('jo_srv_id'), $this->getIntParameter('jo_srt_id'));
                $jogColVal = [
                    'jog_serial_number' => $snGoods,
                    'jog_jo_id' => $this->getDetailReferenceValue(),
                    'jog_gd_id' => $row['gm_goods_id'],
                    'jog_name' => $gdDao->formatFullName($row['gm_gdc_name'], $row['gm_br_name'], $row['gm_gd_name']),
                    'jog_quantity' => (float)$row['gm_quantity'] * $this->getFloatParameter('jog_quantity'),
                    'jog_gdu_id' => $row['gm_gdu_id'],
                ];
                $jogDao->doInsertTransaction($jogColVal);
            }
            # Create date time action
            $dateTime = $this->getStringParameter('jac_date') . ' ' . $this->getStringParameter('jac_time');
            # Create outbound Job
            $jobColVal = [
                'job_jo_id' => $this->getDetailReferenceValue(),
                'job_wh_id' => $this->getIntParameter('jb_wh_id'),
                'job_eta_date' => $this->getStringParameter('jb_et_date'),
                'job_eta_time' => $this->getStringParameter('jb_et_time'),
                'job_ata_date' => $this->getStringParameter('jb_et_date'),
                'job_ata_time' => $this->getStringParameter('jb_et_time'),
                'job_rel_id' => $this->User->getRelId(),
                'job_of_id' => $this->User->Relation->getOfficeId(),
                'job_cp_id' => $this->User->Relation->getPersonId(),
                'job_vendor_id' => $this->User->getRelId(),
                'job_start_store_on' => $dateTime,
            ];
            $jobDao = new JobOutboundDao();
            $jobDao->doInsertTransaction($jobColVal);
            # Update start Job
            $joColVal = [
                'jo_start_by' => $this->User->getId(),
                'jo_start_on' => $dateTime,
            ];
            $joDao = new JobOrderDao();
            $joDao->doUpdateTransaction($this->getDetailReferenceValue(), $joColVal);

            # Update start store job.
            $jbColVal = [
                'jb_start_pick_on' => $dateTime,
            ];
            $jbDao = new JobBundlingDao();
            $jbDao->doUpdateTransaction($this->getIntParameter('jb_id'), $jbColVal);
            # Update job Action
            $this->doUpdateJobAction(1);
        } elseif ($this->getFormAction() === 'doInsertPickingStorage') {
            $jodColVal = [
                'jod_job_id' => $this->getIntParameter('jb_outbound_id'),
                'jod_jog_id' => $this->getIntParameter('jod_jog_id'),
                'jod_jid_id' => $this->getIntParameter('jod_jid_id'),
                'jod_whs_id' => $this->getIntParameter('jod_whs_id'),
                'jod_gd_id' => $this->getIntParameter('jod_gd_id'),
                'jod_quantity' => $this->getFloatParameter('jod_quantity'),
                'jod_gdu_id' => $this->getIntParameter('jod_gdu_id'),
                'jod_lot_number' => $this->getStringParameter('jod_lot_number'),
            ];
            $jodDao = new JobOutboundDetailDao();
            if ($this->isValidParameter('jod_id') === true) {
                $jodDao->doUpdateTransaction($this->getIntParameter('jod_id'), $jodColVal);
            } else {
                $jodDao->doInsertTransaction($jodColVal);
            }
        } elseif ($this->getFormAction() === 'doDeletePickingStorage') {
            $jodDao = new JobOutboundDetailDao();
            $jodDao->doDeleteTransaction($this->getIntParameter('jod_id_del'));
        } elseif ($this->getFormAction() === 'doActionEndPicking') {
            $jodData = JobOutboundDetailDao::loadSimpleDataByJobOutboundId($this->getIntParameter('jb_outbound_id'));
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
                    'jod_qty_loaded' => $row['jod_quantity'],
                ]);
            }
            # Create date time action
            $dateTime = $this->getStringParameter('jac_date') . ' ' . $this->getStringParameter('jac_time');

            # Update start store job.
            $jobColVal = [
                'job_end_store_on' => $dateTime,
                'job_start_load_on' => $dateTime,
                'job_end_load_on' => $dateTime,
            ];
            $jobDao = new JobOutboundDao();
            $jobDao->doUpdateTransaction($this->getIntParameter('jb_outbound_id'), $jobColVal);
            # Update job Packing
            # Update start store job.
            $jbColVal = [
                'jb_end_pick_on' => $dateTime,
            ];
            $jbDao = new JobBundlingDao();
            $jbDao->doUpdateTransaction($this->getIntParameter('jb_id'), $jbColVal);
            # Update job Action
            $this->doUpdateJobAction(2);
        } elseif ($this->getFormAction() === 'doActionStartUnBundling') {
            # Create date time action
            $dateTime = $this->getStringParameter('jac_date') . ' ' . $this->getStringParameter('jac_time');
            # Update start store job.
            $jbColVal = [
                'jb_start_pack_on' => $dateTime,
            ];
            $jbDao = new JobBundlingDao();
            $jbDao->doUpdateTransaction($this->getIntParameter('jb_id'), $jbColVal);
            # Update job Action
            $this->doUpdateJobAction(1);
        } elseif ($this->getFormAction() === 'doCreateBundle') {
            # Load Bundling Materials
            $wheres = [];
            $wheres[] = '(jog_id <> ' . $this->getIntParameter('jb_jog_id') . ')';
            $wheres[] = '(jog_jo_id = ' . $this->getDetailReferenceValue() . ')';
            $wheres[] = '(gm.gm_gd_id = ' . $this->getIntParameter('jog_gd_id') . ')';
            $strWheres = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT    jog.jog_id, gm.gm_quantity, gd.gd_sn, jb.jid_id,
                                jb.jid_gd_id, jb.jid_gdu_id, jb.jid_gdt_id, jb.jid_gdt_remark,
                                jb.jid_gcd_id, jb.jid_gcd_remark, jb.jid_length, jb.jid_width,
                                jb.jid_height, jb.jid_weight, jb.jid_volume, jb.jid_expired_date,
                                jb.jid_serial_number, jb.jid_packing_number, jb.jid_lot_number, jb.jid_id AS jbm_jid_id
                      FROM 		job_order AS jo INNER JOIN
                                job_goods AS jog ON jog.jog_jo_id = jo.jo_id INNER JOIN
                                goods_material as gm ON gm.gm_goods_id = jog.jog_gd_id INNER JOIN
                                goods as gd ON gd.gd_id = jog.jog_gd_id LEFT OUTER JOIN
                               (SELECT 	    job.job_jo_id, jod.jod_jid_id
				          	    FROM 		job_order AS jo INNER JOIN
                                            job_outbound AS job ON job.job_jo_id = jo.jo_id INNER JOIN
                                            job_outbound_detail AS jod ON jod.jod_job_id = job.job_id INNER JOIN
                                            job_inbound_detail AS jid ON jid.jid_id = jod.jod_jid_id
					            WHERE jid.jid_serial_number = \'' . $this->getStringParameter('jbd_serial_number_add') . '\') AS job ON job.job_jo_id = jo.jo_id LEFT OUTER JOIN
					            (SELECT     jid2.jid_gd_id AS jb_gd_id, jid2.jid_id, jid2.jid_gd_id, jid2.jid_gdu_id, jid2.jid_gdt_id, jid2.jid_gdt_remark,
                                            jid2.jid_gcd_id, jid2.jid_gcd_remark, jid2.jid_length, jid2.jid_width,
                                            jid2.jid_height, jid2.jid_weight, jid2.jid_volume, jid2.jid_expired_date,
                                            jid2.jid_serial_number, jid2.jid_packing_number, jid2.jid_lot_number , jid.jid_id AS jod_jid_id
					            FROM        job_inbound_detail AS jid INNER JOIN
                                            job_inbound AS ji ON ji.ji_id = jid.jid_ji_id INNER JOIN
                                            job_order AS jo ON jo.jo_id = ji.ji_jo_id INNER JOIN
                                            job_bundling AS jb ON jb.jb_jo_id = jo.jo_id INNER JOIN
                                            job_bundling_detail AS jbd ON jbd.jbd_jb_id = jb.jb_id AND
                                            jbd.jbd_serial_number = jid.jid_serial_number INNER JOIN
                                            job_bundling_material AS jbm ON jbm.jbm_jbd_id = jbd.jbd_id INNER JOIN
                                            job_inbound_detail AS jid2 ON jid2.jid_id = jbm.jbm_jid_id
                                WHERE    jbd.jbd_deleted_on IS NULL AND jbm.jbm_deleted_on IS NULL) AS jb ON jb.jod_jid_id = job.jod_jid_id AND
                                          jb.jb_gd_id = gd.gd_id' . $strWheres;
            $sqlResult = DB::select($query);
            $data = DataParser::arrayObjectToArray($sqlResult);
            $autocomplete = true;
            foreach ($data as $row) {
                if ($row['gd_sn'] === 'Y') {
                    $autocomplete = false;
                }
            }
            # Insert Bundling Detail
            $jbdColVal = [
                'jbd_jb_id' => $this->getIntParameter('jb_id'),
                'jbd_jog_id' => $this->getIntParameter('jb_jog_id'),
                'jbd_lot_number' => $this->getStringParameter('jbd_lot_number_add'),
                'jbd_serial_number' => $this->getStringParameter('jbd_serial_number_add'),
                'jbd_quantity' => $this->getFloatParameter('jbd_quantity_add'),
                'jbd_us_id' => $this->getIntParameter('jbd_us_id_add'),
                'jbd_start_on' => date('Y-m-d H:i:s'),
            ];
            if ($autocomplete === true) {
                $jbdColVal['jbd_end_on'] = date('Y-m-d H:i:s');
            }
            $jbdDao = new JobBundlingDetailDao();
            $jbdDao->doInsertTransaction($jbdColVal);
            # Insert Bundling materials
            $jbmDao = new JobBundlingMaterialDao();
            foreach ($data as $row) {
                $qty = 1;
                if (empty($row['jid_id']) === true) {
                    $qty = (int)$row['gm_quantity'];
                }
                for ($i = 0; $i < $qty; $i++) {
                    $jbmColVal = [
                        'jbm_jbd_id' => $jbdDao->getLastInsertId(),
                        'jbm_jid_id' => $row['jbm_jid_id'],
                        'jbm_jog_id' => $row['jog_id'],
                        'jbm_quantity' => 1,
                        'jbm_gd_id' => $row['jid_gd_id'],
                        'jbm_gdu_id' => $row['jid_gdu_id'],
                        'jbm_serial_number' => $row['jid_serial_number'],
                        'jbm_packing_number' => $row['jid_packing_number'],
                        'jbm_lot_number' => $row['jid_lot_number'],
                        'jbm_gdt_id' => $row['jid_gdt_id'],
                        'jbm_gdt_remark' => $row['jid_gdt_remark'],
                        'jbm_gcd_id' => $row['jid_gcd_id'],
                        'jbm_gcd_remark' => $row['jid_gcd_remark'],
                        'jbm_expired_date' => $row['jid_expired_date'],
                        'jbm_volume' => $row['jid_volume'],
                        'jbm_weight' => $row['jid_weight']
                    ];
                    $jbmDao->doInsertTransaction($jbmColVal);
                }
            }
        } elseif ($this->getFormAction() === 'doBundleMaterial') {
            $volume = null;
            if (($this->isValidParameter('jbm_length') === true) && ($this->isValidParameter('jbm_height') === true) && ($this->isValidParameter('jbm_width') === true)) {
                $volume = $this->getFloatParameter('jbm_length') * $this->getFloatParameter('jbm_height') * $this->getFloatParameter('jbm_width');
            }
            $jbmColVal = [
                'jbm_jog_id' => $this->getIntParameter('jbm_jog_id'),
                'jbm_quantity' => $this->getFloatParameter('jbm_quantity'),
                'jbm_serial_number' => $this->getStringParameter('jbm_serial_number'),
                'jbm_lot_number' => $this->getStringParameter('jbm_lot_number'),
                'jbm_packing_number' => $this->getStringParameter('jbm_packing_number'),
                'jbm_expired_date' => $this->getStringParameter('jbm_expired_date'),
                'jbm_gdt_id' => $this->getIntParameter('jbm_gdt_id'),
                'jbm_gdt_remark' => $this->getStringParameter('jbm_gdt_remark'),
                'jbm_gcd_id' => $this->getIntParameter('jbm_gcd_id'),
                'jbm_gcd_remark' => $this->getStringParameter('jbm_gcd_remark'),
                'jbm_stored' => $this->getStringParameter('jbm_stored', 'Y'),
                'jbm_length' => $this->getFloatParameter('jbm_length'),
                'jbm_width' => $this->getFloatParameter('jbm_width'),
                'jbm_height' => $this->getFloatParameter('jbm_height'),
                'jbm_volume' => $volume,
                'jbm_weight' => $this->getFloatParameter('jbm_weight'),
            ];
            $jbmDao = new JobBundlingMaterialDao();
            $jbmDao->doUpdateTransaction($this->getIntParameter('jbm_id'), $jbmColVal);
        } elseif ($this->getFormAction() === 'doCompleteBundle') {
            $jbdColVal = [
                'jbd_lot_number' => $this->getStringParameter('jbd_lot_number'),
                'jbd_serial_number' => $this->getStringParameter('jbd_serial_number'),
                'jbd_end_on' => date('Y-m-d H:i:s'),
            ];
            $jbdDao = new JobBundlingDetailDao();
            $jbdDao->doUpdateTransaction($this->getIntParameter('jbd_id'), $jbdColVal);
        } elseif ($this->getFormAction() === 'doDeleteBundle') {
            $jbdDao = new JobBundlingDetailDao();
            $jbdDao->doDeleteTransaction($this->getIntParameter('jbd_id_del'));
        } elseif ($this->getFormAction() === 'doActionEndUnBundling') {
            # Create date time action
            $dateTime = $this->getStringParameter('jac_date') . ' ' . $this->getStringParameter('jac_time');
            # Update job Packing
            $jbColVal = [
                'jb_end_pack_on' => $dateTime,
            ];
            $jbDao = new JobBundlingDao();
            $jbDao->doUpdateTransaction($this->getIntParameter('jb_id'), $jbColVal);
            # Update job Action
            $this->doUpdateJobAction(2);
        } elseif ($this->getFormAction() === 'doActionStartPutAway') {
            # Create date time action
            $dateTime = $this->getStringParameter('jac_date') . ' ' . $this->getStringParameter('jac_time');
            # Create Inbound Job
            $jiColVal = [
                'ji_jo_id' => $this->getDetailReferenceValue(),
                'ji_wh_id' => $this->getIntParameter('jb_wh_id'),
                'ji_eta_date' => $this->getStringParameter('jb_et_date'),
                'ji_eta_time' => $this->getStringParameter('jb_et_time'),
                'ji_ata_date' => $dateTime,
                'ji_ata_time' => $dateTime,
                'ji_rel_id' => $this->User->getRelId(),
                'ji_of_id' => $this->User->Relation->getOfficeId(),
                'ji_cp_id' => $this->User->Relation->getPersonId(),
                'ji_vendor_id' => $this->User->getRelId(),
                'ji_start_load_on' => $dateTime,
                'ji_end_load_on' => $dateTime,
                'ji_start_store_on' => $dateTime,
            ];
            $jiDao = new JobInboundDao();
            $jiDao->doInsertTransaction($jiColVal);
            $jirDao = new JobInboundReceiveDao();
            # Create Inbound Receive.
            if ($this->getStringParameter('jog_gd_sn') === 'Y') {
                $jbmData = JobBundlingMaterialDao::getByJobBundling($this->getIntParameter('jb_id'));
                foreach ($jbmData AS $row) {
                    $jirColVal = [
                        'jir_ji_id' => $jiDao->getLastInsertId(),
                        'jir_jog_id' => $row['jbm_jog_id'],
                        'jir_quantity' => 1,
                        'jir_qty_damage' => 0,
                        'jir_stored' => $row['jbm_stored'],
                        'jir_serial_number' => $row['jbm_serial_number'],
                        'jir_packing_number' => $row['jbm_packing_number'],
                        'jir_lot_number' => $row['jbm_lot_number'],
                        'jir_gdt_id' => $row['jbm_gdt_id'],
                        'jir_gdt_remark' => $row['jbm_gdt_remark'],
                        'jir_gcd_id' => $row['jbm_gcd_id'],
                        'jir_gcd_remark' => $row['jbm_gcd_remark'],
                        'jir_expired_date' => $row['jbm_expired_date'],
                        'jir_volume' => $row['jbm_volume'],
                        'jir_weight' => $row['jbm_weight']
                    ];
                    $jirDao->doInsertTransaction($jirColVal);
                }
            } else {
                $jogData = $this->loadJobGoodsMaterialData($this->getDetailReferenceValue(), $this->getIntParameter('jb_jog_id'));
                foreach ($jogData as $row) {
                    $jirColVal = [
                        'jir_ji_id' => $jiDao->getLastInsertId(),
                        'jir_jog_id' => $row['jog_id'],
                        'jir_quantity' => $row['jog_quantity'],
                        'jir_qty_damage' => 0,
                        'jir_stored' => 'Y'
                    ];
                    $jirDao->doInsertTransaction($jirColVal);
                }
            }
            # Update start store job.
            $jbColVal = [
                'jb_start_store_on' => $dateTime,
            ];
            $jbDao = new JobBundlingDao();
            $jbDao->doUpdateTransaction($this->getIntParameter('jb_id'), $jbColVal);
            # Update job Action
            $this->doUpdateJobAction(1);
        } elseif ($this->getFormAction() === 'doInsertPutAway') {
            $jidColVal = [
                'jid_ji_id' => $this->getIntParameter('jb_inbound_id'),
                'jid_jir_id' => $this->getIntParameter('jid_jir_id'),
                'jid_whs_id' => $this->getIntParameter('jid_whs_id'),
                'jid_quantity' => $this->getFloatParameter('jid_quantity'),
                'jid_gd_id' => $this->getIntParameter('jid_gd_id'),
                'jid_gdu_id' => $this->getIntParameter('jid_gdu_id'),
                'jid_adjustment' => 'N',
                'jid_lot_number' => $this->getStringParameter('jid_lot_number'),
                'jid_serial_number' => $this->getStringParameter('jid_serial_number'),
                'jid_packing_number' => $this->getStringParameter('jid_packing_number'),
            ];
            $jidDao = new JobInboundDetailDao();
            if ($this->isValidParameter('jid_id') === true) {
                $jidDao->doUpdateTransaction($this->getIntParameter('jid_id'), $jidColVal);
            } else {
                $jidDao->doInsertTransaction($jidColVal);
            }
        } elseif ($this->getFormAction() === 'doDeletePutAway') {
            $jidDao = new JobInboundDetailDao();
            $jidDao->doDeleteTransaction($this->getIntParameter('jid_id_del'));
        } elseif ($this->getFormAction() === 'doActionEndPutAway') {
            # Create date time action
            $dateTime = $this->getStringParameter('jac_date') . ' ' . $this->getStringParameter('jac_time');
            $wheres = [];
            $wheres[] = '(jid.jid_ji_id = ' . $this->getIntParameter('jb_inbound_id') . ')';
            $wheres[] = '(jid.jid_id NOT IN (SELECT jis_jid_id FROM job_inbound_stock WHERE jis_deleted_on IS NULL))';
            $wheres[] = '(jid.jid_deleted_on IS NULL)';

            $storage = JobInboundDetailDao::loadData($wheres);
            $jisDao = new JobInboundStockDao();
            foreach ($storage as $row) {
                $jisColVal = [
                    'jis_jid_id' => $row['jid_id'],
                    'jis_quantity' => $row['jid_quantity'],
                ];
                $jisDao->doInsertTransaction($jisColVal);
            }
            $jowColVal = [
                'ji_end_store_on' => $dateTime,
            ];
            $jiDao = new JobInboundDao();
            $jiDao->doUpdateTransaction($this->getIntParameter('jb_inbound_id'), $jowColVal);

            # Update start store job.
            $jbColVal = [
                'jb_end_store_on' => $dateTime,
            ];
            $jbDao = new JobBundlingDao();
            $jbDao->doUpdateTransaction($this->getIntParameter('jb_id'), $jbColVal);
            # Update job Action
            $this->doUpdateJobAction(2);
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
        return JobBundlingDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId(), $this->getIntParameter('jo_srt_id', 14));
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        # Load Current Action
        $this->CurrentAction = JobActionDao::getLastActiveActionByJobId($this->getDetailReferenceValue());

        # Override title page
        $this->overridePageTitle();
        # Show delete reason
        if ($this->isJobDeleted() === true) {
            $this->setDisableUpdate();
            $this->View->addErrorMessage(Trans::getWord('jobCanceledReason', 'message', '', ['user' => $this->getStringParameter('jo_deleted_by'), 'reason' => $this->getStringParameter('jo_deleted_reason')]));
        }
        # Show hold reason
        if ($this->isJobHold() === true) {
            $this->setDisableUpdate();
            $date = DateTimeParser::format($this->getStringParameter('joh_created_on'), 'Y-m-d H:i:s', 'H:i - d M Y');
            $this->View->addWarningMessage(Trans::getWord('joHoldReason', 'message', '', ['date' => $date, 'reason' => $this->getStringParameter('joh_reason')]));
        }
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
        $this->Tab->addPortlet('general', $this->getDetailFieldSet());
        if ($this->isValidParameter('jo_start_on') === true) {
            $this->Tab->addPortlet('general', $this->getJogMaterialFieldSet());
        } else {
            $this->Tab->addPortlet('general', $this->getMaterialFieldSet());
        }
        # Picking Process
        if ($this->isValidParameter('jb_start_pick_on') === true) {
            $this->Tab->addPortlet('pickingGoods', $this->getStorageFieldSet());
            if ($this->isValidParameter('jb_end_pick_on') === false) {
                $this->Tab->setActiveTab('pickingGoods', true);
            } else {
                $this->Tab->setActiveTab('general', true);
            }
        }
        # Bundling Process
        if ($this->isValidParameter('jb_start_pack_on') === true) {
            $this->Tab->addPortlet('unBundling', $this->getBundlingFieldSet());
            if ($this->isValidParameter('jb_end_pack_on') === false) {
                $this->Tab->setActiveTab('unBundling', true);
            } else {
                $this->Tab->setActiveTab('general', true);
            }
            # Progress Bundling
            $jbd = JobBundlingDetailDao::getInProgressBundle($this->getIntParameter('jb_id'), $this->User->getId());
            if (empty($jbd) === false) {
                $this->setParameter('jbd_id', $jbd['jbd_id']);
                $this->setParameter('jbd_us_id', $jbd['jbd_us_id']);
                $this->setParameter('jbd_user', $jbd['jbd_user']);
                $this->setParameter('jbd_lot_number', $jbd['jbd_lot_number']);
                $this->setParameter('jbd_serial_number', $jbd['jbd_serial_number']);

                $this->Tab->addPortlet('inProgress', $this->getBundlingProgressFieldSet());
                $this->Tab->addPortlet('inProgress', $this->getBundlingMaterialProgressFieldSet());
                $this->Tab->setActiveTab('inProgress', true);
            }
        }
        # Put Away Process
        if ($this->isValidParameter('jb_start_store_on') === true) {
            $this->Tab->addPortlet('putAway', $this->getPutAwayPortlet());
            if ($this->isValidParameter('jb_end_store_on') === false) {
                $this->Tab->setActiveTab('putAway', true);
            } else {
                $this->Tab->setActiveTab('general', true);
            }
        }


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
        if ($this->getFormAction() === 'doActionStartPicking') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doInsertPickingStorage') {
            $this->Validation->checkRequire('jb_outbound_id');
            $this->Validation->checkRequire('jod_jog_id');
            $this->Validation->checkRequire('jod_jid_id');
            $this->Validation->checkRequire('jod_gdu_id');
            $this->Validation->checkRequire('jod_whs_id');
            $this->Validation->checkRequire('jod_quantity');
            $this->Validation->checkRequire('jod_jog_quantity');
            $this->Validation->checkRequire('jod_jid_stock');
            if ($this->getStringParameter('jod_gd_sn', 'N') === 'Y') {
                $this->Validation->checkFloat('jod_quantity', 1, 1);
            }
            if ($this->isValidParameter('jod_jog_quantity') === true) {
                $this->Validation->checkFloat('jod_quantity', 1, $this->getFloatParameter('jod_jog_quantity'));
            }
            if ($this->isValidParameter('jod_jid_stock') === true) {
                $this->Validation->checkFloat('jod_quantity', 1, $this->getFloatParameter('jod_jid_stock'));
            }
        } elseif ($this->getFormAction() === 'doDeletePickingStorage') {
            $this->Validation->checkRequire('jod_id_del');
        } elseif ($this->getFormAction() === 'doActionEndPicking') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doActionStartUnBundling') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doCreateBundle') {
            $this->Validation->checkRequire('jb_id');
            $this->Validation->checkRequire('jb_jog_id');
            $this->Validation->checkRequire('jog_gd_id');
            $this->Validation->checkRequire('jbd_user_add');
            $this->Validation->checkRequire('jbd_us_id_add');
            $this->Validation->checkRequire('jog_quantity');
            $this->Validation->checkRequire('jbd_quantity_add');
            $this->Validation->checkCreatingNewBundle('jbd_quantity_add', $this->getIntParameter('jb_id'), $this->getFloatParameter('jog_quantity'), $this->User->getId());
            if ($this->getStringParameter('jog_gd_sn', 'N') === 'Y') {
                $this->Validation->checkRequire('jbd_serial_number_add');
                $message = Trans::getWord('unique', 'validation', '', ['attribute' => 'Serial Number']);
                $wheres = [];
                $wheres[] = '(jbd_deleted_on is null)';
                $wheres[] = "(jbd_serial_number = '" . $this->getStringParameter('jbd_serial_number_add') . "')";
                $wheres[] = '(jbd_jb_id = ' . $this->getIntParameter('jb_id') . ')';
                $strWhere = ' WHERE ' . implode(' AND ', $wheres);
                $query = 'SELECT jbd_id
                        FROM job_bundling_detail ' . $strWhere;
                $this->Validation->checkEmptyQueryResult('jbd_serial_number_add', $query, $message);
            }
        } elseif ($this->getFormAction() === 'doDeleteBundle') {
            $this->Validation->checkRequire('jbd_id_del');
        } elseif ($this->getFormAction() === 'doBundleMaterial') {
            $this->Validation->checkRequire('jbm_jog_id');
            $this->Validation->checkRequire('jbm_quantity');
            $this->Validation->checkRequire('jbm_condition');
            $this->Validation->checkRequire('jbm_gd_id');
            $this->Validation->checkRequire('jbm_gd_sn');
            $this->Validation->checkRequire('jbm_gd_receive_sn');
            $this->Validation->checkRequire('jbm_gd_packing');
            $this->Validation->checkRequire('jbm_gd_expired');
            $this->Validation->checkRequire('jbm_gd_tonnage');
            $this->Validation->checkRequire('jbm_gd_tonnage_dm');
            $this->Validation->checkRequire('jbm_gd_cbm');
            $this->Validation->checkRequire('jbm_gd_cbm_dm');
            if ($this->getStringParameter('jbm_gd_receive_sn', 'N') === 'Y') {
                $this->Validation->checkFloat('jbm_quantity', 1, 1);
                $this->Validation->checkRequire('jbm_serial_number', 3);
            } else {
                $this->Validation->checkFloat('jbm_quantity', 1);
            }
            if ($this->getStringParameter('jbm_gd_packing', 'N') === 'Y') {
                $this->Validation->checkRequire('jbm_packing_number');
            }
            if ($this->getStringParameter('jbm_gd_expired', 'N') === 'Y') {
                $this->Validation->checkRequire('jbm_expired_date');
            }
            if (($this->getStringParameter('jbm_gd_tonnage', 'N') === 'Y') ||
                (($this->getStringParameter('jbm_gd_tonnage_dm', 'N') === 'Y') && ($this->getStringParameter('jbm_condition', 'N') === 'N'))) {
                $this->Validation->checkRequire('jbm_weight');
                $this->Validation->checkFloat('jbm_weight');
                if ($this->isValidParameter('jbm_gd_min_tonnage')) {
                    $this->Validation->checkMinValue('jbm_weight', $this->getFloatParameter('jbm_gd_min_tonnage'));
                }
                if ($this->isValidParameter('jbm_gd_max_tonnage')) {
                    $this->Validation->checkMaxValue('jbm_weight', $this->getFloatParameter('jbm_gd_max_tonnage'));
                }
            }
            if ($this->getStringParameter('jbm_gd_cbm', 'N') === 'Y') {
                $this->Validation->checkRequire('jbm_length');
                $this->Validation->checkRequire('jbm_height');
                $this->Validation->checkRequire('jbm_width');
            }
            if ($this->isValidParameter('jbm_serial_number')) {
                $jir = new InboundReceiveSn();
                $jir->JirId = $this->getIntParameter('jir_id', 0);
                $jir->JiId = $this->getIntParameter('ji_id', 0);
                $jir->WhId = $this->getIntParameter('jb_wh_id', 0);
                $jir->GdId = $this->getIntParameter('jbm_gd_id', 0);
                $this->Validation->checkInboundReceiveSn('jbm_serial_number', $jir);

                $this->Validation->checkUnique('jbm_serial_number', 'job_bundling_material', [
                    'jbm_id' => $this->getIntParameter('jbm_id')
                ], [
                    'jbm_jbd_id' => $this->getIntParameter('jbd_id')
                ]);
            }
            if ($this->getStringParameter('jbm_condition', 'Y') === 'N') {
                $this->Validation->checkRequire('jbm_stored');
                $this->Validation->checkRequire('jbm_gdt_id');
                $this->Validation->checkRequire('jbm_gcd_id');
            }
            if ($this->isValidParameter('jbm_gd_expired')) {
                $this->Validation->checkRequire('jbm_expired_date');
                $this->Validation->checkDate('jbm_expired_date');
            }
        } elseif ($this->getFormAction() === 'doCompleteBundle') {
            $this->Validation->checkRequire('jbd_id');
            if ($this->getStringParameter('jog_gd_sn', 'N') === 'Y') {
                $this->Validation->checkRequire('jbd_serial_number');
                $this->Validation->checkUnique('jbd_serial_number', 'job_bundling_detail', [
                    'jbd_id' => $this->getIntParameter('jbd_id'),
                ], [
                    'jbd_deleted_on' => null,
                    'jbd_jb_id' => $this->getIntParameter('jb_id'),
                ]);
            }
            if ($this->isValidParameter('jbm_id_array') === true) {
                $jbmIds = $this->getArrayParameter('jbm_id_array');
                foreach ($jbmIds as $key => $id) {
                    $goods = GoodsDao::getByReference($this->getIntParameter('jbm_gd_id' . '_' . $id));
                    if ($goods['gd_sn'] === 'Y') {
                        $this->Validation->checkRequire('jbm_serial_number' . '_' . $id);
                    }
                    if ($goods['gd_packing'] === 'Y') {
                        $this->Validation->checkRequire('jbm_packing_number' . '_' . $id);
                    }
                    if ($goods['gd_tonnage'] === 'Y') {
                        $this->Validation->checkRequire('jbm_weight' . '_' . $id);
                        $this->Validation->checkFloat('jbm_weight' . '_' . $id);
                    }
                    if ($goods['gd_expired'] === 'Y') {
                        $this->Validation->checkRequire('jbm_expired_date' . '_' . $id);
                        $this->Validation->checkDate('jbm_expired_date' . '_' . $id);
                    }
                }
            }
        } elseif ($this->getFormAction() === 'doActionEndUnBundling') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doActionStartPutAway') {
            $this->loadActionValidationRole();
        } elseif ($this->getFormAction() === 'doInsertPutAway') {
            $this->Validation->checkRequire('jid_whs_id');
            $this->Validation->checkRequire('jb_inbound_id');
            $this->Validation->checkRequire('jid_jir_id');
            $this->Validation->checkRequire('jid_gd_id');
            $this->Validation->checkRequire('jid_gdu_id');
            $this->Validation->checkRequire('jid_quantity');
            $this->Validation->checkRequire('jid_jir_quantity');
            if ($this->getStringParameter('jid_gd_sn', 'N') === 'Y') {
                $this->Validation->checkRequire('jid_serial_number');
                $this->Validation->checkFloat('jid_quantity', 1, 1);
                if (($this->isValidParameter('jb_inbound_id') === true) && ($this->isValidParameter('jid_serial_number') === true) && ($this->isValidParameter('jid_gd_id') === true)) {
                    $this->Validation->checkInboundSerialNumber('jid_serial_number', $this->getIntParameter('jb_inbound_id'), $this->getIntParameter('jid_id', 0), $this->getIntParameter('jid_gd_id'));
                }
            } else {
                $this->Validation->checkFloat('jid_quantity', 1, $this->getFloatParameter('jid_jir_quantity', 0.0));
            }
        } elseif ($this->getFormAction() === 'doDeletePutAway') {
            $this->Validation->checkRequire('jid_id_del');
        } elseif ($this->getFormAction() === 'doActionEndPutAway') {
            $this->loadActionValidationRole();
        }
        parent::loadValidationRole();
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
                'value' => $this->getStringParameter('jo_pic'),
            ],
            [
                'label' => Trans::getWord('customerRef'),
                'value' => $this->getStringParameter('jo_customer_ref'),
            ],
            [
                'label' => Trans::getWord('orderDate'),
                'value' => DateTimeParser::format($this->getStringParameter('jo_order_date'), 'Y-m-d', 'd M Y'),
            ],
            [
                'label' => Trans::getWord('orderOffice'),
                'value' => $this->getStringParameter('jo_order_of'),
            ],
            [
                'label' => Trans::getWord('invoiceOffice'),
                'value' => $this->getStringParameter('jo_invoice_of'),
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
        $portlet->addText($this->Field->getHidden('jo_srt_id', $this->getIntParameter('jo_srt_id')));
        $portlet->addText($this->Field->getHidden('jo_aju_ref', $this->getStringParameter('jo_aju_ref')));
        $portlet->addText($this->Field->getHidden('jo_so_id', $this->getIntParameter('jo_so_id')));
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    public function getDetailFieldSet(): Portlet
    {
        $etaTime = '';
        if ($this->isValidParameter('jb_et_date') === true) {
            if ($this->isValidParameter('jb_et_time') === true) {
                $etaTime = DateTimeParser::format($this->getStringParameter('jb_et_date') . ' ' . $this->getStringParameter('jb_et_time'), 'Y-m-d H:i:s', 'H:i - d M Y');
            } else {
                $etaTime = DateTimeParser::format($this->getStringParameter('jb_et_date'), 'Y-m-d', 'd M Y');
            }
        }
        $goodsDao = new GoodsDao();
        $number = new NumberFormatter();
        $goods = $goodsDao->formatFullName($this->getStringParameter('jog_gd_category'), $this->getStringParameter('jog_gd_brand'), $this->getStringParameter('jog_goods'));
        $quantity = $number->doFormatFloat($this->getFloatParameter('jog_quantity')) . ' ' . $this->getStringParameter('jog_unit');
        $content = StringFormatter::generateCustomTableView([
            [
                'label' => Trans::getWord('warehouse'),
                'value' => $this->getStringParameter('jb_warehouse'),
            ],
            [
                'label' => Trans::getWord('planningTime'),
                'value' => $etaTime,
            ],
            [
                'label' => Trans::getWord('sku'),
                'value' => $this->getStringParameter('jog_gd_sku'),
            ],
            [
                'label' => Trans::getWord('goods'),
                'value' => $goods,
            ],
            [
                'label' => Trans::getWord('quantity'),
                'value' => $quantity,
            ],
            [
                'label' => Trans::getWord('requiredSn'),
                'value' => StringFormatter::generateYesNoLabel($this->getStringParameter('jog_gd_sn')),
            ],
        ]);
        # Create a portlet box.
        $portlet = new Portlet('JowDetailPtl', Trans::getWord('jobDetail'));
        $portlet->addText($content);
        $portlet->addText($this->Field->getHidden('jb_id', $this->getIntParameter('jb_id')));
        $portlet->addText($this->Field->getHidden('jb_wh_id', $this->getIntParameter('jb_wh_id')));
        $portlet->addText($this->Field->getHidden('jb_et_date', $this->getStringParameter('jb_et_date')));
        $portlet->addText($this->Field->getHidden('jb_et_time', $this->getStringParameter('jb_et_time')));
        $portlet->addText($this->Field->getHidden('jb_jog_id', $this->getIntParameter('jb_jog_id')));
        $portlet->addText($this->Field->getHidden('jog_gd_id', $this->getIntParameter('jog_gd_id')));
        $portlet->addText($this->Field->getHidden('jog_gd_sn', $this->getStringParameter('jog_gd_sn')));
        $portlet->addText($this->Field->getHidden('jog_quantity', $this->getFloatParameter('jog_quantity')));
        $portlet->addText($this->Field->getHidden('jb_outbound_id', $this->getIntParameter('jb_outbound_id')));
        $portlet->addText($this->Field->getHidden('jb_inbound_id', $this->getIntParameter('jb_inbound_id')));
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getMaterialFieldSet(): Portlet
    {
        $table = new Table('JoGmTbl');
        $table->setHeaderRow([
            'gm_gd_sku' => Trans::getWord('sku'),
            'gm_goods' => Trans::getWord('goods'),
            'gm_quantity' => Trans::getWord('composition'),
            'gm_required' => Trans::getWord('qtyRestore'),
            'gm_uom_code' => Trans::getWord('uom'),
        ]);
        $data = GoodsMaterialDao::loadDataWithStock($this->getIntParameter('jog_gd_id'), $this->getIntParameter('jb_wh_id'));
        $rows = [];
        $gdDao = new GoodsDao();
        foreach ($data as $row) {
            $row['gm_goods'] = $gdDao->formatFullName($row['gm_gdc_name'], $row['gm_br_name'], $row['gm_gd_name']);
            $required = (float)$row['gm_quantity'] * $this->getFloatParameter('jog_quantity');
            $row['gm_required'] = $required;
            $rows[] = $row;
        }
        $table->addRows($rows);
        # Create a portlet box.
        $portlet = new Portlet('JoGmPtl', Trans::getWord('billOfMaterials'));
        $table->setColumnType('gm_quantity', 'float');
        $table->setColumnType('gm_required', 'float');
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the material from job goods field Set.
     *
     * @return Portlet
     */
    private function getJogMaterialFieldSet(): Portlet
    {
        $table = new Table('JoJogTbl');
        $table->setHeaderRow([
            'jog_gd_sku' => Trans::getWord('sku'),
            'jog_name' => Trans::getWord('goods'),
            'jog_quantity' => Trans::getWord('qtyRestore'),
            'jog_unit' => Trans::getWord('uom'),
        ]);
        $data = $this->loadJogData();
        $table->addRows($data);
        # Create a portlet box.
        $portlet = new Portlet('JoJogPtl', Trans::getWord('billOfMaterials'));
        $table->setColumnType('jog_quantity', 'float');
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the material from job goods field Set.
     *
     * @return array
     */
    private function loadJogData(): array
    {
        $results = [];
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $this->getDetailReferenceValue() . ')';
        $wheres[] = '(jog.jog_id <> ' . $this->getIntParameter('jb_jog_id') . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jog.jog_id, jog.jog_gd_id, gd.gd_sku as jog_gd_sku, gd.gd_name as jog_gd_name,
                    br.br_name as jog_gd_brand, gdc.gdc_name as jog_gd_category, jog.jog_name, jog.jog_quantity,
                    uom.uom_code as jog_unit
                FROM job_goods as jog INNER JOIN
                goods as gd ON jog.jog_gd_id = gd.gd_id INNER JOIN
                brand as br ON br.br_id = gd.gd_br_id INNER JOIN
                goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id INNER JOIN
                goods_unit as gdu ON gdu.gdu_id = jog.jog_gdu_id INNER JOIN
                unit as uom ON uom.uom_id = gdu.gdu_uom_id ';
        $query .= $strWheres;
        $sqlResults = DB::select($query);
        if (empty($sqlResults) === false) {
            $results = DataParser::arrayObjectToArray($sqlResults);
        }

        return $results;
    }


    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {

        parent::loadDefaultButton();
        if ($this->isValidParameter('ji_end_load_on') === true && $this->isAllowUpdateAction()) {
            $pdfButton = new PdfButton('JiPrint', Trans::getWord('printPdf'), 'goodreceipt');
            $pdfButton->setIcon(Icon::Download)->btnPrimary()->pullRight()->btnMedium();
            $pdfButton->addParameter('jo_id', $this->getDetailReferenceValue());
            $this->View->addButton($pdfButton);
        }
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getStorageFieldSet(): Portlet
    {
        $modal = $this->getPickingStorageModal();
        $modalDelete = $this->getPickingStorageDeleteModal();

        $table = new Table('JoJwdTbl');
        $table->setHeaderRow([
            'jod_storage' => Trans::getWord('storage'),
            'jod_gd_sku' => Trans::getWord('sku'),
            'jod_goods' => Trans::getWord('goods'),
            'jod_lot_number' => Trans::getWord('lotNumber'),
            'jod_jid_serial_number' => Trans::getWord('serialNumber'),
            'jod_quantity' => Trans::getWord('qtyPicking'),
            'jod_unit' => Trans::getWord('uom'),
        ]);
        $wheres = [];
        $wheres[] = '(jod.jod_job_id = ' . $this->getIntParameter('jb_outbound_id') . ')';
        if ($this->isJobDeleted() === false) {
            $wheres[] = '(jod.jod_deleted_on IS NULL)';
        }
        $data = JobOutboundDetailDao::loadData($wheres);
        $rows = [];
        $gdDao = new GoodsDao();
        foreach ($data as $row) {
            $row['jod_goods'] = $gdDao->formatFullName($row['jod_gdc_name'], $row['jod_br_name'], $row['jod_gd_name']);
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('jod_quantity', 'float');
        $table->setFooterType('jod_quantity', 'SUM');
        $table->addColumnAttribute('jod_lot_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_uom', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_jid_serial_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_storage', 'style', 'text-align: center;');
        $table->addColumnAttribute('jod_gd_sku', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('JoJbJodPtl', Trans::getWord('goodsTaken'));
        if ($this->isValidParameter('jb_end_pick_on') === false && $this->isAllowUpdateAction()) {
            $this->View->addModal($modal);
            $this->View->addModal($modalDelete);

            $table->setDeleteActionByModal($modalDelete, 'jobOutboundDetail', 'getByReferenceForDelete', ['jod_id']);

            $btnCpMdl = new ModalButton('btnJoJbJodMdl', Trans::getWord('pickGoods'), $modal->getModalId());
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
    protected function getPickingStorageModal(): Modal
    {
        $modal = new Modal('JobJodMdl', Trans::getWord('pickGoods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doInsertPickingStorage');
        $showModal = false;
        if ($this->getFormAction() === 'doInsertPickingStorage' && $this->isValidPostValues() === false) {
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
            'jog_production_number' => Trans::getWord('productionNumber'),
            'jog_quantity' => Trans::getWord('quantity'),
            'jog_uom' => Trans::getWord('uom'),
        ]);
        $jogField->setAutoCompleteFields([
            'jod_jog_number' => 'jog_serial_number',
            'jod_gd_sku' => 'jog_gd_sku',
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
        $jogField->addParameter('jog_id', $this->getIntParameter('jb_jog_id'));
        $jogField->addParameter('job_id', $this->getIntParameter('jb_outbound_id'));
        $jogField->addClearField('jod_jid_stock');
        $jogField->addClearField('jod_wh_name');
        $jogField->addClearField('jod_jid_id');
        $jogField->setParentModal($modal->getModalId());
        $this->View->addModal($jogField->getModal());


        # Create Goods Field
        $jidField = $this->Field->getSingleSelectTable('jobInboundDetail', 'jod_whs_name', $this->getParameterForModal('jod_whs_name', $showModal), 'loadDataForOutbound');
        $jidField->setHiddenField('jod_whs_id', $this->getParameterForModal('jod_whs_id', $showModal));
        $jidField->setTableColumns([
            'jid_whs_name' => Trans::getWord('storage'),
            'jid_lot_number' => Trans::getWord('lotNumber'),
            'jid_serial_number' => Trans::getWord('serialNumber'),
            'jid_stock' => Trans::getWord('stockAvailable'),
            'jid_uom' => Trans::getWord('uom'),
        ]);
        $jidField->setAutoCompleteFields([
            'jod_jid_id' => 'jid_id',
            'jod_jid_stock' => 'jid_stock',
            'jod_jid_stock_number' => 'jid_stock_number',
            'jod_lot_number' => 'jid_lot_number',
            'jod_jid_serial_number' => 'jid_serial_number',
        ]);
        $jidField->setFilters([
            'jid_whs_name' => Trans::getWord('storage'),
            'jid_serial_number' => Trans::getWord('serialNumber'),
        ]);
        $jidField->setValueCode('jid_whs_id');
        $jidField->setLabelCode('jid_whs_name');

        $jidField->addParameterById('jid_gd_id', 'jod_gd_id', Trans::getWord('goods'));
        $jidField->addParameterById('jid_gdu_id', 'jod_gdu_id', Trans::getWord('uom'));
        $jidField->addOptionalParameterById('jid_lot_number', 'jod_jog_production_number');
        $jidField->addParameter('wh_id', $this->getIntParameter('jb_wh_id'));
        $jidField->addParameter('job_id', $this->getIntParameter('jb_outbound_id'));
        $jidField->addParameter('jid_damage', 'N');
        $jidField->addClearField('jod_jid_stock');
        $jidField->addClearField('jod_jid_stock_number');
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
        $fieldSet->addField(Trans::getWord('goodsId'), $jogNumberField);
        $fieldSet->addField(Trans::getWord('sku'), $jogSkuField);
        $fieldSet->addField(Trans::getWord('lotNumber'), $productionField);
        $fieldSet->addField(Trans::getWord('serialNumber'), $jidSnField);
        $fieldSet->addHiddenField($this->Field->getHidden('jod_gd_id', $this->getParameterForModal('jod_gd_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jod_lot_number', $this->getParameterForModal('jod_lot_number', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jod_gdu_id', $this->getParameterForModal('jod_gdu_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jod_jid_id', $this->getParameterForModal('jod_jid_id', $showModal)));
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
    protected function getPickingStorageDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JbJodDelMdl', Trans::getWord('unpickGoods'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeletePickingStorage');
        $showModal = false;
        if ($this->getFormAction() === 'doDeletePickingStorage' && $this->isValidPostValues() === false) {
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
     * Function to get the bundling Field Set.
     *
     * @return Portlet
     */
    protected function getBundlingFieldSet(): Portlet
    {
        $modal = $this->getCreateBundleModal();
        $modalDelete = $this->getDeleteBundleModal();

        $table = new Table('JoJbdTbl');
        $table->setHeaderRow([
            'jbd_user' => Trans::getWord('officer'),
            'jbd_lot_number' => Trans::getWord('lotNumber'),
            'jbd_serial_number' => Trans::getWord('serialNumber'),
            'jbd_quantity' => Trans::getWord('quantity'),
            'jbd_uom_code' => Trans::getWord('uom'),
            'jbd_status' => Trans::getWord('status'),
        ]);
        $wheres = [];
        if ($this->PageSetting->checkPageRight('AllowSeeAllOfficerBundle') === false) {
            $wheres[] = '(jbd.jbd_us_id = ' . $this->User->getId() . ')';
        }
        $wheres[] = '(jbd.jbd_jb_id = ' . $this->getIntParameter('jb_id') . ')';
        $wheres[] = '(jbd.jbd_deleted_on IS NULL)';
        $data = JobBundlingDetailDao::loadData($wheres, 30);
        $rows = [];
        foreach ($data as $row) {
            $status = new LabelWarning(Trans::getWord('inProgress'));
            if (empty($row['jbd_end_on']) === false) {
                $status = new LabelSuccess(Trans::getWord('complete'));
            }
            $row['jbd_status'] = $status;

            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->setColumnType('jbd_quantity', 'float');
        $table->setFooterType('jbd_quantity', 'SUM');
        $table->addColumnAttribute('jbd_lot_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jbd_uom_code', 'style', 'text-align: center;');
        $table->addColumnAttribute('jbd_serial_number', 'style', 'text-align: center;');
        $table->addColumnAttribute('jbd_status', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('JoJbJbdPtl', Trans::getWord('unBundling'));
        if ($this->isValidParameter('jb_end_pack_on') === false && $this->isAllowUpdateAction()) {
            $this->View->addModal($modal);
            $this->View->addModal($modalDelete);
            $table->setDeleteActionByModal($modalDelete, 'jbd', 'getByReferenceForDelete', ['jbd_id']);
            $btnAddMdl = new ModalButton('btnJoJbdAddMdl', Trans::getWord('createBundle'), $modal->getModalId());
            $btnAddMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnAddMdl);
        }

        $portlet->addTable($table);

        return $portlet;
    }


    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    protected function getCreateBundleModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoJbdCreateMdl', Trans::getWord('actionConfirmation'));
        $text = Trans::getWord('createBundleConfirmation', 'message');
        $modal->setFormSubmit($this->getMainFormId(), 'doCreateBundle');
        $showModal = false;
        if ($this->getFormAction() === 'doCreateBundle' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        if ($this->PageSetting->checkPageRight('AllowSeeAllOfficerBundle') === false) {
            $officerField = $this->Field->getText('jbd_user_add', $this->User->getName());
            $officerField->setReadOnly();
            $fieldSet->addHiddenField($this->Field->getHidden('jbd_us_id_add', $this->User->getId()));
        } else {
            $officerField = $this->Field->getSingleSelect('user', 'jbd_user_add', $this->getParameterForModal('jbd_user_add', $showModal), 'loadOfficerJob');
            $officerField->setHiddenField('jbd_us_id_add', $this->getParameterForModal('jbd_us_id_add', $showModal));
            $officerField->setEnableNewButton(false);
            $officerField->setEnableDetailButton(false);
            $officerField->addParameter('joo_jo_id', $this->getDetailReferenceValue());
        }
        # Create SN Field
        $snField = $this->Field->getSingleSelectTable('jbd', 'jbd_sn_add_text', $this->getParameterForModal('jbd_sn_add_text', $showModal), 'loadUnBunlingSn');
        $snField->setHiddenField('jbd_serial_number_add', $this->getParameterForModal('jbd_serial_number_add', $showModal));
        $snField->setTableColumns([
            'jod_serial_number' => Trans::getWord('serialNumber'),
            'jod_lot_number' => Trans::getWord('lotNumber'),
        ]);
        $snField->setAutoCompleteFields([
            'jbd_lot_number_add' => 'jod_lot_number',
        ]);
        $snField->setFilters([
            'jod_serial_number' => Trans::getWord('serialNumber'),
        ]);
        $snField->setValueCode('jod_serial_number');
        $snField->setLabelCode('jod_serial_number');
        $snField->addParameter('jbd_jb_id', $this->getIntParameter('jb_id'));
        $snField->addParameter('jb_outbound_id', $this->getIntParameter('jb_outbound_id'));
        $snField->setParentModal($modal->getModalId());
        $this->View->addModal($snField->getModal());


        $qtyField = $this->Field->getText('jbd_quantity_add', '1');
        $qtyField->setReadOnly();
        $fieldSet->addField(Trans::getWord('officer'), $officerField, true);
        $fieldSet->addField(Trans::getWord('quantity'), $qtyField);
        $fieldSet->addField(Trans::getWord('serialNumber'), $snField);
        if ($this->getStringParameter('jog_gd_sn', 'N') === 'Y') {
            $fieldSet->setRequiredFields(['jbd_serial_number_add']);
        }
        $fieldSet->addHiddenField($this->Field->getHidden('jbd_lot_number_add', $this->getParameterForModal('jbd_lot_number_add', $showModal)));
        $modal->setBtnOkName(Trans::getWord('yesCreate'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    protected function getDeleteBundleModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoJbdDelMdl', Trans::getWord('deleteConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteBundle');

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $officerField = $this->Field->getText('jbd_user_del', $this->getParameterForModal('jbd_user_del'));
        $officerField->setReadOnly();

        $qtyField = $this->Field->getText('jbd_quantity_del', $this->getParameterForModal('jbd_quantity_del'));
        $qtyField->setReadOnly();

        $snField = $this->Field->getText('jbd_serial_number_del', $this->getParameterForModal('jbd_serial_number_del'));
        $snField->setReadOnly();

        $lotField = $this->Field->getText('jbd_lot_number_del', $this->getParameterForModal('jbd_lot_number_del'));
        $lotField->setReadOnly();

        $fieldSet->addField(Trans::getWord('officer'), $officerField);
        $fieldSet->addField(Trans::getWord('quantity'), $qtyField);
        $fieldSet->addField(Trans::getWord('serialNumber'), $snField);
        $fieldSet->addField(Trans::getWord('lotNumber'), $lotField);
        $fieldSet->addHiddenField($this->Field->getHidden('jbd_id_del', $this->getParameterForModal('jbd_id_del')));
        $fieldSet->addHiddenField($this->Field->getHidden('jbd_us_id_dell', $this->getParameterForModal('jbd_us_id_dell')));

        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get the warehouse Field Set.
     *
     * @return Portlet
     */
    private function getBundlingProgressFieldSet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(4);
        $officerField = $this->Field->getText('jbd_user', $this->getStringParameter('jbd_user'));
        $officerField->setReadOnly();

        # Create SN Field
        if ($this->isValidParameter('jbd_sn_text') === false) {
            $this->setParameter('jbd_sn_text', $this->getStringParameter('jbd_serial_number'));
        }
        $snField = $this->Field->getSingleSelectTable('jbd', 'jbd_sn_text', $this->getStringParameter('jbd_sn_text'), 'loadUnBunlingSn');
        $snField->setHiddenField('jbd_serial_number', $this->getStringParameter('jbd_serial_number'));
        $snField->setTableColumns([
            'jod_serial_number' => Trans::getWord('serialNumber'),
            'jod_lot_number' => Trans::getWord('lotNumber'),
        ]);
        $snField->setAutoCompleteFields([
            'jbd_lot_number' => 'jod_lot_number',
        ]);
        $snField->setFilters([
            'jod_serial_number' => Trans::getWord('serialNumber'),
        ]);
        $snField->setValueCode('jod_serial_number');
        $snField->setLabelCode('jod_serial_number');
        $snField->addParameter('jbd_id', $this->getIntParameter('jbd_id'));
        $snField->addParameter('jbd_jb_id', $this->getIntParameter('jb_id'));
        $snField->addParameter('jb_outbound_id', $this->getIntParameter('jb_outbound_id'));
        $this->View->addModal($snField->getModal());


        $lotNumber = $this->Field->getText('jbd_lot_number', $this->getStringParameter('jbd_lot_number'));
        $lotNumber->setReadOnly();
        # Add field to fieldset
        $fieldSet->addField(Trans::getWord('officer'), $officerField);
        $fieldSet->addField(Trans::getWord('lotNumber'), $lotNumber);
        $fieldSet->addField(Trans::getWord('serialNumber'), $snField);
        $fieldSet->addHiddenField($this->Field->getHidden('jbd_id', $this->getIntParameter('jbd_id')));
        if ($this->getStringParameter('jog_gd_sn', 'N') === 'Y') {
            $fieldSet->setRequiredFields(['jbd_sn_text']);
        }
        # Create a portlet box.
        $portlet = new Portlet('JoJbdPtl', Trans::getWord('bundlingDetail'));
        $saveBtn = new SubmitButton('btnJbmComplete', Trans::getWord('saveBundle'), 'doCompleteBundle', $this->getMainFormId());
        $saveBtn->setIcon(Icon::Save)->btnPrimary()->pullRight()->btnMedium();
        $saveBtn->setEnableLoading(false);
        $portlet->addFieldSet($fieldSet);
        $portlet->addButton($saveBtn);

        return $portlet;
    }

    /**
     * Function to get the warehouse Field Set.
     *
     * @return Portlet
     */
    private function getBundlingMaterialProgressFieldSet(): Portlet
    {
        # Modal
        $modal = $this->getBundleMaterialModal();
        $this->View->addModal($modal);
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(4);
        $table = new Table('JoJbmTbl');
        $table->setHeaderRow([
            'jbm_id_array' => '',
            'jbm_gd_id' => '',
            'jbm_gd_sn' => '',
            'jbm_gd_sku' => Trans::getWord('sku'),
            'jbm_gd_name' => Trans::getWord('goods'),
            'jbm_qty_uom' => Trans::getWord('quantity'),
            'jbm_serial_number' => Trans::getWord('serialNumber'),
            'jbm_packing_number' => Trans::getWord('packingNumber'),
            'jbm_weight' => Trans::getWord('weight'),
            'jbm_expired_date' => Trans::getWord('expiredDate'),
        ]);
        $data = $this->loadJobBundlingMaterialData();
        $table->addRows($data);
        $table->addColumnAttribute('jbm_qty_uom', 'style', 'text-align: right;');
        $table->addColumnAttribute('jbm_gd_sku', 'style', 'text-align: center;');
        $table->setUpdateActionByModal($modal, 'jobBundlingMaterial', 'getByIdForUpdate', ['jbm_id']);
        # Create a portlet box.
        $portlet = new Portlet('JoJbdMaterialPtl', Trans::getWord('bundlingMaterial'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    private function getBundleMaterialModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoJbmMdl', Trans::getWhsWord('bundleMaterial'));
        $modal->setFormSubmit($this->getMainFormId(), 'doBundleMaterial');
        $showModal = false;
        if ($this->getFormAction() === 'doBundleMaterial' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $modal->addFieldSet($this->getBundleMaterialFieldSet($showModal));

        return $modal;
    }

    /**
     * Function to get operator modal.
     *
     * @param bool $showModal To trigger modal.
     *
     * @return FieldSet
     */
    protected function getBundleMaterialFieldSet(bool $showModal): FieldSet
    {
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $skuField = $this->Field->getText('jbm_gd_sku', $this->getParameterForModal('jbm_gd_sku', $showModal));
        $skuField->setReadOnly();
        $goodsField = $this->Field->getText('jbm_goods', $this->getParameterForModal('jbm_goods', $showModal));
        $goodsField->setReadOnly();
        $unitField = $this->Field->getText('jbm_uom_code', $this->getParameterForModal('jbm_uom_code', $showModal));
        $unitField->setReadOnly();
        $condition = $this->Field->getRadioGroup('jbm_condition', $this->getParameterForModal('jbm_condition', $showModal));
        $condition->addRadios([
            'Y' => Trans::getWord('good'),
            'N' => Trans::getWord('damage'),
        ]);
        $stored = $this->Field->getRadioGroup('jbm_stored', $this->getParameterForModal('jbm_stored', $showModal));
        $stored->addRadios([
            'Y' => Trans::getWord('accept'),
            'N' => Trans::getWord('reject'),
        ]);
        # Create damage type Field
        $damageTypeField = $this->Field->getSingleSelect('goodsDamageType', 'jbm_gdt_description', $this->getParameterForModal('jbm_gdt_description', $showModal));
        $damageTypeField->setHiddenField('jbm_gdt_id', $this->getParameterForModal('jbm_gdt_id', $showModal));
        $damageTypeField->addParameter('gdt_ss_id', $this->User->getSsId());
        $damageTypeField->setEnableDetailButton(false);
        $damageTypeField->setEnableNewButton(false);

        # Create damage type Field
        $damageCauseField = $this->Field->getSingleSelect('goodsCauseDamage', 'jbm_gcd_description', $this->getParameterForModal('jbm_gcd_description', $showModal));
        $damageCauseField->setHiddenField('jbm_gcd_id', $this->getParameterForModal('jbm_gcd_id', $showModal));
        $damageCauseField->addParameter('gcd_ss_id', $this->User->getSsId());
        $damageCauseField->setEnableDetailButton(false);
        $damageCauseField->setEnableNewButton(false);
        # Create damage type Field
        $packingField = $this->Field->getText('jbm_packing_number', $this->getParameterForModal('jbm_packing_number', $showModal));
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('sku'), $skuField);
        $fieldSet->addField(Trans::getWord('goods'), $goodsField);
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getNumber('jbm_quantity', $this->getParameterForModal('jbm_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getWord('uom'), $unitField);
        $fieldSet->addField(Trans::getWord('lotNumber'), $this->Field->getText('jbm_lot_number', $this->getParameterForModal('jbm_lot_number', $showModal)));
        $fieldSet->addField(Trans::getWord('serialNumber'), $this->Field->getText('jbm_serial_number', $this->getParameterForModal('jbm_serial_number', $showModal)));
        $fieldSet->addField(Trans::getWord('condition'), $condition, true);
        $fieldSet->addField(Trans::getWord('stored'), $stored);
        $fieldSet->addField(Trans::getWord('packingNumber'), $packingField);
        $fieldSet->addField(Trans::getWord('damageType'), $damageTypeField);
        $fieldSet->addField(Trans::getWord('causeDamage'), $damageCauseField);
        $fieldSet->addField(Trans::getWord('damageTypeRemark'), $this->Field->getText('jbm_gdt_remark', $this->getParameterForModal('jbm_gdt_remark', $showModal)));
        $fieldSet->addField(Trans::getWord('causeDamageRemark'), $this->Field->getText('jbm_gcd_remark', $this->getParameterForModal('jbm_gcd_remark', $showModal)));
        $fieldSet->addField(Trans::getWord('weight') . ' (KG)', $this->Field->getNumber('jbm_weight', $this->getParameterForModal('jbm_weight', $showModal)));
        $fieldSet->addField(Trans::getWord('length') . ' (M)', $this->Field->getNumber('jbm_length', $this->getParameterForModal('jbm_length', $showModal)));
        $fieldSet->addField(Trans::getWord('width') . ' (M)', $this->Field->getNumber('jbm_width', $this->getParameterForModal('jbm_width', $showModal)));
        $fieldSet->addField(Trans::getWord('height') . ' (M)', $this->Field->getNumber('jbm_height', $this->getParameterForModal('jbm_height', $showModal)));
        $fieldSet->addField(Trans::getWord('expiredDate'), $this->Field->getCalendar('jbm_expired_date', $this->getParameterForModal('jbm_expired_date', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_jog_id', $this->getParameterForModal('jbm_jog_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_gd_id', $this->getParameterForModal('jbm_gd_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_id', $this->getParameterForModal('jbm_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_gd_tonnage', $this->getParameterForModal('jbm_gd_tonnage', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_gd_cbm', $this->getParameterForModal('jbm_gd_cbm', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_gd_sn', $this->getParameterForModal('jbm_gd_sn', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_gd_receive_sn', $this->getParameterForModal('jbm_gd_receive_sn', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_gd_packing', $this->getParameterForModal('jbm_gd_packing', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_gd_tonnage_dm', $this->getParameterForModal('jbm_gd_tonnage_dm', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_gd_min_tonnage', $this->getParameterForModal('jbm_gd_min_tonnage', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_gd_max_tonnage', $this->getParameterForModal('jbm_gd_max_tonnage', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_gd_cbm_dm', $this->getParameterForModal('jbm_gd_cbm_dm', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_gd_min_cbm', $this->getParameterForModal('jbm_gd_min_cbm', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_gd_max_cbm', $this->getParameterForModal('jbm_gd_max_cbm', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jbm_gd_expired', $this->getParameterForModal('jbm_gd_expired', $showModal)));

        return $fieldSet;
    }


    /**
     * Function to load job bundling material data.
     *
     * @return array
     */
    private function loadJobBundlingMaterialData(): array
    {
        $results = JobBundlingMaterialDao::getByJobBundlingDetail($this->getIntParameter('jbd_id'));
        $length = count($results);
        if ($length > 0) {
            $number = new NumberFormatter();
            for ($i = 0; $i < $length; $i++) {
                $postFix = '_' . $results[$i]['jbm_id'];
                $results[$i]['jbm_required_sn'] = $results[$i]['jbm_gd_sn'];
                $results[$i]['jbm_qty_uom'] = $number->doFormatFloat((float)$results[$i]['jbm_quantity']) . ' ' . $results[$i]['jbm_uom_code'];
                $results[$i]['jbm_id_array'] = $this->Field->getHidden('jbm_id_array[' . $i . ']', $results[$i]['jbm_id']);
                $results[$i]['jbm_gd_id'] = $this->Field->getHidden('jbm_gd_id' . $postFix, $results[$i]['jbm_gd_id']);
                # Serial Number Field
                $snFieldId = 'jbm_serial_number' . $postFix;
                $snField = $this->Field->getText($snFieldId, $results[$i]['jbm_serial_number']);
                $snField->setReadOnly();
                if ($this->Validation->isValid($snFieldId) === false) {
                    $results[$i]['jbm_serial_number'] = '<div class=" form-group bad">';
                    $results[$i]['jbm_serial_number'] .= $snField;
                    $results[$i]['jbm_serial_number'] .= '<span class="input-alert">' . $this->Validation->getErrorMessage($snFieldId, Trans::getWord('serialNumber')) . '</span>';
                    $results[$i]['jbm_serial_number'] .= '</div>';
                } else {
                    $results[$i]['jbm_serial_number'] = $snField;
                }
                # packing Number Field
                $packingFieldId = 'jbm_packing_number' . $postFix;
                $packingField = $this->Field->getText($packingFieldId, $results[$i]['jbm_packing_number']);
                $packingField->setReadOnly();
                if ($this->Validation->isValid($packingFieldId) === false) {
                    $results[$i]['jbm_packing_number'] = '<div class=" form-group bad">';
                    $results[$i]['jbm_packing_number'] .= $packingField;
                    $results[$i]['jbm_packing_number'] .= '<span class="input-alert">' . $this->Validation->getErrorMessage($packingFieldId, Trans::getWord('packingNumber')) . '</span>';
                    $results[$i]['jbm_packing_number'] .= '</div>';
                } else {
                    $results[$i]['jbm_packing_number'] = $packingField;
                }
                # weight  Field
                $weightFieldId = 'jbm_weight' . $postFix;
                $weightField = $this->Field->getText($weightFieldId, $results[$i]['jbm_weight']);
                $weightField->setReadOnly();
                if ($this->Validation->isValid($weightFieldId) === false) {
                    $results[$i]['jbm_weight'] = '<div class=" form-group bad">';
                    $results[$i]['jbm_weight'] .= $weightField;
                    $results[$i]['jbm_weight'] .= '<span class="input-alert">' . $this->Validation->getErrorMessage($weightFieldId, Trans::getWord('weight')) . '</span>';
                    $results[$i]['jbm_weight'] .= '</div>';
                } else {
                    $results[$i]['jbm_weight'] = $weightField;
                }
                # Expired Field
                $xepiredFieldId = 'jbm_expired_date' . $postFix;
                $expiredDateVal = DateTimeParser::format($results[$i]['jbm_expired_date'], 'Y-m-d', 'd M Y');
                $xepiredField = $this->Field->getText($xepiredFieldId, $expiredDateVal);
                $xepiredField->setReadOnly();
                if ($this->Validation->isValid($xepiredFieldId) === false) {
                    $results[$i]['jbm_expired_date'] = '<div class=" form-group bad">';
                    $results[$i]['jbm_expired_date'] .= $xepiredField;
                    $results[$i]['jbm_expired_date'] .= '<span class="input-alert">' . $this->Validation->getErrorMessage($xepiredFieldId, Trans::getWord('expiredDate')) . '</span>';
                    $results[$i]['jbm_expired_date'] .= '</div>';
                } else {
                    $results[$i]['jbm_expired_date'] = $xepiredField;
                }
            }
        }

        return $results;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return Portlet
     */
    protected function getPutAwayPortlet(): Portlet
    {
        $table = new Table('JoJidTbl');
        $table->setHeaderRow([
            'jid_whs_name' => Trans::getWord('storage'),
            'jid_gd_sku' => Trans::getWord('sku'),
            'jid_goods' => Trans::getWord('goods'),
            'jid_lot_number' => Trans::getWord('lotNumber'),
            'jid_serial_number' => Trans::getWord('serialNumber'),
            'jid_quantity' => Trans::getWord('quantity'),
            'jid_uom' => Trans::getWord('uom'),
            'jid_total_volume' => Trans::getWord('totalVolume') . ' (M3)',
            'jid_total_weight' => Trans::getWord('totalWeight') . ' (KG)',
        ]);
        $wheres = [];
        $wheres[] = '(jid.jid_ji_id = ' . $this->getIntParameter('jb_inbound_id') . ')';
        $wheres[] = '(jid.jid_deleted_on IS NULL)';
        $wheres[] = "(jid.jid_adjustment = 'N')";

        $data = JobInboundDetailDao::loadData($wheres);
        $rows = JobInboundDetailDao::doPrepareInboundDetailData($data);
        $table->addRows($rows);
        $table->addColumnAttribute('jid_whs_name', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_gd_sku', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_serial_number', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_lot_number', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_uom', 'style', 'text-align: center');
        $table->addColumnAttribute('jid_condition', 'style', 'text-align: center');
        $table->setColumnType('jid_quantity', 'float');
        $table->setColumnType('jid_total_volume', 'float');
        $table->setColumnType('jid_total_weight', 'float');
        $table->setFooterType('jid_quantity', 'SUM');
        $table->setFooterType('jid_total_volume', 'SUM');
        $table->setFooterType('jid_total_weight', 'SUM');
        # Create a portlet box.
        $portlet = new Portlet('JoJidPtl', Trans::getWord('storage'));
        if ($this->isValidParameter('jb_end_store_on') === false && $this->isAllowUpdateAction()) {
            $modal = $this->getStorageModal();
            $this->View->addModal($modal);
            $modalDelete = $this->getPutAwayDeleteModal();
            $this->View->addModal($modalDelete);
            $table->setDeleteActionByModal($modalDelete, 'jobInboundDetail', 'getByReferenceForDelete', ['jid_id']);
            $btnCpMdl = new ModalButton('btnJoJidMdl', Trans::getWord('addStorage'), $modal->getModalId());
            $btnCpMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight();
            $portlet->addButton($btnCpMdl);
        }

        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get storage modal.
     *
     * @param string $modalId   To store the modal id.
     * @param bool   $showModal To set trigger on load mode.
     *
     * @return FieldSet
     */
    protected function getPutAwayFieldSet(string $modalId, bool $showModal): FieldSet
    {
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Create Goods Field
        $jirField = $this->Field->getSingleSelectTable('jobInboundReceive', 'jid_gd_name', $this->getParameterForModal('jid_gd_name', $showModal), 'loadPutAwayData');
        $jirField->setHiddenField('jid_jir_id', $this->getParameterForModal('jid_jir_id', $showModal));
        $jirField->setTableColumns([
            'jir_jog_number' => Trans::getWord('goodsId'),
            'jir_serial_number' => Trans::getWord('serialNumber'),
            'jir_gd_sku' => Trans::getWord('sku'),
            'jir_goods' => Trans::getWord('goods'),
            'jir_quantity_number' => Trans::getWord('qtyReceived'),
            'jir_jog_uom' => Trans::getWord('uom'),
        ]);
        $jirField->setAutoCompleteFields([
            'jid_gd_id' => 'jir_gd_id',
            'jid_gd_sku' => 'jir_gd_sku',
            'jid_serial_number' => 'jir_serial_number',
            'jid_packing_number' => 'jir_packing_number',
            'jid_lot_number' => 'jir_lot_number',
            'jid_jog_number' => 'jir_jog_number',
            'jid_jir_quantity' => 'jir_quantity',
            'jid_jir_quantity_number' => 'jir_quantity_number',
            'jid_uom' => 'jir_jog_uom',
            'jid_gdu_id' => 'jir_jog_gdu_id',
            'jid_gd_sn' => 'jir_gd_sn',
        ]);
        $jirField->setValueCode('jir_id');
        $jirField->setLabelCode('jir_goods');
        $jirField->addParameter('jir_ji_id', $this->getIntParameter('jb_inbound_id'));
        $jirField->addOptionalParameterById('jid_id', 'jid_id');
        $jirField->setParentModal($modalId);
        $this->View->addModal($jirField->getModal());


        # Create Unit Field
        $whsField = $this->Field->getSingleSelect('warehouseStorage', 'jid_whs_name', $this->getParameterForModal('jid_whs_name', $showModal));
        $whsField->setHiddenField('jid_whs_id', $this->getParameterForModal('jid_whs_id', $showModal));
        $whsField->addParameter('whs_wh_id', $this->getIntParameter('jb_wh_id'));
        $whsField->setEnableNewButton(false);
        $whsField->setEnableDetailButton(false);

        $skuField = $this->Field->getText('jid_gd_sku', $this->getParameterForModal('jid_gd_sku', $showModal));
        $skuField->setReadOnly();
        $jogNumberField = $this->Field->getText('jid_jog_number', $this->getParameterForModal('jid_jog_number', $showModal));
        $jogNumberField->setReadOnly();
        $jirQtyField = $this->Field->getNumber('jid_jir_quantity', $this->getParameterForModal('jid_jir_quantity', $showModal));
        $jirQtyField->setReadOnly();
        $uomField = $this->Field->getText('jid_uom', $this->getParameterForModal('jid_uom', $showModal));
        $uomField->setReadOnly();
        # Create SN Field
        $snField = $this->Field->getText('jid_serial_number', $this->getParameterForModal('jid_serial_number', $showModal));
        $snField->setReadOnly();
        $lotField = $this->Field->getText('jid_lot_number', $this->getParameterForModal('jid_lot_number', $showModal));
        $lotField->setReadOnly();
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('goods'), $jirField, true);
        $fieldSet->addField(Trans::getWord('storage'), $whsField, true);
        $fieldSet->addField(Trans::getWord('qtyReceived'), $jirQtyField);
        $fieldSet->addField(Trans::getWord('uom'), $uomField);
        $fieldSet->addField(Trans::getWord('qtyStore'), $this->Field->getNumber('jid_quantity', $this->getParameterForModal('jid_quantity', $showModal)), true);
        $fieldSet->addField(Trans::getWord('serialNumber'), $snField);
        $fieldSet->addField(Trans::getWord('lotNumber'), $lotField);
        $fieldSet->addField(Trans::getWord('packingNumber'), $this->Field->getText('jid_packing_number', $this->getParameterForModal('jid_packing_number', $showModal)));
        $fieldSet->addField(Trans::getWord('sku'), $skuField);
        $fieldSet->addField(Trans::getWord('goodsId'), $jogNumberField);
        $fieldSet->addHiddenField($this->Field->getHidden('jid_id', $this->getParameterForModal('jid_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_gd_id', $this->getParameterForModal('jid_gd_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_gdu_id', $this->getParameterForModal('jid_gdu_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_gd_sn', $this->getParameterForModal('jid_gd_sn', $showModal)));

        return $fieldSet;
    }

    /**
     * Function to get storage modal.
     *
     * @return Modal
     */
    protected function getStorageModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JbJidMdl', Trans::getWord('putAway'));
        $modal->setFormSubmit($this->getMainFormId(), 'doInsertPutAway');
        $showModal = false;
        if ($this->getFormAction() === 'doInsertPutAway' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $modal->addFieldSet($this->getPutAwayFieldSet($modal->getModalId(), $showModal));

        return $modal;
    }

    /**
     * Function to get storage delete modal.
     *
     * @return Modal
     */
    protected function getPutAwayDeleteModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JbJidDelMdl', Trans::getWord('deletePutAway'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeletePutAway');
        $showModal = false;
        if ($this->getFormAction() === 'doDeletePutAway' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Add field into field set.
        $fieldSet->addField(Trans::getWord('sku'), $this->Field->getText('jid_gd_sku_del', $this->getParameterForModal('jid_gd_sku_del', $showModal)));
        $fieldSet->addField(Trans::getWord('goods'), $this->Field->getText('jid_goods_del', $this->getParameterForModal('jid_goods_del', $showModal)));
        $fieldSet->addField(Trans::getWord('storage'), $this->Field->getText('jid_whs_name_del', $this->getParameterForModal('jid_whs_name_del', $showModal)));
        $fieldSet->addField(Trans::getWord('lotNumber'), $this->Field->getText('jid_lot_number_del', $this->getParameterForModal('jid_lot_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('serialNumber'), $this->Field->getText('jid_serial_number_del', $this->getParameterForModal('jid_serial_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('packingNumber'), $this->Field->getText('jid_packing_number_del', $this->getParameterForModal('jid_packing_number_del', $showModal)));
        $fieldSet->addField(Trans::getWord('quantity'), $this->Field->getText('jid_quantity_del', $this->getParameterForModal('jid_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getWord('uom'), $this->Field->getText('jid_uom_del', $this->getParameterForModal('jid_uom_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jid_id_del', $this->getParameterForModal('jid_id_del', $showModal)));
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }


    /**
     * Function to get the material from job goods field Set.
     *
     * @param int $joId        To store the id of job order.
     * @param int $jogIgnoreId To store the ignore id of job goods.
     *
     * @return array
     */
    private function loadJobGoodsMaterialData($joId, $jogIgnoreId): array
    {
        $results = [];
        $wheres = [];
        $wheres[] = '(jog_jo_id = ' . $joId . ')';
        $wheres[] = '(jog_id <> ' . $jogIgnoreId . ')';
        $wheres[] = '(jog_deleted_on IS NULL)';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jog_id, jog_quantity
                FROM job_goods ';
        $query .= $strWheres;
        $sqlResults = DB::select($query);
        if (empty($sqlResults) === false) {
            $results = DataParser::arrayObjectToArray($sqlResults);
        }

        return $results;
    }

}
