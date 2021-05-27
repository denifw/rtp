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

use App\Frame\Formatter\DataParser;
use App\Frame\Gui\Html\FieldsInterface;
use App\Frame\Gui\Html\Html;

/**
 * Class to manage creation of select field.
 *
 * @package    app
 * @subpackage Util\Gui\Html\Field
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  15/03/2017 C-Book
 */
class Select extends Html implements FieldsInterface
{
    /**
     * Read only attribute in select, if true then only show the selected data.
     *
     * @var boolean $ReadOnly
     */
    private $ReadOnly = false;

    /**
     * Property to set the please select option.
     *
     * @var boolean $PleaseSelect
     */
    private $PleaseSelect = false;

    /**
     * Value of the select field.
     *
     * @var string $Selected
     */
    private $Selected = '';

    /**
     * List of the select options there are.
     *
     * @var array $Options
     */
    private $Options = [];

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string               $fieldId The id of the element.
     * @param string|integer|float $value   The value of the element.
     */
    public function __construct(string $fieldId, $value)
    {
        $this->setTag('select');
        $this->addAttribute('name', $fieldId);
        $this->addAttribute('id', $fieldId);
        $this->addAttribute('class', 'form-control input-sm');
        if ($value !== null) {
            $this->setSelected($value);
        }
    }

    /**
     * Override the original create element method.
     *
     * @return string
     */
    public function __toString()
    {
        $this->setContent($this->createOptionList());

        return parent::__toString();
    }

    /**
     * Create the complete option list.
     *
     * @return string
     */
    private function createOptionList(): string
    {
        $options = '';
        if ($this->PleaseSelect === true || $this->getNumberOfOptions() > 1) {
            $this->addOption('Please Select', '', 0);
//        } else {
//            if ($this->PleaseSelect === true) {
//                $this->addOption('Please Select', '', 0);
//            }
        }
        # Built up the complete list
        foreach ($this->Options as $value) {
            /**
             * Make sure that the option is an object of the Option Class.
             *
             * @var \App\Frame\Gui\Html\Fields\Option $option The Option object
             */
            $option = clone $value;
            if ($this->isReadOnly() === true) {
                if ((string)$option->getValue() === (string)$this->Selected) {
                    $option->setSelected(true);
                    # Concatenate all the elements
                    $options .= $option;
                }
            } else {
                if ((string)$option->getValue() === (string)$this->Selected) {
                    $option->setSelected(true);
                    # Concatenate all the elements
                }
                $options .= $option;
            }
        }

        return $options;
    }

    /**
     * Return the number of options in the select list.
     *
     * @return integer
     */
    public function getNumberOfOptions(): int
    {
        return count($this->Options);
    }

    /**
     * Add one single option to the selection list.
     *
     * @param string               $text     to set the text of the options.
     * @param string|integer|float $value    to set the value of the options.
     * @param integer              $position The position of the option to add.
     *
     * @return void
     */
    public function addOption(string $text, $value, int $position = -1): void
    {
        if (empty($text) === false) {
            $option = new Option($text, $value);
            if ($position < 0 || is_int($position) === false) {
                $this->Options[] = $option;
            } elseif ($position === 0) {
                array_unshift($this->Options, $option);
            } elseif ($position > 0) {
                if ($this->getNumberOfOptions() > $position) {
                    $newOptions = [];
                    foreach ($this->Options as $key => $oldOption) {
                        if ((int)$key === (int)$position) {
                            $newOptions[] = $option;
                        }
                        $newOptions[] = $oldOption;
                    }
                    $this->Options = $newOptions;
                } else {
                    $this->Options[] = $option;
                }
            }
        }
    }

    /**
     * Function to check is this field read only or not.
     *
     * @return boolean
     */
    public function isReadOnly(): bool
    {
        return $this->ReadOnly;
    }

    /**
     * Function to set the read only attribute.
     *
     * @param boolean $readOnly To set the read only value.
     *
     * @return void
     */
    public function setReadOnly(bool $readOnly = true): void
    {
        $this->ReadOnly = $readOnly;
    }

    /**
     * Function to set the please select option.
     *
     * @param boolean $enable To set the read only value.
     *
     * @return void
     */
    public function setPleaseSelect(bool $enable = true): void
    {
        $this->PleaseSelect = $enable;
    }

    /**
     * Add list of options to the array.
     *
     * @param array  $data  The list array option data.
     * @param string $text  to set the text of the options.
     * @param string $value to set the value of the options.
     *
     * @return void
     */
    public function addOptions(array $data, string $text = 'text', string $value = 'value'): void
    {
        if (empty($data) === false) {
            foreach ($data as $row) {
                $this->addOption(DataParser::getAttributeValue($row, $text), DataParser::getAttributeValue($row, $value));
            }
        }
    }

    /**
     * Get Selected option.
     *
     * @return string
     */
    public function getSelected(): string
    {
        return $this->Selected;
    }

    /**
     * The selected option.
     *
     * @param string $selected The key from the selected option.
     *
     * @return void
     */
    public function setSelected(string $selected): void
    {
        $this->Selected = $selected;
    }

    /**
     * Remove all option.
     *
     * @return void
     */
    public function removeAllOption(): void
    {
        $this->Options = [];
    }

    /**
     * Get all option.
     *
     * @return array
     */
    public function getAllOption(): array
    {
        return $this->Options;
    }
}
