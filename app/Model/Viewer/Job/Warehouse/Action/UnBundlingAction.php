<?php

/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 18/05/2019
 * Time: 16:38
 */

namespace App\Model\Viewer\Job\Warehouse\Action;


use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Icon;
use App\Frame\System\View;
use App\Model\Dao\Job\Warehouse\Bundling\JobBundlingDao;
use App\Model\Dao\Job\Warehouse\Bundling\JobBundlingDetailDao;
use App\Model\Dao\Job\Warehouse\JobInboundDetailDao;
use App\Model\Dao\Master\Goods\GoodsMaterialDao;
use App\Model\Viewer\Job\AbstractBaseJobAction;
use Illuminate\Support\Facades\DB;

class UnBundlingAction extends AbstractBaseJobAction
{
    /**
     * Function to add layout for action to view
     *
     * @param View $View
     *
     * @return void
     */
    public function addActionView(View $View): void
    {
        $this->loadAction();
        if (empty($this->Action) === false) {
            switch ($this->Action['jac_action']) {
                case 'Picking':
                    if (empty($this->Action['jac_start_on']) === true) {
                        $errors = $this->doValidateMaterialStock();
                        if (empty($errors) === true) {
                            $modal = $this->getDefaultModal('AcStartPickMdl', 'doActionStartPicking');
                        } else {
                            $message = implode(' <br/>', $errors);
                            $modal = $this->getWarningModal('AcStartPickMdl', $message);
                            $modal->setTitle(Trans::getWord('warning') . ' - ' . Trans::getWord('invalidAvailableStock', 'message'));
                        }
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnStartPickAc', $modal->getModalId(), Icon::ShareSquareO));
                    } else {
                        $errors = $this->doValidateCompletePicking();
                        if (empty($errors) === true) {
                            $modal = $this->getDefaultModal('AcEndPickMdl', 'doActionEndPicking', '1');
                        } else {
                            $message = implode(' <br/>', $errors);
                            $modal = $this->getWarningModal('AcEndPickMdl', $message);
                        }
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnEndPickAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
                    }
                    break;
                case 'UnBundling':
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcStartUnBundleMdl', 'doActionStartUnBundling');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnStartUnBundleAc', $modal->getModalId(), Icon::Cubes));
                    } else {
                        $errors = $this->doValidateCompleteBundling();
                        if (empty($errors) === true) {
                            $modal = $this->getDefaultModal('AcEndUnBundleMdl', 'doActionEndUnBundling', '1');
                        } else {
                            $message = implode(' <br/>', $errors);
                            $modal = $this->getWarningModal('AcEndUnBundleMdl', $message);
                        }
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnEndUnBundleAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
                    }
                    break;
                case 'PutAway':
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcStartPutAwayMdl', 'doActionStartPutAway');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnStartPutAwayAc', $modal->getModalId(), Icon::Cubes));
                    } else {
                        $errors = $this->doValidateCompletePutAway();
                        if (empty($errors) === true) {
                            $modal = $this->getDefaultModal('AcEndPutAwayMdl', 'doActionEndPutAway', '1');
                        } else {
                            $message = implode(' <br/>', $errors);
                            $modal = $this->getWarningModal('AcEndPutAwayMdl', $message);
                        }
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnEndPutAwayAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
                    }
                    break;
            }
        }
    }


    /**
     * Function to get document action modal.
     *
     * @return array
     */
    private function doValidateCompletePicking(): array
    {
        $result = [];
        $jogWheres = [];
        $jogWheres[] = '(jog_deleted_on IS NULL)';
        $jogWheres[] = '(jog_jo_id = ' . $this->Model->getDetailReferenceValue() . ')';
        $jogWheres[] = '(jog_id = ' . $this->Model->getIntParameter('jb_jog_id') . ')';
        $strJogWhere = ' WHERE ' . implode(' AND ', $jogWheres);
        $jodWheres = [];
        $jodWheres[] = '(j.jod_deleted_on IS NULL)';
        $jodWheres[] = '(job.job_jo_id = ' . $this->Model->getDetailReferenceValue() . ')';
        $strJodWhere = ' WHERE ' . implode(' AND ', $jodWheres);
        $query = 'SELECT jog.jog_jo_id, jog.qty_outbound, jod.job_jo_id, jod.qty_pick, (jog.qty_outbound - jod.qty_pick) as diff_qty
                FROM (SELECT jog_jo_id, sum(jog_quantity) as qty_outbound
                      FROM job_goods ' . $strJogWhere . ' GROUP BY jog_jo_id) as jog
                       INNER JOIN
                     (SELECT job.job_jo_id, sum(j.jod_quantity) as qty_pick
                      FROM job_outbound_detail as j INNER JOIN
                       job_outbound as job ON j.jod_job_id = job.job_id ' . $strJodWhere . ' GROUP BY job.job_jo_id) as jod ON jod.job_jo_id = jog.jog_jo_id
                GROUP BY jog.jog_jo_id, jog.qty_outbound, jod.job_jo_id, jod.qty_pick ';
        $sqlResult = DB::select($query);
        if (count($sqlResult) === 1) {
            $data = DataParser::objectToArray($sqlResult[0], [
                'jog_jo_id',
                'job_jo_id',
                'qty_outbound',
                'qty_pick',
                'diff_qty',
            ]);
            if ((float)$data['diff_qty'] > 0) {
                $result[] = Trans::getWord('outboundStorageNotMatch', 'message', '', [
                    'outbound' => $data['qty_outbound'],
                    'taken' => $data['qty_pick'],
                ]);
            }
        } else {
            $result[] = Trans::getWord('outboundPickingEmpty', 'message');
        }
        return $result;
    }

    /**
     * Function to get document action modal.
     *
     * @return array
     */
    private function doValidateCompleteBundling(): array
    {
        $total = JobBundlingDetailDao::getTotalCompleteQuantity($this->Model->getIntParameter('jb_id'));
        $required = $this->Model->getFloatParameter('jog_quantity');
        $result = [];
        if ($total !== $required) {
            $result[] = Trans::getWord('UnBundlingQuantityNotMatch', 'message', '', [
                'planning' => $required,
                'bundling' => $total,
            ]);
        }
        return $result;
    }

    /**
     * Function to get document action modal.
     *
     * @return array
     */
    private function doValidateMaterialStock(): array
    {
        $result = [];
        $stock = JobInboundDetailDao::getStockByGoodsAndUnitId($this->Model->getIntParameter('jog_gd_id'), $this->Model->getIntParameter('jog_gdu_id'));
        $number = new NumberFormatter();
        $required = $this->Model->getFloatParameter('jog_quantity');
        if ($stock < $required) {
            $result[] = Trans::getWord('invalidBundlingStock', 'message', '', [
                'sku' => $this->Model->getStringParameter('jog_gd_sku'),
                'required' => $number->doFormatFloat($required),
                'stock' => $number->doFormatFloat($stock),
                'uom' => $this->Model->getStringParameter('jog_unit'),
            ]);
        }
        return $result;
    }

    /**
     * Function to get document action modal.
     *
     * @return array
     */
    private function doValidateCompletePutAway(): array
    {
        $result = [];
        $diffQty = JobInboundDetailDao::getTotalDifferentQuantityLoadWithStoredByJobInboundId($this->Model->getIntParameter('jb_inbound_id'));
        if (empty($diffQty) === false) {
            if ((float)$diffQty['diff_qty'] !== 0.0) {
                $result[] = Trans::getWord('inboundStorageNotMatch', 'message', '', [
                    'inbound' => $diffQty['qty_actual'],
                    'stored' => $diffQty['qty_stored'],
                ]);
            }
        } else {
            $result[] = Trans::getWord('inboundStorageEmpty', 'message');
        }
        return $result;
    }


}
