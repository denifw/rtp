<?php

namespace App\Frame\Gui\Html\Fields;

use App\Frame\Exceptions\Message;
use App\Frame\Gui\Html\FieldsInterface;
use App\Frame\Gui\Html\Html;

class CheckBoxGroup extends Html implements FieldsInterface
{
    /**
     * Property to store all check box data.
     *
     * @var array $ListCheckBox
     */
    private $ListCheckBox = [];
    /**
     * Property to store read only state.
     *
     * @var bool $ReadOnly
     */
    private $ReadOnly = false;
    /**
     * Property to store the id of element.
     *
     * @var String $Id
     */
    private $Id;
    /**
     * Property to store the id of element.
     *
     * @var array $CheckedValues
     */
    private $CheckedValues = [];

    /**
     * Property to store the id of element.
     *
     * @var bool $IsOldValueExists ;
     */
    private $IsOldValueExists;

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id               The unique id of the field.
     * @param array  $values           To store the selected values.
     * @param bool   $isOldValueExists To store the condition if there is old value from form submit.
     */
    public function __construct($id, array $values = [], bool $isOldValueExists = false)
    {
        $this->Id = $id;
        $this->IsOldValueExists = $isOldValueExists;
        $this->addAttribute('id', $id);
        $this->CheckedValues = $values;
    }

    /**
     * Return the radio group  as string.
     *
     * @return string
     */
    public function __toString()
    {
        $result = '';
        if (empty($this->ListCheckBox) === false) {
            $result .= '<div style = "line-height: normal" class="form-check-input" id="' . $this->Id . '">';
            $index = 0;
            foreach ($this->ListCheckBox as $row) {
                $checked = $row['checked'];
                if ($this->IsOldValueExists === true && array_key_exists($index, $this->CheckedValues) === true) {
                    $checked = ($row['value'] === $this->CheckedValues[$index]);
                }
                $cb = new Checkbox($this->Id . '[' . $index . ']', $row['value'], $checked);
                $cb->setReadOnly($this->ReadOnly);
                $result .= $cb;
                $result .= '<label style="padding-right: 5px;" class="check-label" for="' . $this->Id . '[' . $index . ']">' . $row['text'] . '</label>';
                $index++;
            }
            $result .= '</div>';
        } else {
            Message::throwMessage('Empty list check box data.');
        }

        return $result;
    }

    /**
     * Function to add single radio.
     *
     * @param string $text         To store the text for check box
     * @param string $value        To store the value of check box
     * @param string $checkedValue To store the condition of check box
     *
     * @return void
     */
    public function addCheckBox(string $text, string $value, string $checkedValue): void
    {
        $checked = false;
        if ($checkedValue === $value) {
            $checked = true;
        }
        $this->ListCheckBox[] = [
            'text' => $text,
            'value' => $value,
            'checked' => $checked,
        ];
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
        $this->ReadOnly = $readOnly;
    }


}