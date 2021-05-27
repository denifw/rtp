<?php

namespace App\Frame\Gui\Html\Fields;

use App\Frame\Gui\Html\FieldsInterface;

class Password extends \App\Frame\Gui\Html\Html implements FieldsInterface
{

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id    The id of the element.
     * @param string $value The value of the element.
     */
    public function __construct($id, $value)
    {
        $this->setTag('input');
        $this->addAttribute('type', 'password');
        $this->addAttribute('name', $id);
        $this->addAttribute('id', $id);
        $this->addAttribute('value', $value);
        $this->addAttribute('class', 'form-control input-sm');
        $this->addAttribute('autocomplete', 'new-' . $id);
    }
}
