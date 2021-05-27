<?php

/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 Matalogix
 */

namespace App\Model\Api\Alfa;

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractBaseApi;
use App\Model\Dao\Job\JobActionDao;
use App\Model\Dao\Job\JobActionEventDao;
use App\Model\Dao\Job\JobOfficerDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\Document\DocumentTypeDao;
use App\Model\Dao\System\Service\ServiceTermDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle Stock Card.
 *
 * @package    app
 * @subpackage Model\Api
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 Matalogix
 */
class JobOrder extends AbstractBaseApi
{

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    protected function loadValidationRole(): void
    {
        if ($this->ActionName === 'loadJobOverviewByTime') {
            $this->Validation->checkRequire('srt_id');
            $this->Validation->checkInt('srt_id');
            if ($this->isValidParameter('index_time') === true) {
                $this->Validation->checkInt('index_time');
            }
        } elseif ($this->ActionName === 'loadListJobOverview') {
            $this->Validation->checkRequire('srt_id');
            $this->Validation->checkInt('srt_id');
            $this->Validation->checkRequire('status_type');
        } elseif ($this->ActionName === 'loadJobWorkSheet') {
            $this->Validation->checkRequire('jo_id');
            $this->Validation->checkInt('jo_id');
        } elseif ($this->ActionName === 'insertJobEvent') {
            $this->Validation->checkRequire('jo_id');
            $this->Validation->checkRequire('jac_id');
            $this->Validation->checkInt('jac_id');
            $this->Validation->checkRequire('event');
            $this->Validation->checkMaxLength('remark', 255);
            $this->Validation->checkRequire('date');
            if ($this->isValidParameter('date')) {
                $this->Validation->checkDate('date', '', '', 'Y-m-d');
            }
            $this->Validation->checkRequire('time');
            if ($this->isValidParameter('time')) {
                $this->Validation->checkTime('time', 'H:i');
            }
        } elseif ($this->ActionName === 'doUploadEventImage') {
            $this->Validation->checkRequire('jae_id');
            $this->Validation->checkRequire('jo_id');
            $this->Validation->checkRequire('jae_description');
            $this->Validation->checkRequire('jae_image');
        } elseif ($this->ActionName === 'loadMyJobs') {
            $this->Validation->checkRequire('srt_id');
            $this->Validation->checkRequire('srt_status');
        } elseif ($this->ActionName === 'loadJobGoods') {
            $this->Validation->checkRequire('jog_jo_id');
        }
    }

    /**
     * Abstract function to update data in database.
     *
     * @return void
     */
    protected function doControl(): void
    {

        if ($this->ActionName === 'loadJobOverview') {
            $this->loadJobOverviewData();
        } elseif ($this->ActionName === 'loadJobOverviewByTime') {
            $this->loadJobOverviewDataByTime();
        } elseif ($this->ActionName === 'loadListJobOverview') {
            $data = $this->loadListJobOverview();
            $this->addResultData('jobs', $data);
        } elseif ($this->ActionName === 'loadMyJobs') {
            $data = $this->loadMyJobData();
            $this->addResultData('jobs', $data);
        } elseif ($this->ActionName === 'loadJobWorkSheet') {
            $data = $this->loadJobWorkSheet();
            $this->addResultData('worksheets', $data);
        } elseif ($this->ActionName === 'insertJobEvent') {
            $data = $this->doInsertJobEvent();
            $this->addResultData('jobEvent', $data);
        } elseif ($this->ActionName === 'doUploadEventImage') {
            $data = $this->doUploadEventImage();
            $this->addResultData('image_path', $data);
        } elseif ($this->ActionName === 'planningJobOverview') {
            $data = $this->loadPlanningJobOverview();
            $this->addResultData('data', $data);
        } elseif ($this->ActionName === 'progressJobOverview') {
            $data = $this->loadProgressJobOverview();
            $this->addResultData('data', $data);
        } elseif ($this->ActionName === 'loadJobGoods') {
            $data = $this->loadJobGoodsData();
            $this->addResultData('jobGoods', $data);
        }
    }

    /**
     * Function to load stock card data
     *
     * @return array
     */
    private function loadJobGoodsData(): array
    {
        $wheres = [];
        $wheres[] = '(jog.jog_jo_id = ' . $this->getIntParameter('jog_jo_id') . ')';
        $wheres[] = '(jog.jog_deleted_on IS NULL)';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jog.jog_id, jog.jog_serial_number, jog.jog_name, jog.jog_production_number,
                        jog.jog_gd_id, gd.gd_sku as jog_gd_sku, gd.gd_barcode as jog_gd_barcode, gd.gd_name as jog_gd_name, gdc.gdc_name as jog_gd_category,
                        br.br_name as jog_gd_brand, jog.jog_quantity, jog.jog_gdu_id, uom.uom_code as jog_uom,
                        gdu.gdu_weight, gdu.gdu_volume,
                        gd.gd_sn as jog_gd_sn, gd.gd_tonnage as jog_gd_tonnage, gd.gd_cbm as jog_gd_cbm, gd.gd_multi_sn as jog_gd_multi_sn,
                        gd.gd_receive_sn as jog_gd_receive_sn, gd.gd_generate_sn as jog_gd_generate_sn,
                        gd.gd_packing as jog_gd_packing, gd.gd_expired as jog_gd_expired, gd.gd_min_tonnage as jog_gd_min_tonnage,
                        gd.gd_max_tonnage as jog_gd_max_tonnage,
                        gd.gd_min_cbm as jog_gd_min_cbm, gd.gd_max_cbm as jog_gd_max_cbm, gd.gd_tonnage_dm as jog_gd_tonnage_dm,
                         gd.gd_cbm_dm as jog_gd_cbm_dm
                    FROM job_goods as jog
                    INNER JOIN goods as gd ON jog.jog_gd_id = gd.gd_id
                    INNER JOIN goods_category as gdc ON gdc.gdc_id = gd.gd_gdc_id
                    INNER JOIN brand as br ON br.br_id = gd.gd_br_id
                    INNER JOIN goods_unit as gdu ON jog.jog_gdu_id = gdu.gdu_id
                    INNER JOIN unit as uom ON uom.uom_id = gdu.gdu_uom_id ' . $strWheres;
        $query .= ' ORDER BY gd.gd_sku, jog.jog_id';
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        $results = [];
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $qty = (float)$row['jog_quantity'];
            $volume = (float)$row['gdu_volume'];
            $weight = (float)$row['gdu_weight'];
            $row['jog_quantity_str'] = $number->doFormatFloat($qty);
            $row['jog_volume'] = $number->doFormatFloat($qty * $volume);
            $row['jog_weight'] = $number->doFormatFloat($qty * $weight);
            $results[] = $row;
        }

