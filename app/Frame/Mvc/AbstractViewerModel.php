<?php
/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 11/04/2019
 * Time: 12:16
 */

namespace App\Frame\Mvc;


use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Icon;

abstract class AbstractViewerModel extends AbstractDetailModel
{


    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        return '';
    }

    /**
     * Base detail model constructor.
     *
     * @param string $nameSpace To store the name space of the page.
     * @param string $route To store the name space of the page.
     * @param string $detailReferenceCode To store the detail reference code.
     */
    public function __construct(string $nameSpace, string $route, string $detailReferenceCode)
    {
        parent::__construct('Viewer', $nameSpace, $route);
        $this->setDetailReferenceCode($detailReferenceCode);
    }

    /**
     * Function to get the the detail route.
     *
     * @return string
     */
    protected function getUpdateRoute(): string
    {
        return $this->PageSetting->getPageRoute() . '/detail';
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isUpdateButtonEnabled() === true && $this->isUpdate() === true && $this->PageSetting->checkPageRight('AllowUpdate') === true) {
            $btnUpdate = new HyperLink('hplUpdate', Trans::getWord('update'), url($this->getUpdateRoute() . '?' . $this->getDetailReferenceCode() . '=' . $this->getDetailReferenceValue()));
            $btnUpdate->viewAsButton();
            $btnUpdate->setIcon(Icon::Pencil)->btnSuccess()->pullRight()->btnMedium();
            $this->View->addButton($btnUpdate);
        }
        $btnReload = new Button('btnReload', Trans::getWord('reload'), 'button');
        $btnReload->setIcon(Icon::Refresh)->btnWarning()->pullRight()->btnMedium();
        $btnReload->addAttribute('onclick', 'App.reloadWindow()');
        $this->View->addButton($btnReload);

        if ($this->isCloseButtonEnabled() === true) {
            if ($this->isPopupLayout() === true) {
                $btnClose = new Button('btnClose', Trans::getWord('close'), 'button');
                $btnClose->setIcon(Icon::Close)->btnDanger()->pullRight()->btnMedium();
                $btnClose->addAttribute('onclick', 'App.closeWindow()');
                $this->View->addButton($btnClose);
            } else {
                $backUrl = $this->getStringParameter('back_url', $this->getDefaultRoute());
                $btnClose = new HyperLink('hplBack', Trans::getWord('close'), url($backUrl));
                $btnClose->viewAsButton();
                $btnClose->setIcon(Icon::MailReply)->btnDanger()->pullRight()->btnMedium();
                $this->View->addButton($btnClose);
            }
        }
    }

    /**
     * Function to generate table view.
     *
     * @param array $data To store the data.
     * @param integer $large To set the grid amount for a large screen.
     * @param integer $medium To set the grid amount for a medium screen.
     * @param integer $small To set the grid amount for a small screen.
     * @param integer $extraSmall To set the grid amount for a extra small screen.
     *
     * @return string
     * @deprecated Use StringFormatter::generateCustomTableView Instead
     */
    protected function generateTableView(array $data = [], int $large = 12, int $medium = 12, int $small = 12, $extraSmall = 12): string
    {
        $content = '<div class="col-lg-' . $large . ' col-md-' . $medium . ' col-sm-' . $small . ' col-xs-' . $extraSmall . '">';
        $content .= '<table class="table">';
        $i = 0;
        foreach ($data as $row) {
            $val = $row['value'];
            if (empty($val) === true) {
                $val = '-';
            }
            if (($i % 2) === 0) {
                $content .= '<tr style="background: #E0E0FF">';
            } else {
                $content .= '<tr>';
            }
            $content .= '<td>' . $row['label'] . '</td>';
            $content .= '<td style="font-weight: bold">' . $val . '</td>';
            $content .= '</tr>';
            $i++;
        }
        $content .= '</table>';
        $content .= '</div>';

        return $content;
    }


}
