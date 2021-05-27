<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Gui\Templates;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Icon;

/**
 * Class to create number template.
 *
 * @package    app
 * @subpackage Frame\Gui\Templetes
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class NumberGeneral extends AbstractTemplate
{
    /**
     * Attributes :
     * 1. title (OPTIONAL)
     * 2. icon (OPTIONAL)
     * 3. tile_style (OPTIONAL)
     * 4. amount
     * 5. label
     */

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id To set the id of the template.
     */
    public function __construct(string $id = '')
    {
        parent::__construct($id);
        $this->setHeight(100);
    }


    /**
     * Function to create the view.
     *
     * @return string
     */
    protected function createTemplate(): string
    {
        $class = 'tile-stats';
        if ($this->isValidData('tile_style') === true) {
            $class .= ' ' . $this->getData('tile_style');
        }
        $result = '<div id="' . $this->Id . 'panel" class="x_panel_widget ' . $class . '" style="' . $this->getHeightStyle() . '">';
        if ($this->isValidData('title') === true) {
            $result .= '<div class="x_title_widget">';
            $result .= '<h5 class="title pull-left"  id="' . $this->Id . 'title">' . $this->getData('title') . '</h5>';
            $result .= '<div class="clearfix"></div>';
            $result .= '</div>';
        }
        # param *amount*
        $amount = $this->getData('amount');
        if ($this->isValidData('uom') === true) {
            $amount .= ' <label style="font-size: 15px;">' . $this->getData('uom') . '</label>';
        }
        if ($this->isValidData('url') === true) {
            $result .= '<div class="count" style="margin: 0 !important; text-align: center;">' . $amount . '</div>';
        } else {
            $result .= '<div class="count" style="margin-top: 14px !important; text-align: center;">' . $amount . '</div>';
        }

        # param *label*
        if ($this->isValidData('label') === true) {
            $result .= '<h5>' . $this->getData('label') . '</h5>';
        }
        if ($this->isValidData('url') === true) {
            $result .= '<div class="tile-footer">';
            $result .= '<a  href="javascript:;" onclick="' . $this->getData('url') . '">' . Trans::getWord('viewDetails') . ' <i class="' . Icon::AngleRight . '"></i></a>';
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
        # param *amount*
        $result = '';
        $amount = $this->getData('amount');
        if ($this->isValidData('uom') === true) {
            $amount .= ' <label style="font-size: 15px;">' . $this->getData('uom') . '</label>';
        }
        if ($this->isValidData('url') === true) {
            $result .= '<div class="count" style="margin: 0 !important; text-align: center;">' . $amount . '</div>';
        } else {
            $result .= '<div class="count" style="margin-top: 14px !important; text-align: center;">' . $amount . '</div>';
        }

        # param *label*
        if ($this->isValidData('label') === true) {
            $result .= '<h5>' . $this->getData('label') . '</h5>';
        }
        if ($this->isValidData('url') === true) {
            $result .= '<div class="tile-footer">';
            $result .= '<a  href="javascript:;" onclick="' . $this->getData('url') . '">' . Trans::getWord('viewDetails') . ' <i class="' . Icon::AngleRight . '"></i></a>';
            $result .= '</div>';
        }

        return $result;
    }
}
