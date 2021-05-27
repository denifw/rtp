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
class Card extends AbstractTemplate
{
    /**
     * Attributes :
     * 1. title
     * 2. description (OPTIONAL)
     * 3. phone       (OPTIONAL)
     * 4. website     (OPTIONAL)
     * 5. items     (OPTIONAL)
     */

    /**
     * Function to create the view.
     *
     * @return string
     */
    protected function createTemplate(): string
    {
        $imgPath = asset('images/image-not-found.jpg');
        if ($this->isValidData('img_path') === true) {
            $imgPath = asset('storage/' . $this->getData('img_path'));
        }
        $result = '<div class="card" style="height: inherit; padding: 10px 0 0">';
        $result .= '<div class="card-content" style="overflow : auto; ' . $this->getHeightStyle() . '">';
        $result .= '<div class="left col-xs-7 text-left">';
        $result .= '<h2>' . $this->getData('title') . '</h2>';
        $result .= $this->getItemList();
        $result .= '</div>';
        $result .= '<div class="right col-xs-5">';
        $result .= '<img style="height: 100px" src="' . $imgPath . '" alt="" class="img-circle img-responsive">';
        $result .= '</div>';
        if ($this->isValidData('description') === true) {
            $result .= '<div class="col-xs-12 text-center">';
            $result .= '<p style="text-align: justify;">' . $this->getData('description') . '</p>';
            $result .= '</div>';
        }
        $result .= '</div>';
        if ($this->isValidData('RowDblClick') === true) {
            $result .= '<div class="card-footer text-center">
                    <button onclick="' . $this->getData('RowDblClick') . '" type="button" class="btn btn-primary btn-xs">
                    <i class="' . Icon::ShareSquare . '"> </i> ' . Trans::getWord('viewMore') . '
                    </button>
                    </div>';
        }
        $result .= '</div>';

        return $result;
    }

    /**
     * Function to generate list item
     *
     * @return string
     */
    private function getItemList(): string
    {
        $item = '';
        if ($this->isValidData('items') === true) {
            foreach ($this->getData('items') as $key => $value) {
                $item .= '<li>' . $value . '</li>';
            }
        }
        if ($this->isValidData('phone') === true) {
            $item .= '<li> <i class="' . Icon::Phone . '"> </i> ' . $this->getData('phone') . '</li>';
        } else {
            $item .= '<li> <i class="' . Icon::Phone . '"> </i> </li>';
        }
        if ($this->isValidData('website') === true) {
            $item .= '<li> <i class="' . Icon::Globe . '"> </i> ' . $this->getData('website') . '</li>';
        }
        $result = '';
        if (empty($item) === false) {
            $result = '<ul class="list-unstyled"> ' . $item . '</ul>';
        }

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
