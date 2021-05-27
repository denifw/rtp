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
class NumberGeneralEquipment extends AbstractTemplate
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
        $this->setHeight(120);
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
        $result = '<div class="' . $class . '" style="' . $this->getHeightStyle() . '">';
        if ($this->isValidData('title') === true) {
            $result .= '<div class="title">';
            $result .= '<h5 style=" font-size: 18px">' . $this->getData('title') . '</h5>';
            $result .= '</div>';
        }
        $data = [];
        $dataText = '';
        if ($this->isValidData('data') === true) {
            $data = $this->getData('data');
        }
        if (empty($data) === false) {
            $dataText .= ' <div style="font-size: 15px;">';
            foreach ($data as $label => $value) {
                $dataText .= '<div style="width: 100%; float: left">';
                $dataText .= '<div style="float: left; width: 50%; font-size: 15px; font-weight: normal">' . $label . '</div>';
                $dataText .= '<div style="float: right; width: 50%; text-align: right;padding-right: 10px">' . $value . '</div>';
                $dataText .= '<div style="clear: both"></div>';
                $dataText .= '</div>';
            }
            $dataText .= '</div>';
        }

        $result .= '<div class="count" style="margin-top: 5px !important; text-align: left;">' . $dataText . '</div>';


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
    }
}
