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
class ProfileGeneral extends AbstractTemplate
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
        $result .= '<div class="card-content  menu_fixed scroll-view" style="' . $this->getHeightStyle() . '">';
        $result .= '<div class="scroll-view">';
        $result .= '<div class="text-center">';
        if ($this->isExistData('img_path') === true) {
            $result .= '<img src="' . $image . '" alt="" class="img-square" style="width: auto; height: 128px; max-width: 100%;">';
        }
        $result .= '<h2>' . $this->getData('title') . '</h2>';
        if ($this->isValidData('subtitle') === true) {
            $subtitle = $this->getData('subtitle');
            if (\is_array($subtitle) === true) {
                foreach ($subtitle as $row) {
                    $result .= '<h5>' . $row . '</h5>';
                }
            } else {
                $result .= '<h2>' . $subtitle . '</h2>';
            }
        }
        $result .= '</div>';
        $result .= '<div class="clearfix"></div>';
        if ($this->isValidData('infos') === true && \is_array($this->getData('infos')) === true) {
            $result .= '<div class="text-center">';
            $result .= '<table class="tile_info">';
            $infos = $this->getData('infos');
            foreach ($infos AS $info) {
                $result .= '<tr>';
                $result .= '<td>';
                if (array_key_exists('icon', $info) === true && $info['icon'] !== '') {
                    $result .= '<i class="' . $info['icon'] . '"></i>';
                }
                if (array_key_exists('text', $info) === true) {
                    $result .= $info['text'];
                }
                $result .= '</td>';
                if (array_key_exists('value', $info) === true) {
                    $result .= '<td style="text-align: center;">' . $info['value'] . '</td>';
                }
                $result .= '</tr>';
            }
            $result .= '</table>';
            $result .= '<p></p>';
            $result .= '</div>';
        }
        $result .= '<div class="clearfix"></div>';
        if ($this->isValidData('description') === true) {
            $result .= '<div class="text-center">';
            $result .= '<p style="text-align: justify">' . $this->getData('description') . '</p>';
            $result .= '</div>';

        }
        $result .= '</div>';
        $result .= '</div>';
        $url = '';
        if ($this->isValidData('RowDblClick') === true) {
            $url = $this->getData('RowDblClick');
        }
        if ($this->isValidData('buttons') === true) {
            $url = $this->getData('buttons');
        }
        if (empty($url) === false) {
            $result .= '<div class="card-footer">';
            if (\is_array($url) === true) {
                foreach ($url AS $row) {
                    $result .= '<button type="button" class="' . $row['class'] . '" onclick="' . $row['url'] . '">';
                    $result .= '<i class="' . $row['icon'] . '"> </i> ' . $row['text'];
                    $result .= '</button>';
                }
            } else {
                $result .= '<button type="button" class="btn btn-primary btn-xs" onclick="' . $url . '">';
                $result .= '<i class="' . Icon::ShareSquare . '"> </i> ' . Trans::getWord('viewMore');
                $result .= '</button>';
            }
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
