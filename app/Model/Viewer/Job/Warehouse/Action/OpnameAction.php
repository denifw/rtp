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
use App\Model\Dao\Job\Warehouse\StockOpnameDetailDao;
use App\Model\Viewer\Job\AbstractBaseJobAction;

class OpnameAction extends AbstractBaseJobAction
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
        if (empty($this->Action) === false) {
            switch ($this->Action['jac_action']) {
                case 'Opname' :
                    if (empty($this->Action['jac_start_on']) === true) {
                        $modal = $this->getDefaultModal('AcStartOpnameMdl', 'doActionStartOpname');
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnStartOpnameAc', $modal->getModalId(), Icon::ShareSquareO));
                    } else {
                        $details = StockOpnameDetailDao::getUncompleteFigureDataBySopId($this->Model->getIntParameter('sop_id'));
                        if (empty($details) === true) {
                            $modal = $this->getDefaultModal('AcEndOpnameMdl', 'doActionEndOpname', '1');
                        } else {
                            $message = Trans::getWord('pleaseUpdateOpnameActual', 'message');
                            $modal = $this->getWarningModal('AcEndOpnameMdl', $message);
                        }
                        $View->addModal($modal);
                        $View->addButton($this->getDefaultButton('btnEndOpnameAc', $modal->getModalId(), Icon::CheckSquareO, '1'));
                    }
                    break;
                case 'Document' :
                    $modal = $this->getDefaultModal('AcDocMdl', 'doActionDocument');
                    $View->addModal($modal);
                    $View->addButton($this->getDefaultButton('btnDocAc', $modal->getModalId(), Icon::File));
                    break;
            }
        }
    }
}
