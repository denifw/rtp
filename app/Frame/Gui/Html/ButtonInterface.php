<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 29/08/2018 C-Book
 */

namespace App\Frame\Gui\Html;

/**
 * Interface to create button.
 *
 * @package    app
 * @subpackage Util\Gui\Html
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  29/08/2018 C-Book
 */
interface ButtonInterface extends HtmlInterface
{
    /**
     * Converts tha main property to a string and pass it to a variable.
     *
     * @return string
     */
    public function __toString();

    /**
     * Set the icon of the button.
     *
     * @param string $icon to store the icon name.
     *
     * @return Html
     */
    public function setIcon($icon);
    /**
     * Function to get the icon of the button.
     *
     * @return string
     */
    public function getIcon(): string;

}
