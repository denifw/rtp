<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 15/03/2017 C-Book
 */

namespace App\Frame\Gui\Html\Fields;

use App\Frame\Gui\Html\FieldsInterface;
use App\Frame\Gui\Html\Html;

/**
 * Class to manage creation of text field.
 *
 * @package    app
 * @subpackage Util\Gui\Html\Field
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  15/03/2017 C-Book
 */
class Text extends Html implements FieldsInterface
{
    /**
     * Constructor to load when there is a new object created.
     *
     * @param string               $id    The id of the element.
     * @param string|integer|float $value The value of the element.
     */
    public function __construct($id, $value)
    {
        $this->setTag('input');
        $this->addAttribute('type', 'text');
        $this->addAttribute('name', $id);
        $this->addAttribute('id', $id);
        $this->addAttribute('value', $value);
        $this->addAttribute('class', 'form-control input-sm');
        $this->addAttribute('autocomplete', 'off');
    }


    /**
     * Function to set the read only value.
     *
     * @param bool $readOnly to set the value of read only
     *
     * @return void
     */
    public function setReadOnly(bool $readOnly = true): void
    {
        if ($readOnly === true) {
            if (\in_array('readonly', $this->Attributes, true) === false) {
                $this->addAttribute('readonly', 'readonly');
            }
        } else {
            if (\in_array('readonly', $this->Attributes, true) === true) {
                unset($this->Attributes['readonly']);
            }
        }
    }


}

