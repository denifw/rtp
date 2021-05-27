<?php

namespace App\Frame\Gui\Html\Fields;

use App\Frame\System\Session\UserSession;

class Number extends Text
{
    /**
     * Property to store unique id of calendar.
     *
     * @var string $IdCalendar
     */
    private $Id;

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id    The Identifier or id from the element.
     * @param string $value The date value of the calender field.
     */
    public function __construct($id, $value)
    {
        parent::__construct($id, $value);
        $this->Id = $id;
    }

    /**
     * Converts tha main property to a string and pass it to a variable.
     *
     * @return string
     */
    public function __toString()
    {

        return $this->createField();
    }

    /**
     * Create calender element.
     *
     * @return string
     */
    private function createField(): string
    {
        $result = '';
        $result .= parent::__toString();
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
        $user = new UserSession();
        $thousand = ',';
        $decimal = '.';
        $decimalNumber = '2';
        if ($user->isSet()) {
            $thousand = $user->Settings->getThousandSeparator();
            $decimal = $user->Settings->getDecimalSeparator();
            $decimalNumber = $user->Settings->getDecimalNumber();
        }
        $varJs = $this->Id . '_var';
        $javascript = '<script type="text/javascript">';
        $javascript .= 'var ' . $varJs . " = new App.NumberField('" . $this->Id . "', '" . $thousand . "', '" . $decimal . "', " . $decimalNumber . ');';
        $javascript .= $varJs . '.create();';
        $javascript .= '</script>';

        return $javascript;
    }

}