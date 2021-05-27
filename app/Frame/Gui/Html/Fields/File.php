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

/**
 * Class to manage creation of text field.
 *
 * @package    app
 * @subpackage Util\Gui\Html\Field
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  15/03/2017 C-Book
 */
class File extends Text
{
    /**
     * Constructor to load when there is a new object created.
     *
     * @param string               $id    The id of the element.
     * @param string|integer|float $value The value of the element.
     */
    public function __construct($id, $value)
    {
        parent::__construct($id, $value);
        $this->addAttribute('type', 'file');
    }

}

