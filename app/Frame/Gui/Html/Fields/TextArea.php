<?php

namespace App\Frame\Gui\Html\Fields;

use App\Frame\Gui\Html\FieldsInterface;
use App\Frame\Gui\Html\Html;

class TextArea extends Html implements FieldsInterface
{
    /**
     * Constructor to load when there is a new object created.
     *
     * @param string  $id    The id of the element.
     * @param string  $value The value of the element.
     * @param integer $rows  Number of rows for the text area.
     * @param integer $cols  Number of columns for the text area.
     */
    public function __construct($id, $value, $rows, $cols)
    {
        $this->setTag('textarea');
        $this->addAttribute('name', $id);
        $this->addAttribute('id', $id);
        $this->addAttribute('rows', $rows);
        $this->addAttribute('cols', $cols);
        $this->addAttribute('class', 'form-control');
        $this->addContent($value);
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
