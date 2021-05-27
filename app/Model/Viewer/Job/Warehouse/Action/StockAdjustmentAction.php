<?php
/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 18/05/2019
 * Time: 16:38
 */

namespace App\Model\Viewer\Job\Warehouse\Action;


use App\Frame\Formatter\Trans;
use App\Frame\Gui\Icon;
use App\Model\Dao\Job\Warehouse\JobAdjustmentDetailDao;
use App\Model\Viewer\Job\AbstractBaseJobAction;

class StockAdjustmentAction extends AbstractBaseJobAction
{

    /**
     * Function to add layout for action to view
     *
     * @param \App\Frame\System\View $View
     *
     * @return void
     */
    public function addActionView(\App\Frame\System\View $View): void
    {
        $this->loadAction();
        if (empty($this->Action) === false && $this->Action['jac_action'] === 'Adjust') {
            if (empty($this->Action['jac_start_on']) === true) {
                $modal = $this->getDefaultModal('AcStartAdjustMdl', 'doActionStartAdjust');
                $View->addModal($modal);
                $View->addButton($this->getDefaultButton('btnStartAdjustAc', $modal->getModalId(), Icon::ShareSquareO));
            } else {
                $details = JobAdjustmentDetailDao::loadDataByJaId($this->Model->getIntParameter('ja_id'));
                if (empty($details) === false) {
                    $modal = $this->getDefaultModal('AcEndAdjustMdl', 'doActionEndAdjust', '1');
                } else {
                    $message = Trans::getWord('pleaseUpdateAdjustmentDetail', 'message');
                    $modal = $this->getWarningModal('AcEndAdjustMdl', $message);
                }
                $View->addModal($modal);
                $View->addButton($this->getDefaultButton('btnEndAdjustAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
            }

        }
    }
}
