<?php

/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 18/05/2019
 * Time: 16:38
 */

namespace App\Model\Viewer\Job\Warehouse\Action;


use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Icon;
use App\Frame\System\View;
use App\Model\Dao\Job\Warehouse\Bundling\JobBundlingDao;
use App\Model\Dao\Job\Warehouse\Bundling\JobBundlingDetailDao;
use App\Model\Dao\Job\Warehouse\JobInboundDetailDao;
use App\Model\Dao\Master\Goods\GoodsMaterialDao;
use App\Model\Viewer\Job\AbstractBaseJobAction;

class BundlingAction extends AbstractBaseJobAction
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
                            $modal->setTitle(Trans::getWord('warning' ). ' - '. Trans::getWord('invalidAvailableStock' , 'message'));
                        }
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnStartPickAc', $modal->getModalId(), Icon::ShareSquareO));
                    } else {
                        $errors = JobBundlingDao::doValidateCompletePicking($this->Model->getDetailReferenceValue(), $this->Model->getIntParameter('jb_jog_id'));
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
                case 'Bundling':
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcStartBundlingMdl', 'doActionStartBundling');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnStartBundlingAc', $modal->getModalId(), Icon::Cubes));
                    } else {
                        $errors = $this->doValidateCompleteBundling();
                        if (empty($errors) === true) {
                            $modal = $this->getDefaultModal('AcEndBundlingMdl', 'doActionEndBundling', '1');
                        } else {
                            $message = implode(' <br/>', $errors);
                            $modal = $this->getWarningModal('AcEndBundlingMdl', $message);
                        }
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnEndBundlingAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
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
    private function doValidateCompleteBundling(): array
    {
        $total = JobBundlingDetailDao::getTotalCompleteQuantity($this->Model->getIntParameter('jb_id'));
        $required = $this->Model->getFloatParameter('jog_quantity');
        $result = [];
        if ($total !== $required) {
            $result[] = Trans::getWord('bundlingQuantityNotMatch', 'message', '', [
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
        $data = GoodsMaterialDao::loadDataWithStock($this->Model->getIntParameter('jog_gd_id'), $this->Model->getIntParameter('jb_wh_id'));
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $required = (float)$row['gm_quantity'] * $this->Model->getFloatParameter('jog_quantity');
            $stock = (float)$row['gm_available_stock'];
            if ($stock < $required) {
                $result[] = Trans::getWord('invalidBundlingStock', 'message', '', [
                    'sku' => $row['gm_gd_sku'],
                    'required' => $number->doFormatFloat($required),
                    'stock' => $number->doFormatFloat($stock),
                    'uom' => $row['gm_uom_code'],
                ]);
            }
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
