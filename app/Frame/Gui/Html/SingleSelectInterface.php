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
 * Interface to create field.
 *
 * @package    app
 * @subpackage Util\Gui\Html
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  29/08/2018 C-Book
 */
interface SingleSelectInterface
{

    /**
     * Add extra hidden field parameter.
     *
     * @param string $value The value that the field will contain.
     *
     * @return void
     */
    public function setHiddenFieldValue($value = ''): void;

    /**
     * Add extra hidden field parameter.
     *
     * @return string
     */
    public function getHiddenFieldId(): string;
}
