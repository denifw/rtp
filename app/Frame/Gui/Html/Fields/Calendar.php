<?php

namespace App\Frame\Gui\Html\Fields;

class Calendar extends Text
{
    /**
     * Property to store unique id of calendar.
     *
     * @var string $IdCalendar
     */
    private $IdCalendar;

    /**
     * Property to store read only value.
     *
     * @var bool $ReadOnle
     */
    private $ReadOnly = false;

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id The Identifier or id from the element.
     * @param string $value The date value of the calender field.
     */
    public function __construct($id, $value)
    {
        parent::__construct($id, $value);
        $this->IdCalendar = $id;
    }

    /**
     * Converts tha main property to a string and pass it to a variable.
     *
     * @return string
     */
    public function __toString()
    {

        return $this->createCalender();
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
        parent::setReadOnly($readOnly);
        $this->ReadOnly = $readOnly;
    }

    /**
     * Create calender element.
     *
     * @return string
     */
    private function createCalender(): string
    {
        if($this->ReadOnly === true) {
            return parent::__toString();
        }
        $this->addAttribute('size', '10');
        $this->addAttribute('maxlength', '10');
        $result = '';
        $result .= '<div class="input-group date" id="' . $this->IdCalendar . '_div">';
        $result .= parent::__toString();
        $result .= '<span class="input-group-addon">';
        $result .= '<span class="glyphicon glyphicon-calendar"></span>';
        $result .= '</span>';
        $result .= '</div>';
        $result .= $this->getJavascript();

        return $result;
    }


    /**
     * Returns a string if the javascript must be loaded.
     *
     * @return string
     */
    private function getJavascript(): string
    {
        $varJs = $this->IdCalendar . '_var';
        $idJs = $this->IdCalendar . '_div';
        $javascript = '<script type="text/javascript">';
        $javascript .= 'var ' . $varJs . " = new App.Calendar('" . $idJs . "');";
        $javascript .= $varJs . '.create();';
        $javascript .= '</script>';

        return $javascript;
    }

}
