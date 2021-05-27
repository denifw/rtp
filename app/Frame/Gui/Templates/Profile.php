<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Spada
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2018 spada
 */

namespace App\Frame\Gui\Templates;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\Icon;

/**
 * This class to create layour profile card
 *
 * @package    app
 * @subpackage Frame\Gui\Templates
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2018 Spada
 */
class Profile extends AbstractTemplate
{

    /**
     * Function to create the view.
     *
     * @return string
     */
    protected function createTemplate(): string
    {
        $image = asset('images/image-not-found.jpg');
        if ($this->isValidData('img_path') === true) {
            $image = asset('storage/' . $this->getData('img_path'));
        }

        $result = '<div class="card">';
        $result .= '<div class="card-content">';
        $result .= '<div class="text-center">';
        $result .= '<img src="' . $image . '" alt="" class="img-square" style="height: 128px">';
        $result .= '<h2>' . $this->getData('title') . '</h2>';
        if ($this->isValidData('pj_interest') === true) {
            $result .= '<h2>' . $this->getData('pj_interest') . '% P.A </h2>';
        }
        $result .= '</div>';
        $result .= '<div class="clearfix"></div>';
        $result .= '<div class="text-center">';
        $result .= '<table class="tile_info">';
        if ($this->isValidData('total_draft') === true) {
            $result .= '<tr>';
            $result .= '<td><p><i class="' . Icon::Square . ' cyan"></i>' . Trans::getWord('draftInvoice') . ' </p></td>';
            $result .= '<td style="text-align: center;">' . $this->getData('total_draft') . '</td>';
            $result .= '</tr>';
        }
        if ($this->isValidData('total_approval') === true) {
            $result .= '<tr>';
            $result .= '<td><p><i class="' . Icon::Square . ' blue"></i>' . Trans::getWord('waitingApproval') . ' </p></td>';
            $result .= '<td style="text-align: center;">' . $this->getData('total_approval') . '</td>';
            $result .= '</tr>';
        }
        if ($this->isValidData('total_payment') === true) {
            $result .= '<tr>';
            $result .= '<td><p><i class="' . Icon::Square . ' green"></i>' . Trans::getWord('waitingPayment') . ' </p></td>';
            $result .= '<td style="text-align: center;">' . $this->getData('total_payment') . '</td>';
            $result .= '</tr>';
        }
        if ($this->isValidData('total_plafon_used') === true) {
            $result .= '<tr>';
            $result .= '<td><p><i class="' . Icon::Square . ' red"></i>' . Trans::getWord('plafonUsed') . ' </p></td>';
            $result .= '<td style="text-align: center;">' . $this->getData('total_plafon_used') . '</td>';
            $result .= '</tr>';
        }
        $result .= '</table>';
        $result .= '</div>';
        $result .= '</div>';
        if ($this->isValidData('url')) {
            $result .= '<div class="card-footer">';
            $result .= '<div class="col-xs-12 col-sm-12">';
            $result .= '<button type="button" class="btn btn-primary btn-block">';
            $result .= '<i class="' . Icon::User . '"> </i> <a style="color: #ffffff" href="' . $this->getData('url') . '">View Project</a>';
            $result .= '</button>';
            $result .= '</div>';
            $result .= '</div>';
        }
        $result .= '</div>';

        return $result;
    }

    /**
     * Function to get content body of widget,load by ajax.
     *
     * @return string
     */
    public function getContentBody(): string
    {
        return '';
    }
}
