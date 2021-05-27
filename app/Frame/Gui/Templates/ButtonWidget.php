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

/**
 * Class to create number template.
 *
 * @package    app
 * @subpackage Frame\Gui\Templetes
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class ButtonWidget extends AbstractTemplate
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
        $style = 'height: auto !important;';
        $result = '<a href="' . url('/' . $this->getData('route')) . '">';
        $result .= '<div class="' . $class . '" style="' . $style . '">';
        if ($this->isValidData('title') === true) {
            $result .= '<div class="title text-center">';
            $result .= '<h5 style="font-weight: bold" id="' . $this->Id . 'title">' . $this->getData('title') . '</h5>';
            $result .= '</div>';
        }
        # param *amount*
        $image = asset('images/image-not-found.jpg');
        if ($this->isValidData('image') === true) {
            $image = $this->getData('image');
        }
        $result .= '<div class="count" style="margin: 5px 0 !important; text-align: center;"><img style="max-height: 100px; width: auto" src="' . $image . '" alt="image"/></div>';

        # param *label*
        if ($this->isValidData('label') === true) {
            $result .= '<div class="tile-footer" style="margin-bottom: 5px; position: inherit !important;">';
            $result .= '<h5>' . $this->getData('label') . '</h5>';
            $result .= '</div>';
        }
        $result .= '</div>';
        $result .= '</a>';

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
        $image = asset('images/image-not-found.jpg');
        if ($this->isValidData('image') === true) {
            $image = $this->getData('image');
        }
        $result .= '<div class="count" style="margin: 5px 0 !important; text-align: center;"><img style="max-height: 100px; width: auto" src="' . $image . '" alt="image"/></div>';

        # param *label*
        if ($this->isValidData('label') === true) {
            $result .= '<div class="tile-footer" style="margin-bottom: 5px; position: inherit !important;">';
            $result .= '<h5>' . $this->getData('label') . '</h5>';
            $result .= '</div>';
        }
        $result .= '</div>';
        $result .= '</a>';

        return $result;
    }
}
