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
use App\Model\Dao\Job\Warehouse\JobMovementDetailDao;
use App\Model\Viewer\Job\AbstractBaseJobAction;

class StockMovementAction extends AbstractBaseJobAction
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
        if (empty($this->Action) === false && $this->Action['jac_action'] === 'Move') {
            if (empty($this->Action['jac_start_on']) === true) {
                $modal = $this->getDefaultModal('AcStartMoveMdl', 'doActionStartMove');
                $View->addModal($modal);
                $View->addButton($this->getDefaultButton('btnStartMoveAc', $modal->getModalId(), Icon::ShareSquareO));
            } else {
                $errors = $this->doValidateComplete();
                if (empty($errors) === true) {
                    $modal = $this->getDefaultModal('AcEndMoveMdl', 'doActionEndMove', '1');
                } else {
                    $message = implode(' <br/>', $errors);
                    $modal = $this->getWarningModal('AcEndUnloadMdl', $message);
                }
                $View->addModal($modal);
                $View->addButton($this->getDefaultButton('btnEndMoveAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
            }
        }
    }

    /**
     * Function to get document action modal.
     *
     * @return array
     */
    private function doValidateComplete(): array
    {
        $user = $this->Model->getUser();
        $result = [];
        $isExist = JobMovementDetailDao::isExistData($this->Model->getIntParameter('jm_id'));
        if ($isExist === false) {
            $result[] = Trans::getWord('movementGoodsValidation', 'message');
        }
        if (empty($result) === false && $user->Settings->getNameSpace() === 'mbs') {
            $valid = JobMovementDetailDao::isDataValidToCompleteMovement($this->Model->getIntParameter('jm_id'));
            if ($valid === false) {
                $result[] = Trans::getWord('movementCompleteValidation', 'message');
            }
        }

        return $result;
    }
}
