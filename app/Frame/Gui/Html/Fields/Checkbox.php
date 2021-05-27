<?php

namespace App\Frame\Gui\Html\Fields;

use App\Frame\Gui\Html\Html;

class Checkbox extends Html
{

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string  $id      The unique id of the field.
     * @param string  $value   The post value when selected.
     * @param boolean $checked Is the field checked or not.
     */
    public function __construct($id, $value, $checked = false)
    {
        $this->setTag('input');
        $this->addAttribute('type', 'checkbox');
        $this->addAttribute('name', $id);
        $this->addAttribute('id', $id);
        $this->addAttribute('value', $value);
        if ($checked === true) {
            $this->addAttribute('checked', 'checked');
        }
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
            if (\in_array('onclick', $this->Attributes, true) === false) {
                $this->addAttribute('onclick', 'return false;');
            }
        } else {
            if (\in_array('onclick', $this->Attributes, true) === true) {
                unset($this->Attributes['onclick']);
            }
        }
    }
}