        return $results;
    }


    /**
     * Function to load stock card data
     *
     * @return array
     */
    private function loadProgressJobOverview(): array
    {
        $wheres = [];
        if ($this->Access->allowSeeAllJob() === false) {
            $wheres[] = '((jo.jo_manager_id = ' . $this->User->getId() . ')
                            OR (jo.jo_id IN (SELECT joo_jo_id
                                                FROM job_officer
                                                WHERE (joo_us_id = ' . $this->User->getId() . ')
                                                AND (joo_deleted_on IS NULL)
                                                GROUP BY joo_jo_id)))';
        }
        if ($this->Access->allowSeeJobAsRelation() === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->User->getRelId() . ')';
        }
        if ($this->Access->allowSeeAllOfficeJob() === false) {
            $wheres[] = '(jo.jo_order_of_id = ' . $this->User->Relation->getOfficeId() . ')';
        }
        $wheres[] = '(jo.jo_start_on IS NOT NULL)';
        $wheres[] = '(jo.jo_srv_id = 1)';
        $wheres[] = '(jo.jo_finish_on IS NULL)';
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT srt.srt_id, srt.srt_name, srv.srv_id, srv.srv_name, srt.srt_image, COUNT(jo.jo_id) as total, srt.srt_order
                    FROM job_order as jo
                    INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                    INNER JOIN service as srv ON srt.srt_srv_id = srv.srv_id ' . $strWheres;
        $query .= ' GROUP BY srt.srt_id, srt.srt_name, srv.srv_id, srv.srv_name, srt.srt_order';
        $query .= ' ORDER BY srv.srv_id, srt.srt_order, srt.srt_id';
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        $results = [];
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $total = (float)$row['total'];
            if ($total > 0) {
                $row['total'] = $number->doFormatFloat($total);
                if (empty($row['srt_image']) === false) {
                    $row['srt_image'] = asset('images/menus/' . $row['srt_image']);
                }
                $results[] = $row;
            }
        }

        return $results;
    }

    /**
     * Function to load stock card data
     *
     * @return array
     */
    private function loadPlanningJobOverview(): array
    {
        $wheres = [];
        if ($this->Access->allowSeeAllJob() === false) {
            $wheres[] = '((jo.jo_manager_id = ' . $this->User->getId() . ')
                            OR (jo.jo_id IN (SELECT joo_jo_id
                                                FROM job_officer
                                                WHERE (joo_us_id = ' . $this->User->getId() . ')
                                                AND (joo_deleted_on IS NULL)
                                                GROUP BY joo_jo_id)))';
        }
        if ($this->Access->allowSeeJobAsRelation() === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->User->getRelId() . ')';
        }
        if ($this->Access->allowSeeAllOfficeJob() === false) {
            $wheres[] = '(jo.jo_order_of_id = ' . $this->User->Relation->getOfficeId() . ')';
        }
        $wheres[] = '(jo.jo_srv_id = 1)';
        $wheres[] = '(jo.jo_publish_on IS NOT NULL)';
        $wheres[] = '(jo.jo_start_on IS NULL)';
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT srt.srt_id, srt.srt_name, srv.srv_id, srv.srv_name, srt.srt_image, COUNT(jo.jo_id) as total, srt.srt_order
                    FROM job_order as jo
                    INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                    INNER JOIN service as srv ON srt.srt_srv_id = srv.srv_id ' . $strWheres;
        $query .= ' GROUP BY srt.srt_id, srt.srt_name, srv.srv_id, srv.srv_name, srt.srt_order';
        $query .= ' ORDER BY srv.srv_id, srt.srt_order, srt.srt_id';
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        $results = [];
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $total = (float)$row['total'];
            if ($total > 0) {
                $row['total'] = $number->doFormatFloat($total);
                if (empty($row['srt_image']) === false) {
                    $row['srt_image'] = asset('images/menus/' . $row['srt_image']);
                }
                $results[] = $row;
            }
        }

        return $results;
    }


    /**
     * Function to load list event for action
     *
     * @return string
     */
    private function doUploadEventImage(): string
    {
        DB::beginTransaction();
        try {
            $dct = DocumentTypeDao::getByCode('joborder', 'actionevent');
            $fileName = time() . '.jpeg';
            $colVal = [
                'doc_ss_id' => $this->User->getSsId(),
                'doc_dct_id' => $dct['dct_id'],
                'doc_group_reference' => $this->getIntParameter('jo_id'),
                'doc_type_reference' => $this->getIntParameter('jae_id'),
                'doc_file_name' => $fileName,
                'doc_description' => $this->getStringParameter('jae_description'),
                'doc_file_size' => 100,
                'doc_file_type' => 'jpeg',
                'doc_public' => 'Y',
            ];
            $docDao = new DocumentDao();
            $docDao->doApiInsertTransaction($colVal, $this->User->getId());
            $jaeDao = new JobActionEventDao();
            $jaeDao->doApiUpdateTransaction($this->getIntParameter('jae_id'), [
                'jae_doc_id' => $docDao->getLastInsertId(),
            ], $this->User->getId());
            $upload = new FileUpload($docDao->getLastInsertId());
            $path = $upload->uploadBinaryFile($this->getStringParameter('jae_image'));
            DB::commit();
            $result = asset('storage/' . $path . '/' . $fileName);
        } catch (\Exception $e) {
            DB::rollBack();
            $result = '';
            $this->setErrorCode('500');
        }

        return $result;
    }

    /**
     * Function to load list event for action
     *
     * @return array
     */
    private function doInsertJobEvent(): array
    {
        DB::beginTransaction();
        try {
            $jaeColVal = [
                'jae_jac_id' => $this->getIntParameter('jac_id'),
                'jae_description' => $this->getStringParameter('event'),
                'jae_remark' => $this->getStringParameter('remark'),
                'jae_date' => $this->getStringParameter('date'),
                'jae_time' => $this->getStringParameter('time'),
                'jae_active' => 'Y',
            ];
            $jaeDao = new JobActionEventDao();
            $jaeDao->doApiInsertTransaction($jaeColVal, $this->User->getId());
            $joDao = new JobOrderDao();
            $joDao->doApiUpdateTransaction($this->getIntParameter('jo_id'), [
                'jo_jae_id' => $jaeDao->getLastInsertId(),
            ], $this->User->getId());
            DB::commit();
            $jaeColVal['jae_id'] = $jaeDao->getLastInsertId();
            $time = $this->getStringParameter('date') . ' ' . $this->getStringParameter('time');
            $jaeColVal['jae_time_on'] = DateTimeParser::format($time, 'Y-m-d H:i', 'H:i d M Y');
            $jaeColVal['jae_added_on'] = date('H:i d M Y');
            $jaeColVal['jae_added_by'] = $this->User->getName();

            $results = DataParser::doFormatApiData($jaeColVal);
        } catch (\Exception $e) {
            DB::rollBack();
            $results = [];
            $this->setErrorCode('500');
        }

        return $results;
    }

    /**
     * Function to load list event for action
     *
     * @return array
     */
    private function loadJobWorkSheet(): array
    {
        $joId = $this->getIntParameter('jo_id');
        $result = [];
        $jobOrder = JobOrderDao::loadSimpleJobOrderById($joId);
        if (empty($jobOrder) === false) {
            $events = JobActionEventDao::loadEventByJobId($joId);
            if (empty($jobOrder['jo_finish_on']) === false) {
                $result[] = [
                    'jae_id' => '',
                    'jae_jac_id' => '',
                    'jae_action' => Trans::getWord('finish'),
                    'jae_description' => '',
                    'jae_remark' => '',
                    'jae_time_on' => DateTimeParser::format($jobOrder['jo_finish_on'], 'Y-m-d H:i:s', 'H:i - d M Y'),
                    'jae_added_by' => $jobOrder['jo_finisher'],
                    'jae_added_on' => DateTimeParser::format($jobOrder['jo_finish_on'], 'Y-m-d H:i:s', 'H:i - d M Y'),
                    'jae_image' => '',
                ];
            }
            $docDao = new DocumentDao();
            foreach ($events as $row) {
                $image = '';
                if (empty($row['doc_id']) === false) {
                    $image = $docDao->getDocumentPath($row);
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
                    'jae_id' => $row['jae_id'],
                    'jae_jac_id' => $row['jae_jac_id'],
                    'jae_action' => Trans::getWord($row['jae_action'] . $jobOrder['jo_srt_id'] . '.description', 'action'),
                    'jae_description' => $row['jae_description'],
                    'jae_remark' => $row['remark'],
                    'jae_time_on' => $time,
                    'jae_added_by' => $row['jae_created_by'],
                    'jae_added_on' => DateTimeParser::format($row['jae_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y'),
                    'jae_image' => $image,
                ];
            }
            if (empty($jobOrder['jo_publish_on']) === false) {

                $result[] = [
                    'jae_id' => '',
                    'jae_jac_id' => '',
                    'jae_action' => Trans::getWord('published'),
                    'jae_description' => '',
                    'jae_remark' => '',
                    'jae_time_on' => DateTimeParser::format($jobOrder['jo_publish_on'], 'Y-m-d H:i:s', 'H:i - d M Y'),
                    'jae_added_by' => $jobOrder['jo_publisher'],
                    'jae_added_on' => DateTimeParser::format($jobOrder['jo_publish_on'], 'Y-m-d H:i:s', 'H:i - d M Y'),
                    'jae_image' => '',
                ];
            }
            $result[] = [
                'jae_id' => '',
                'jae_jac_id' => '',
                'jae_action' => Trans::getWord('draft'),
                'jae_description' => '',
                'jae_remark' => '',
                'jae_time_on' => DateTimeParser::format($jobOrder['jo_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y'),
                'jae_added_by' => $jobOrder['jo_creator'],
                'jae_added_on' => DateTimeParser::format($jobOrder['jo_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y'),
                'jae_image' => '',

            ];
        }


        return $result;
    }

    /**
     * Function to load stock card data
     *
     * @return array
     */
    private function loadMyJobData(): array
    {
        $wheres = [];
        if ($this->Access->allowSeeAllJob() === false) {
            $wheres[] = '((jo.jo_manager_id = ' . $this->User->getId() . ')
                            OR (jo.jo_id IN (SELECT joo_jo_id
                                                FROM job_officer
                                                WHERE (joo_us_id = ' . $this->User->getId() . ')
                                                AND (joo_deleted_on IS NULL)
                                                GROUP BY joo_jo_id)))';
        }
        if ($this->Access->allowSeeJobAsRelation() === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->User->getRelId() . ')';
        }
        if ($this->Access->allowSeeAllOfficeJob() === false) {
            $wheres[] = '(jo.jo_order_of_id = ' . $this->User->Relation->getOfficeId() . ')';
        }
        if (mb_strtolower($this->getStringParameter('srt_status', 'planning')) === 'planning') {
            $wheres[] = '(jo.jo_publish_on IS NOT NULL)';
            $wheres[] = '(jo.jo_start_on IS NULL)';
        } else {
            $wheres[] = '(jo.jo_start_on IS NOT NULL)';
        }
        $wheres[] = '(jo.jo_deleted_on IS NULL)';
        $wheres[] = '(jo.jo_srt_id = ' . $this->getIntParameter('srt_id') . ')';
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo.jo_finish_on IS NULL)';
        $results = [];
        $srt = ServiceTermDao::getByReference($this->getIntParameter('srt_id'));
        if (empty($srt) === false) {
            if ($srt['srt_route'] === 'joWhInbound') {
                # Inbound
                $results = $this->loadJobInboundData($wheres, $this->getIntParameter('limit', 0), $this->getIntParameter('offset', 0));
            } elseif ($srt['srt_route'] === 'joWhOutbound') {
                # Outbound
                $results = $this->loadJobOutboundData($wheres, $this->getIntParameter('limit', 0), $this->getIntParameter('offset', 0));
            } elseif ($srt['srt_route'] === 'joWhOpname') {
                # Opname
                $results = $this->loadJobOpnameData($wheres, $this->getIntParameter('limit', 0), $this->getIntParameter('offset', 0));
            } elseif ($srt['srt_route'] === 'joWhStockMovement') {
                # Movement
                $results = $this->loadJobMovementData($wheres, $this->getIntParameter('limit', 0), $this->getIntParameter('offset', 0));
            }
        }
        return $results;
        // return $this->loadJobOrderData($wheres, $this->getIntParameter('limit', 0), $this->getIntParameter('offset', 0));
    }


    /**
     * Function to load stock card data
     *
     * @return array
     */
    private function loadListJobOverview(): array
    {
        $wheres = [];
        if ($this->Access->allowSeeAllJob() === false) {
            $wheres[] = '((jo.jo_manager_id = ' . $this->User->getId() . ')
                            OR (jo.jo_id IN (SELECT joo_jo_id
                                                FROM job_officer
                                                WHERE (joo_us_id = ' . $this->User->getId() . ')
                                                AND (joo_deleted_on IS NULL)
                                                GROUP BY joo_jo_id)))';
        }
        if ($this->Access->allowSeeJobAsRelation() === true) {
            $wheres[] = '(jo.jo_rel_id = ' . $this->User->getRelId() . ')';
        }
        if ($this->Access->allowSeeAllOfficeJob() === false) {
            $wheres[] = '(jo.jo_order_of_id = ' . $this->User->Relation->getOfficeId() . ')';
        }
        $status = $this->getStringParameter('status_type');
        if ($status === 'D') {
            $wheres[] = '(jo.jo_publish_on IS NULL)';
        } elseif ($status === 'P') {
            $wheres[] = '(jo.jo_publish_on IS NOT NULL)';
            $wheres[] = '(jo.jo_start_on IS NULL)';
        } elseif ($status === 'A') {
            $wheres[] = '(jo.jo_start_on IS NOT NULL)';
            $wheres[] = '(jo.jo_finish_on IS NULL)';
        } else {
            $wheres[] = '(jo.jo_finish_on IS NOT NULL)';
            if ($this->isValidParameter('index_time') === true) {
                $indexTime = $this->getIntParameter('index_time');
                if ($indexTime === 0) {
                    $wheres[] = "(jo.jo_finish_on >= '" . date('Y-m-d') . ' 00:01:00' . "')";
                    $wheres[] = "(jo.jo_finish_on <= '" . date('Y-m-d') . ' 23:59:00' . "')";
                } elseif ($indexTime === 1) {
                    $month = DateTimeParser::createDateTime();
                    $wheres[] = "(jo.jo_finish_on >= '" . $month->format('Y-m') . '-01 00:01:00' . "')";
                    $wheres[] = "(jo.jo_finish_on <= '" . $month->format('Y-m-t') . ' 23:59:00' . "')";
                } elseif ($indexTime === 2) {
                    $month = DateTimeParser::createDateTime();
                    $month->modify('-1 month');
                    $wheres[] = "(jo.jo_finish_on >= '" . $month->format('Y-m') . '-01 00:01:00' . "')";
                    $wheres[] = "(jo.jo_finish_on <= '" . $month->format('Y-m-t') . ' 23:59:00' . "')";
                } elseif ($indexTime === 3) {
                    $wheres[] = "(jo.jo_finish_on >= '" . date('Y') . '-01-01 00:01:00' . "')";
                    $wheres[] = "(jo.jo_finish_on <= '" . date('Y') . '-12-31 23:59:00' . "')";
                }
            }
        }
        $wheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo_srt_id = ' . $this->getIntParameter('srt_id') . ')';
        $wheres[] = '(jo_deleted_on IS NULL)';
        $results = [];
        $srt = ServiceTermDao::getByReference($this->getIntParameter('srt_id'));
        if (empty($srt) === false) {
            if ($srt['srt_route'] === 'joWhInbound') {
                # Inbound
                $results = $this->loadJobInboundData($wheres, $this->getIntParameter('limit', 0), $this->getIntParameter('offset', 0));
            } elseif ($srt['srt_route'] === 'joWhOutbound') {
                # Outbound
                $results = $this->loadJobOutboundData($wheres, $this->getIntParameter('limit', 0), $this->getIntParameter('offset', 0));
            } elseif ($srt['srt_route'] === 'joWhOpname') {
                # Opname
                $results = $this->loadJobOpnameData($wheres, $this->getIntParameter('limit', 0), $this->getIntParameter('offset', 0));
            } elseif ($srt['srt_route'] === 'joWhStockMovement') {
                # Movement
                $results = $this->loadJobMovementData($wheres, $this->getIntParameter('limit', 0), $this->getIntParameter('offset', 0));
            }
        }
        return $results;
    }

    /**
     * Function to load stock card data
     *
     * @param array $wheres To store the where conditions.
     * @param int $limit To store the where conditions.
     * @param int $offset To store the where conditions.
     *
     * @return array
     */
    private function loadJobInboundData(array $wheres = [], $limit = 0, $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jo.jo_id, jo.jo_number, (CASE WHEN jo.jo_manager_id IS NULL THEN -1 ELSE jo.jo_manager_id END) as jo_manager_id,
                        jo.jo_srv_id, srv.srv_name, jo.jo_srt_id, srt.srt_name,
                        jo.jo_rel_id, rel.rel_name, jo.jo_order_of_id,
                          jo.jo_publish_on, jo.jo_start_on, jac.jac_id, ac.ac_code as jac_action, jae.jae_description, ac.ac_style,
                          jo.jo_deleted_on, jo.jo_finish_on, jo.jo_order_date,
                          jae.jae_created_on, jo.jo_created_on, jo.jo_document_on, ji.ji_so_id as jo_so_id, so.so_id, so.so_number,
                        (CASE WHEN so.so_customer_ref IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                            (CASE WHEN so.so_aju_ref IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref,
                            (CASE WHEN so.so_bl_ref IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                            (CASE WHEN so.so_packing_ref IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                            (CASE WHEN so.so_sppb_ref IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                            (CASE WHEN jae.jae_created_on IS NULL THEN jo.jo_publish_on ELSE jae.jae_created_on END) as update_on,
                            pic.cp_name as pic_customer, rel.rel_short_name, ji.ji_eta_date as eta_date,
                            ji.ji_eta_time as eta_time, ji.ji_ata_date as ata_date, ji.ji_ata_time as ata_time,
                            shp.rel_name as jo_shipper, wh.wh_name as jo_warehouse, \'\' as jo_storage,
                            joh.joh_id, joh.joh_jo_id, joh.joh_reason, joh.joh_created_on, srt.srt_route as jo_route, so.so_start_on,
                            jtr.jtr_id as jo_jtr_id, \'\' as jo_jb_id, pics.cp_name as jo_pic_shipper
                FROM job_order as jo
                    INNER JOIN job_inbound as ji ON ji.ji_jo_id = jo.jo_id
                    INNER JOIN relation as shp ON shp.rel_id = ji.ji_rel_id
                    INNER JOIN warehouse as wh ON wh.wh_id = ji.ji_wh_id
                    INNER JOIN service as srv on jo.jo_srv_id = srv.srv_id
                    INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                    INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                    LEFT OUTER JOIN contact_person as pic ON jo.jo_pic_id = pic.cp_id
                    LEFT OUTER JOIN contact_person as pics ON ji.ji_cp_id = pics.cp_id
                    LEFT OUTER JOIN sales_order as so ON ji.ji_so_id = so.so_id
                    LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                    LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                    LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id
                    LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id
                    LEFT OUTER JOIN job_stock_transfer as jtr ON jo.jo_id = jtr.jtr_ji_jo_id' . $strWhere;
        if (mb_strtolower($this->getStringParameter('srt_status', 'finish')) === 'planning') {
            $query .= ' ORDER BY rel.rel_name, jo.jo_publish_on, jo.jo_id';
        } else {
            $query .= ' ORDER BY rel.rel_name, update_on DESC, jo.jo_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        return $this->doPrepareJobData($data);
    }


    /**
     * Function to load stock card data
     *
     * @param array $wheres To store the where conditions.
     * @param int $limit To store the where conditions.
     * @param int $offset To store the where conditions.
     *
     * @return array
     */
    private function loadJobOutboundData(array $wheres = [], $limit = 0, $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT jo.jo_id, jo.jo_number, (CASE WHEN jo.jo_manager_id IS NULL THEN -1 ELSE jo.jo_manager_id END) as jo_manager_id,
                        jo.jo_srv_id, srv.srv_name, jo.jo_srt_id, srt.srt_name,
                        jo.jo_rel_id, rel.rel_name, jo.jo_order_of_id,
                          jo.jo_publish_on, jo.jo_start_on, jac.jac_id, ac.ac_code as jac_action, jae.jae_description, ac.ac_style,
                          jo.jo_deleted_on, jo.jo_finish_on, jo.jo_order_date,
                          jae.jae_created_on, jo.jo_created_on, jo.jo_document_on, job.job_so_id as jo_so_id, so.so_id, so.so_number,
                        (CASE WHEN so.so_customer_ref IS NULL THEN jo.jo_customer_ref ELSE so.so_customer_ref END) as customer_ref,
                            (CASE WHEN so.so_aju_ref IS NULL THEN jo.jo_aju_ref ELSE so.so_aju_ref END) as aju_ref,
                            (CASE WHEN so.so_bl_ref IS NULL THEN jo.jo_bl_ref ELSE so.so_bl_ref END) as bl_ref,
                            (CASE WHEN so.so_packing_ref IS NULL THEN jo.jo_packing_ref ELSE so.so_packing_ref END) as packing_ref,
                            (CASE WHEN so.so_sppb_ref IS NULL THEN jo.jo_sppb_ref ELSE so.so_sppb_ref END) as sppb_ref,
                            (CASE WHEN jae.jae_created_on IS NULL THEN jo.jo_publish_on ELSE jae.jae_created_on END) as update_on,
                            pic.cp_name as pic_customer, rel.rel_short_name, job.job_eta_date as eta_date,
                            job.job_eta_time as eta_time, job.job_ata_date as ata_date, job.job_ata_time as ata_time,
                            cons.rel_name as jo_shipper, wh.wh_name as jo_warehouse, \'\' as jo_storage,
                            joh.joh_id, joh.joh_jo_id, joh.joh_reason, joh.joh_created_on, srt.srt_route as jo_route, so.so_start_on,
                            jtr.jtr_id as jo_jtr_id, \'\' as jo_jb_id, pics.cp_name as jo_pic_shipper
                FROM job_order as jo
                    INNER JOIN job_outbound as job ON job.job_jo_id = jo.jo_id
                    INNER JOIN relation as cons ON cons.rel_id = job.job_rel_id
                    INNER JOIN warehouse as wh ON wh.wh_id = job.job_wh_id
                    INNER JOIN service as srv on jo.jo_srv_id = srv.srv_id
                    INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                    INNER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                    LEFT OUTER JOIN contact_person as pic ON jo.jo_pic_id = pic.cp_id
                    LEFT OUTER JOIN contact_person as pics ON job.job_cp_id = pics.cp_id
                    LEFT OUTER JOIN sales_order as so ON job.job_so_id = so.so_id
                    LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                    LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                    LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id
                    LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id
                    LEFT OUTER JOIN job_stock_transfer as jtr ON jo.jo_id = jtr.jtr_job_jo_id ' . $strWhere;
        if (mb_strtolower($this->getStringParameter('srt_status', 'finish')) === 'planning') {
            $query .= ' ORDER BY rel.rel_name, jo.jo_publish_on, jo.jo_id';
        } else {
            $query .= ' ORDER BY rel.rel_name, update_on DESC, jo.jo_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        return $this->doPrepareJobData($data);
    }


    /**
     * Function to load stock card data
     *
     * @param array $wheres To store the where conditions.
     * @param int $limit To store the where conditions.
     * @param int $offset To store the where conditions.
     *
     * @return array
     */
    private function loadJobOpnameData(array $wheres = [], $limit = 0, $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = "SELECT jo.jo_id, jo.jo_number, (CASE WHEN jo.jo_manager_id IS NULL THEN -1 ELSE jo.jo_manager_id END) as jo_manager_id,
                        jo.jo_srv_id, srv.srv_name, jo.jo_srt_id, srt.srt_name,
                        jo.jo_rel_id, rel.rel_name, jo.jo_order_of_id,
                          jo.jo_publish_on, jo.jo_start_on, jac.jac_id, ac.ac_code as jac_action, jae.jae_description, ac.ac_style,
                          jo.jo_deleted_on, jo.jo_finish_on, jo.jo_order_date,
                          jae.jae_created_on, jo.jo_created_on, jo.jo_document_on, '' as jo_so_id, '' as so_id, '' as so_number,
                        jo.jo_customer_ref as customer_ref, jo.jo_aju_ref as aju_ref,
                            jo.jo_bl_ref as bl_ref, jo.jo_packing_ref as packing_ref, jo.jo_sppb_ref as sppb_ref,
                            (CASE WHEN jae.jae_created_on IS NULL THEN jo.jo_publish_on ELSE jae.jae_created_on END) as update_on,
                            pic.cp_name as pic_customer, rel.rel_short_name, sop.sop_date as eta_date,
                            sop.sop_time as eta_time, '' as ata_date, '' as ata_time,
                            '' as jo_shipper, wh.wh_name as jo_warehouse, '' as jo_storage,
                            joh.joh_id, joh.joh_jo_id, joh.joh_reason, joh.joh_created_on, srt.srt_route as jo_route, '' as so_start_on,
                            '' as jo_jtr_id, '' as jo_jb_id, '' as jo_pic_shipper
                FROM job_order as jo
                    INNER JOIN stock_opname as sop ON sop.sop_jo_id = jo.jo_id
                    INNER JOIN service as srv on jo.jo_srv_id = srv.srv_id
                    INNER JOIN warehouse as wh ON wh.wh_id = sop.sop_wh_id
                    INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                    LEFT OUTER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                    LEFT OUTER JOIN contact_person as pic ON jo.jo_pic_id = pic.cp_id
                    LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                    LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                    LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id
                    LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id  " . $strWhere;
        if (mb_strtolower($this->getStringParameter('srt_status', 'finish')) === 'planning') {
            $query .= ' ORDER BY rel.rel_name, jo.jo_publish_on, jo.jo_id';
        } else {
            $query .= ' ORDER BY rel.rel_name, update_on DESC, jo.jo_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        return $this->doPrepareJobData($data);
    }


    /**
     * Function to load stock card data
     *
     * @param array $wheres To store the where conditions.
     * @param int $limit To store the where conditions.
     * @param int $offset To store the where conditions.
     *
     * @return array
     */
    private function loadJobMovementData(array $wheres = [], $limit = 0, $offset = 0): array
    {
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = "SELECT jo.jo_id, jo.jo_number, (CASE WHEN jo.jo_manager_id IS NULL THEN -1 ELSE jo.jo_manager_id END) as jo_manager_id,
                        jo.jo_srv_id, srv.srv_name, jo.jo_srt_id, srt.srt_name,
                        jo.jo_rel_id, rel.rel_name, jo.jo_order_of_id,
                          jo.jo_publish_on, jo.jo_start_on, jac.jac_id, ac.ac_code as jac_action, jae.jae_description, ac.ac_style,
                          jo.jo_deleted_on, jo.jo_finish_on, jo.jo_order_date,
                          jae.jae_created_on, jo.jo_created_on, jo.jo_document_on, '' as jo_so_id, '' as so_id, '' as so_number,
                            jo.jo_customer_ref as customer_ref, jo.jo_aju_ref as aju_ref,
                            jo.jo_bl_ref as bl_ref, jo.jo_packing_ref as packing_ref, jo.jo_sppb_ref as sppb_ref,
                            (CASE WHEN jae.jae_created_on IS NULL THEN jo.jo_publish_on ELSE jae.jae_created_on END) as update_on,
                            pic.cp_name as pic_customer, rel.rel_short_name, jm.jm_date as eta_date,
                            jm.jm_time as eta_time, '' as ata_date, '' as ata_time,
                            '' as jo_shipper, wh.wh_name as jo_warehouse, whs.whs_name as jo_storage,
                            joh.joh_id, joh.joh_jo_id, joh.joh_reason, joh.joh_created_on, srt.srt_route as jo_route, '' as so_start_on,
                            '' as jo_jtr_id, '' as jo_jb_id, '' as jo_pic_shipper
                FROM job_order as jo
                    INNER JOIN job_movement as jm ON jm.jm_jo_id = jo.jo_id
                    INNER JOIN service as srv on jo.jo_srv_id = srv.srv_id
                    INNER JOIN warehouse as wh ON wh.wh_id = jm.jm_wh_id
                    INNER JOIN warehouse_storage as whs ON whs.whs_id = jm.jm_whs_id
                    INNER JOIN service_term as srt ON jo.jo_srt_id = srt.srt_id
                    LEFT OUTER JOIN relation as rel ON jo.jo_rel_id = rel.rel_id
                    LEFT OUTER JOIN contact_person as pic ON jo.jo_pic_id = pic.cp_id
                    LEFT OUTER JOIN job_action_event as jae ON jo.jo_jae_id = jae.jae_id
                    LEFT OUTER JOIN job_action as jac ON jae.jae_jac_id = jac.jac_id
                    LEFT OUTER JOIN action as ac ON jac.jac_ac_id = ac.ac_id
                    LEFT OUTER JOIN job_order_hold as joh ON jo.jo_joh_id = joh.joh_id  " . $strWhere;
        if (mb_strtolower($this->getStringParameter('srt_status', 'finish')) === 'planning') {
            $query .= ' ORDER BY rel.rel_name, jo.jo_publish_on, jo.jo_id';
        } else {
            $query .= ' ORDER BY rel.rel_name, update_on DESC, jo.jo_id';
        }
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        return $this->doPrepareJobData($data);
    }


    /**
     * Function to prepare job data
     *
     * @param array $data To store the data.
     *
     * @return array
     */
    private function doPrepareJobData(array $data): array
    {
        $results = [];
        if (empty($data) === false) {
            foreach ($data as $row) {
                $d = [];
                $d['jo_id'] = $row['jo_id'];
                $d['jo_so_id'] = $row['jo_so_id'];
                $d['jo_so_start_on'] = $row['so_start_on'];
                $d['jo_number'] = $row['jo_number'];
                $d['jo_manager_id'] = $row['jo_manager_id'];
                $d['jo_srv_id'] = $row['jo_srv_id'];
                $d['srv_name'] = $row['srv_name'];
                $d['srt_name'] = $row['srt_name'];
                $d['jo_srt_id'] = $row['jo_srt_id'];
                $d['jo_rel_id'] = $row['jo_rel_id'];
                $d['jo_order_of_id'] = $row['jo_order_of_id'];
                $d['rel_name'] = $row['rel_name'];
                $d['rel_short_name'] = $row['rel_short_name'];
                $d['jo_customer_ref'] = $row['customer_ref'];
                $d['jo_so_ref'] = $row['so_number'];
                $d['jo_bl_ref'] = $row['bl_ref'];
                $d['jo_aju_ref'] = $row['aju_ref'];
                $d['jo_packing_ref'] = $row['packing_ref'];
                $d['jo_sppb_ref'] = $row['sppb_ref'];
                $d['jo_pic_customer'] = $row['pic_customer'];
                $d['jo_shipper'] = $row['jo_shipper'];
                $d['jo_pic_shipper'] = $row['jo_pic_shipper'];
                $d['jo_warehouse'] = $row['jo_warehouse'];
                $d['jo_storage'] = $row['jo_storage'];
                if (empty($row['jo_deleted_on']) === false) {
                    $style = 'dark';
                    $status = Trans::getWord('canceled');
                    $updatedOn = DateTimeParser::format($row['jo_deleted_on'], 'Y-m-d H:i:s', 'H:i - d M Y');
                } elseif (empty($row['joh_created_on']) === false) {
                    $style = 'dark';
                    $status = Trans::getWord('hold');
                    $updatedOn = DateTimeParser::format($row['joh_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y');
                } elseif (empty($row['jo_finish_on']) === false) {
                    $style = 'success';
                    $status = Trans::getWord('finish');
                    $updatedOn = DateTimeParser::format($row['jo_finish_on'], 'Y-m-d H:i:s', 'H:i - d M Y');
                } elseif (empty($row['jo_start_on']) === false && empty($row['jac_id']) === false) {
                    $style = 'primary';
                    if (empty($row['ac_style']) === false) {
                        $style = $row['ac_style'];
                    }
                    $status = Trans::getWord($row['jac_action'] . '' . $row['jo_srt_id'] . '.description', 'action');
                    $updatedOn = DateTimeParser::format($row['jae_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y');
                } elseif (empty($row['jo_publish_on']) === false) {
                    $style = 'danger';
                    $status = Trans::getWord('published');
                    $updatedOn = DateTimeParser::format($row['jo_publish_on'], 'Y-m-d H:i:s', 'H:i - d M Y');
                } else {
                    $style = 'gray';
                    $status = Trans::getWord('draft');
                    $updatedOn = DateTimeParser::format($row['jo_created_on'], 'Y-m-d H:i:s', 'H:i - d M Y');
                }
                $d['ac_style'] = $style;
                $d['ac_status'] = $status;
                $d['last_update_on'] = $updatedOn;
                $eta = '';
                if (empty($row['eta_date']) === false) {
                    if (empty($row['eta_time']) === false) {
                        $eta = DateTimeParser::format($row['eta_date'] . ' ' . $row['eta_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
                    } else {
                        $eta = DateTimeParser::format($row['eta_date'], 'Y-m-d', 'd M Y');
                    }
                }
                $d['jo_eta'] = $eta;
                $ata = '';
                if (empty($row['ata_date']) === false) {
                    if (empty($row['ata_time']) === false) {
                        $ata = DateTimeParser::format($row['ata_date'] . ' ' . $row['ata_time'], 'Y-m-d H:i:s', 'H:i - d M Y');
                    } else {
                        $ata = DateTimeParser::format($row['ata_date'], 'Y-m-d', 'd M Y');
                    }
                }
                $d['jo_ata'] = $ata;
                $d['jo_created_on'] = '';
                $d['jo_publish_on'] = '';
                $d['jo_document_on'] = '';
                $d['jo_start_date'] = '';
                $d['jo_start_on'] = '';
                $d['jo_finish_on'] = '';
                if (empty($row['jo_created_on']) === false) {
                    $d['jo_created_on'] = DateTimeParser::format($row['jo_created_on'], 'Y-m-d H:i:s', 'H:i d M Y');
                }
                if (empty($row['jo_publish_on']) === false) {
                    $d['jo_publish_on'] = DateTimeParser::format($row['jo_publish_on'], 'Y-m-d H:i:s', 'H:i d M Y');
                }
                if (empty($row['jo_document_on']) === false) {
                    $d['jo_document_on'] = DateTimeParser::format($row['jo_document_on'], 'Y-m-d H:i:s', 'H:i d M Y');
                }
                if (empty($row['jo_start_on']) === false) {
                    $d['jo_start_on'] = DateTimeParser::format($row['jo_start_on'], 'Y-m-d H:i:s', 'H:i d M Y');
                    $d['jo_start_date'] = DateTimeParser::format($row['jo_start_on'], 'Y-m-d H:i:s', 'Y-m-d');
                }
                if (empty($row['jo_finish_on']) === false) {
                    $d['jo_finish_on'] = DateTimeParser::format($row['jo_finish_on'], 'Y-m-d H:i:s', 'H:i d M Y');
                }
                $d['jo_transfer_id'] = $row['jo_jtr_id'];
                $d['jo_bundling_id'] = $row['jo_jb_id'];
                $results[] = DataParser::doFormatApiData($d);
            }
        }

        return $results;
    }

    /**
     * Function to load stock card data
     *
     * @return void
     */
    private function loadJobOverviewDataByTime(): void
    {
        $wheres = [];
        if ($this->Access->allowSeeAllJob() === false) {
            $wheres[] = '((jo_manager_id = ' . $this->User->getId() . ')
                            OR (jo_id IN (SELECT joo_jo_id
                                                FROM job_officer
                                                WHERE (joo_us_id = ' . $this->User->getId() . ')
                                                AND (joo_deleted_on IS NULL)
                                                GROUP BY joo_jo_id)))';
        }
        if ($this->Access->allowSeeJobAsRelation() === true) {
            $wheres[] = '(jo_rel_id = ' . $this->User->getRelId() . ')';
        }
        if ($this->Access->allowSeeAllOfficeJob() === false) {
            $wheres[] = '(jo_order_of_id = ' . $this->User->Relation->getOfficeId() . ')';
        }
        $wheres[] = '(jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo_srt_id = ' . $this->getIntParameter('srt_id') . ')';
        $wheres[] = '(jo_deleted_on IS NULL)';
        $wheres[] = '(jo_start_on IS NOT NULL)';
        # Set Select query;
        $indexTime = $this->getIntParameter('index_time', 0);
        if ($indexTime === 0) {
            $wheres[] = "(jo_finish_on >= '" . date('Y-m-d') . ' 00:01:00' . "')";
            $wheres[] = "(jo_finish_on <= '" . date('Y-m-d') . ' 23:59:00' . "')";
        } elseif ($indexTime === 1) {
            $month = DateTimeParser::createDateTime();
            $wheres[] = "(jo_finish_on >= '" . $month->format('Y-m') . '-01 00:01:00' . "')";
            $wheres[] = "(jo_finish_on <= '" . $month->format('Y-m-t') . ' 23:59:00' . "')";
        } elseif ($indexTime === 2) {
            $month = DateTimeParser::createDateTime();
            $month->modify('-1 month');
            $wheres[] = "(jo_finish_on >= '" . $month->format('Y-m') . '-01 00:01:00' . "')";
            $wheres[] = "(jo_finish_on <= '" . $month->format('Y-m-t') . ' 23:59:00' . "')";
        } elseif ($indexTime === 3) {
            $wheres[] = "(jo_finish_on >= '" . date('Y') . '-01-01 00:01:00' . "')";
            $wheres[] = "(jo_finish_on <= '" . date('Y') . '-12-31 23:59:00' . "')";
        } else {
            $wheres[] = '(jo_finish_on IS NOT NULL)';
        }
        $strFinishWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jo_srt_id, COUNT(jo_id) AS total
                FROM job_order ' . $strFinishWhere;
        $query .= ' GROUP BY jo_srt_id';
        $sqlResults = DB::select($query);
        $finish = 0;
        if (count($sqlResults) === 1) {
            $data = DataParser::objectToArrayAPI($sqlResults[0]);
            $finish = (float)$data['total'];
        }
        $number = new NumberFormatter();
        $this->addResultData('qty_finish', $number->doFormatFloat($finish));
    }


    /**
     * Function to load stock card data
     *
     * @return void
     */
    private function loadJobOverviewData(): void
    {
        $wheres = [];
        if ($this->Access->allowSeeAllJob() === false) {
            $wheres[] = '((jo_manager_id = ' . $this->User->getId() . ')
                            OR (jo_id IN (SELECT joo_jo_id
                                                FROM job_officer
                                                WHERE (joo_us_id = ' . $this->User->getId() . ')
                                                AND (joo_deleted_on IS NULL)
                                                GROUP BY joo_jo_id)))';
        }
        if ($this->Access->allowSeeJobAsRelation() === true) {
            $wheres[] = '(jo_rel_id = ' . $this->User->getRelId() . ')';
        }
        if ($this->Access->allowSeeAllOfficeJob() === false) {
            $wheres[] = '(jo_order_of_id = ' . $this->User->Relation->getOfficeId() . ')';
        }
        $wheres[] = '(jo_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(jo_srt_id = ' . $this->getIntParameter('srt_id') . ')';
        $wheres[] = '(jo_deleted_on IS NULL)';
        # Set Select query;
        $draftWheres = [];
        $draftWheres[] = '(jo_publish_on IS NULL)';
        $strDraftWhere = ' WHERE ' . implode(' AND ', array_merge($wheres, $draftWheres));

        $publishWheres = [];
        $publishWheres[] = '(jo_publish_on IS NOT NULL)';
        $publishWheres[] = '(jo_start_on IS NULL)';
        $strPublishWhere = ' WHERE ' . implode(' AND ', array_merge($wheres, $publishWheres));

        $activityWheres = [];
        $activityWheres[] = '(jo_start_on IS NOT NULL)';
        $activityWheres[] = '(jo_finish_on IS NULL)';
        $strActivityWhere = ' WHERE ' . implode(' AND ', array_merge($wheres, $activityWheres));

        $finishWheres = [];
        $finishWheres[] = '(jo_start_on IS NOT NULL)';

        $finishWheres[] = "(jo_finish_on >= '" . date('Y-m-d') . ' 00:01:00' . "')";
        $finishWheres[] = "(jo_finish_on <= '" . date('Y-m-d') . ' 23:59:00' . "')";
        $strFinishWhere = ' WHERE ' . implode(' AND ', array_merge($wheres, $finishWheres));

        $query = "SELECT 'D' AS type, COUNT(jo_id) AS total
                FROM job_order " . $strDraftWhere;
        $query .= ' UNION ALL ';
        $query .= "SELECT 'P' AS type, COUNT(jo_id) AS total
                FROM job_order " . $strPublishWhere;
        $query .= ' UNION ALL ';
        $query .= "SELECT 'A' AS type, COUNT(jo_id) AS total
                FROM job_order " . $strActivityWhere;
        $query .= ' UNION ALL ';
        $query .= "SELECT 'F' AS type, COUNT(jo_id) AS total
                FROM job_order " . $strFinishWhere;
        $sqlResults = DB::select($query);
        $data = DataParser::arrayObjectToArrayAPI($sqlResults);
        $this->doPrepareJobOverviewData($data);
    }

    /**
     * Function to do prepare stock card data.
     *
     * @param array $data To store the data.
     *
     * @return void
     */
    private function doPrepareJobOverviewData(array $data): void
    {
        $number = new NumberFormatter();
        $draft = 0;
        $publish = 0;
        $activity = 0;
        $finish = 0;
        foreach ($data as $row) {
            if ($row['type'] === 'D') {
                $draft = (float)$row['total'];
            } elseif ($row['type'] === 'P') {
                $publish = (float)$row['total'];
            } elseif ($row['type'] === 'A') {
                $activity = (float)$row['total'];
            } else {
                $finish = (float)$row['total'];
            }
        }
        $this->addResultData('qty_draft', $number->doFormatFloat($draft));
        $this->addResultData('qty_published', $number->doFormatFloat($publish));
        $this->addResultData('qty_activity', $number->doFormatFloat($activity));
        $this->addResultData('qty_finish', $number->doFormatFloat($finish));
    }

    /**
     * Function to load total number of draft project.
     *
     * @param array $jobData To store the job order reference.
     *
     * @return void
     */
    protected function doPrepareNextJobActionData(array $jobData): void
    {
        $result = [
            'next_jac_id' => '',
            'next_jac_action' => '',
            'next_jac_action_id' => '',
            'next_jac_style' => '',
            'next_jac_start_on' => '',
        ];
        if (empty($jobData['joh_id']) === true) {
            $data = JobActionDao::loadNextActionByJobId($jobData['jo_id']);
            if (empty($data) === false) {
                $result = [
                    'next_jac_id' => $data['jac_id'],
                    'next_jac_action' => $data['jac_action'],
                    'next_jac_action_id' => $data['jac_ac_id'],
                    'next_jac_style' => $data['jac_style'],
                    'next_jac_start_on' => $data['jac_start_on'],
                ];
            }
        }

        $this->addResultData('nextAction', DataParser::doFormatApiData($result));
    }


    /**
     * Function to load total number of draft project.
     *
     * @param array $jobData To store the job order reference.
     *
     * @return void
     */
    protected function doPrepareStatusJobData(array $jobData): void
    {
        $result = [];
        if (empty($jobData['jo_deleted_on']) === false) {
            $result['last_status'] = Trans::getWord('canceled');
            $result['last_event'] = '';
            $result['last_update'] = DateTimeParser::format($jobData['jo_deleted_on'], 'Y-m-d H:i:s', 'H:i d M Y');
            $result['last_id'] = '';
            $result['last_style'] = 'dark';
            $result['last_ac_id'] = '';
        } elseif (empty($jobData['joh_id']) === false) {
            $result['last_status'] = Trans::getWord('hold');
            $result['last_event'] = $jobData['joh_reason'];
            $result['last_update'] = DateTimeParser::format($jobData['joh_created_on'], 'Y-m-d H:i:s', 'H:i d M Y');
            $result['last_style'] = 'dark';
            $result['last_id'] = '';
            $result['last_ac_id'] = '';
        } elseif (empty($jobData['jo_finish_on']) === false) {
            $result['last_status'] = Trans::getWord('finish');
            $result['last_event'] = '';
            $result['last_update'] = DateTimeParser::format($jobData['jo_finish_on'], 'Y-m-d H:i:s', 'H:i d M Y');
            $result['last_style'] = 'success';
            $result['last_id'] = '';
            $result['last_ac_id'] = '';
        } elseif (empty($jobData['jo_start_on']) === false) {
            $current = JobActionDao::getLastActiveActionByJobIdManually($jobData['jo_id']);
            if (empty($current) === false) {
                $result['last_status'] = Trans::getWord($current['jac_action'] . '' . $current['ac_srt_id'] . '.description', 'action');
                $result['last_event'] = $current['jae_description'];
                $time = '';
                if (empty($current['jae_date']) === false) {
                    if (empty($current['jae_time']) === false) {
                        $time = DateTimeParser::format($current['jae_date'] . ' ' . $current['jae_time'], 'Y-m-d H:i:s', 'H:i d M Y');
                    } else {
                        $time = DateTimeParser::format($current['jae_date'], 'Y-m-d', 'd M Y');
                    }
                }
                $result['last_update'] = $time;
                $result['last_style'] = $current['jac_style'];
                $result['last_id'] = $current['jac_id'];
                $result['last_ac_id'] = $current['jac_ac_id'];
            } else {
                $result['last_status'] = '';
                $result['last_event'] = '';
                $result['last_update'] = '';
                $result['last_style'] = '';
                $result['last_id'] = '';
                $result['last_ac_id'] = '';
            }
        } elseif (empty($jobData['jo_publish_on']) === false) {
            $result['last_status'] = Trans::getWord('published');
            $result['last_event'] = '';
            $result['last_update'] = DateTimeParser::format($jobData['jo_publish_on'], 'Y-m-d H:i:s', 'H:i d M Y');
            $result['last_style'] = 'danger';
            $result['last_id'] = '';
            $result['last_ac_id'] = '';
        } else {
            $result['last_status'] = Trans::getWord('draft');
            $result['last_event'] = '';
            $result['last_style'] = 'gray';
            $result['last_update'] = '';
            $result['last_id'] = '';
            $result['last_ac_id'] = '';
        }

        $this->addResultData('lastStatus', $result);
    }

    /**
     * Function to load the list of event for action
     *
     * @return void
     */
    protected function loadJobActionValidation(): void
    {
        $this->Validation->checkRequire('jo_id');
        $this->Validation->checkInt('jo_id');
        $this->Validation->checkRequire('jwId');
        $this->Validation->checkInt('jwId');
        $this->Validation->checkRequire('jac_id');
        $this->Validation->checkInt('jac_id');
        $this->Validation->checkRequire('jo_srt_id');
        $this->Validation->checkRequire('action');
        $this->Validation->checkRequire('date');
        if ($this->isValidParameter('date')) {
            $this->Validation->checkDate('date', '', '', 'Y-m-d');
        }
        $this->Validation->checkRequire('time');
        if ($this->isValidParameter('time')) {
            $this->Validation->checkTime('time', 'H:i');
        }
    }

    /**
     * Function to get total sail out by office
     *
     * @param int $type TO set the type of update.
     *                  0. is for start and end data.
     *                  1. is only for start data information
     *                  2. is only for end data information.
     *
     * @return array
     */
    protected function doUpdateJobActionEvent($type = 0): array
    {
        if ($type === 1) {
            $jacColVal = [
                'jac_start_by' => $this->User->getId(),
                'jac_start_on' => date('Y-m-d H:i:s'),
                'jac_start_date' => $this->getStringParameter('date'),
                'jac_start_time' => $this->getStringParameter('time'),
            ];
        } elseif ($type === 2) {
            $jacColVal = [
                'jac_end_by' => $this->User->getId(),
                'jac_end_on' => date('Y-m-d H:i:s'),
                'jac_end_date' => $this->getStringParameter('date'),
                'jac_end_time' => $this->getStringParameter('time'),
            ];
        } else {
            $jacColVal = [
                'jac_start_by' => $this->User->getId(),
                'jac_start_on' => date('Y-m-d H:i:s'),
                'jac_start_date' => $this->getStringParameter('date'),
                'jac_start_time' => $this->getStringParameter('time'),
                'jac_end_by' => $this->User->getId(),
                'jac_end_on' => date('Y-m-d H:i:s'),
                'jac_end_date' => $this->getStringParameter('date'),
                'jac_end_time' => $this->getStringParameter('time'),
            ];
        }
        $jacDao = new JobActionDao();
        $jacDao->doApiUpdateTransaction($this->getIntParameter('jac_id'), $jacColVal, $this->User->getId());
        $key = $this->getStringParameter('action') . $this->getIntParameter('jo_srt_id') . '.event';
        if ($type === 2) {
            $key .= '1';
        }
        $event = Trans::getWord($key, 'action');
        $jaeColVal = [
            'jae_jac_id' => $this->getIntParameter('jac_id'),
            'jae_description' => $event,
            'jae_date' => $this->getStringParameter('date'),
            'jae_time' => $this->getStringParameter('time'),
            'jae_remark' => '',
            'jae_active' => 'Y',
        ];
        $jaeDao = new JobActionEventDao();
        $jaeDao->doApiInsertTransaction($jaeColVal, $this->User->getId());
        $joDao = new JobOrderDao();
        $joDao->doApiUpdateTransaction($this->getIntParameter('jo_id'), [
            'jo_jae_id' => $jaeDao->getLastInsertId(),
        ], $this->User->getId());
        $jaeColVal['jae_id'] = $jaeDao->getLastInsertId();
        $keyAction = $this->getStringParameter('action') . $this->getIntParameter('jo_srt_id') . '.description';
        $action = Trans::getWord($keyAction, 'action');
        $time = $this->getStringParameter('date') . ' ' . $this->getStringParameter('time');
        $jaeColVal['jae_time_on'] = DateTimeParser::format($time, 'Y-m-d H:i', 'H:i d M Y');
        $jaeColVal['jae_added_on'] = date('H:i d M Y');
        $jaeColVal['jae_action'] = $action;
        $jaeColVal['jae_added_by'] = $this->User->getName();

        return $jaeColVal;
    }


    /**
     * Function to get so Id
     *
     * @param int $joId To store job id
     * @param int $joManagerId To store job id
     * @param int $usId To store job id
     * @return string
     */
    protected function isUserOfficer(int $joId, int $joManagerId, int $usId): string
    {
        $officer = 'N';
        if ($usId === $joManagerId) {
            $officer = 'Y';
        } else {
            $data = JobOfficerDao::getByJobOrderAndUser($joId, $usId);
            if (empty($data) === false) {
                $officer = 'Y';
            }
        }
        return $officer;
    }

}
