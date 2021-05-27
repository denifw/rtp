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
class Number extends AbstractTemplate
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
            $result .= '<h2>' . $this->getData('title') . '</h2>';
            $result .= '</div>';
        }
        # param *icon*
        if ($this->isValidData('icon') === true) {
            $result .= '<div class="icon">';
            $result .= '<i class="' . $this->getData('icon') . '"></i>';
            $result .= '</div>';
        }
        # param *amount*
        $result .= '<div class="count">' . $this->getData('amount') . '</div>';

        # param *label*
        if ($this->isValidData('label') === true) {
            $result .= '<h5>' . $this->getData('label') . '</h5>';
        }
        $result .= '<div class="tile-footer">';
        if ($this->isValidData('url')) {
            $result .= '<a  href="' . $this->getData('url') . '">' . Trans::getWord('viewDetails') . ' <i class="' . Icon::AngleRight . '"></i></a>';
        } else {
            $result .= '<a href="#">' . Trans::getWord('viewDetails') . ' <i class="' . Icon::AngleRight . '"></i></a>';
        }
        $result .= '</div>';
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
