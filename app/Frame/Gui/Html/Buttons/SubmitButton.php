<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 29/08/2018 C-Book
 */

namespace App\Frame\Gui\Html\Buttons;

use App\Frame\Exceptions\Message;

/**
 * Class to handle submit button.
 *
 * @package    app
 * @subpackage Util\Gui\Buttons
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  29/08/2018 C-Book
 */
class SubmitButton extends Button
{
    /**
     * Property to store the action name.
     *
     * @var string $ActionName
     */
    private $ActionName;
    /**
     * Property to store the form id.
     *
     * @var string $FormId
     */
    private $FormId;
    /**
     * Property to store the button id.
     *
     * @var string $Id
     */
    private $Id;
    /**
     * Property to store the trigger to enable loading.
     *
     * @var string $EnableLoading
     */
    private $EnableLoading = true;

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id         The id of the element.
     * @param string $value      The value of the element.
     * @param string $actionName The type of the element.
     * @param string $formId     The type of the element.
     */
    public function __construct($id, $value, $actionName, $formId)
    {
        parent::__construct($id, $value);
        $this->Id = $id;
        if (empty($actionName) === false && empty($formId) === false) {
            $this->ActionName = $actionName;
            $this->FormId = $formId;
        } else {
            Message::throwMessage('Missing parameter action name / form id for submit button');
        }
    }

    /**
     * Converts tha main property to a string and pass it to a variable.
     *
     * @param bool $enable To store the trigger to enable the loading.
     *
     * @return self
     */
    public function setEnableLoading(bool $enable = true): self
    {

        $this->EnableLoading = $enable;

        return $this;
    }

    /**
     * Converts tha main property to a string and pass it to a variable.
     *
     * @return string
     */
    public function __toString()
    {
        $result = parent::__toString();
        $result .= $this->getJavascript();

        return $result;
    }

    /**
     * Returns a string if the javascript must be loaded.
     *
     * @return string
     */
    public function getJavascript(): string
    {
        $varJs = $this->Id . '_var';
        $javascript = '<script type="text/javascript">';
        $javascript .= 'var ' . $varJs . " = new App.Action('" . $this->Id . "', '" . $this->FormId . "', '" . $this->ActionName . "');";
        $javascript .= $varJs . '.setEnableLoading(' . $this->EnableLoading . ');';
        $javascript .= $varJs . '.create();';
        $javascript .= '</script>';

        return $javascript;
    }

}
