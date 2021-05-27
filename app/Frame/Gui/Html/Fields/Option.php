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

use App\Frame\Gui\Html\Html;

/**
 * Class to manage creation of option field.
 *
 * @package    app
 * @subpackage Util\Gui\Html\Field
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  15/03/2017 C-Book
 */
class Option extends Html
{

    /**
     * If the value from the option.
     *
     * @var string $Value
     */
    private $Value;

    /**
     * Constructor of the option object.
     *
     * @param string  $content    The content to display in the drop down list.
     * @param string  $value      The value linked to the selected option.
     * @param boolean $isSelected The selected field.
     */
    public function __construct($content, $value, $isSelected = false)
    {
        $this->setTag('option');
        # Store the selected property
        $this->setSelected($isSelected);
        $this->addAttribute('value', $value);
        $this->addContent($content);
        $this->Value = $value;
    }

    /**
     * Set the selected field.
     *
     * @param boolean $boolean If set to true te option will be selected.
     *
     * @return void
     */
    public function setSelected($boolean): void
    {
        if (is_bool($boolean) === true && $boolean === true) {
            $this->addAttribute('selected', 'selected');
        }
    }

    /**
     * Returns the value of the option.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->Value;
    }
}
