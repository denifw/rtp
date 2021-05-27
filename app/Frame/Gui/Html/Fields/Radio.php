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
 * Class to manage creation of radio field.
 *
 * @package    app
 * @subpackage Util\Gui\Html\Field
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  15/03/2017 C-Book
 */
class Radio extends Html implements FieldsInterface
{

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string  $identifier The unique id of the radio button.
     * @param string  $value      The value to be given on the radio.
     * @param boolean $checked    When the radio button must be checked.
     */
    public function __construct($identifier, $value, $checked = false)
    {
        $this->setTag('input');
        $this->addAttribute('type', 'radio');
        $this->addAttribute('name', $identifier);
        $this->addAttribute('id', $identifier . '_' . $value);
        $this->addAttribute('value', $value);
        if ($checked === true) {
            $this->addAttribute('checked', 'checked');
        }
    }
}