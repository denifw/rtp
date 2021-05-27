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


/**
 * This class to create layour profile card
 *
 * @package    app
 * @subpackage Frame\Gui\Templates
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2018 Spada
 */
class CardImage extends AbstractTemplate
{

    /**
     * Function to create the view.
     *
     * @return string
     */
    protected function createTemplate(): string
    {
        $result = '<div class="card">';
        $result .= '<div class="card-content  menu_fixed scroll-view" style="' . $this->getHeightStyle() . '">';
        $result .= '<div class="scroll-view">';
        $result .= '<div class="text-center">';
        $result .= '<img src="' . $this->getData('img_path') . '" alt="" class="img-square" style="width: auto; height: 128px; max-width: 100%;">';
        $result .= '<h5>' . $this->getData('title') . '</h5>';
        if ($this->isValidData('subtitle') === true) {
            $result .= '<h5>' . $this->getData('subtitle') . '</h5>';
        }
        $result .= '</div>';
        $result .= '<div class="clearfix"></div>';
        $result .= '</div>';
        $result .= '</div>';
        if ($this->isValidData('buttons') === true) {
            $buttons = $this->getData('buttons');
            $result .= '<div class="card-footer">';
            foreach ($buttons AS $btn) {
                $result .= $btn;
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
