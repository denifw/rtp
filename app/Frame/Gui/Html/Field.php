<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 14/03/2017 C-Book
 */

namespace App\Frame\Gui\Html;

use App\Frame\Exceptions\Message;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Fields\Calendar;
use App\Frame\Gui\Html\Fields\Checkbox;
use App\Frame\Gui\Html\Fields\CheckBoxGroup;
use App\Frame\Gui\Html\Fields\Color;
use App\Frame\Gui\Html\Fields\File;
use App\Frame\Gui\Html\Fields\Hidden;
use App\Frame\Gui\Html\Fields\Number;
use App\Frame\Gui\Html\Fields\Password;
use App\Frame\Gui\Html\Fields\Radio;
use App\Frame\Gui\Html\Fields\RadioGroup;
use App\Frame\Gui\Html\Fields\Select;
use App\Frame\Gui\Html\Fields\SingleSelect;
use App\Frame\Gui\Html\Fields\SingleSelectTable;
use App\Frame\Gui\Html\Fields\Text;
use App\Frame\Gui\Html\Fields\TextArea;
use App\Frame\Gui\Html\Fields\Time;
use App\Frame\Gui\Html\Fields\YesNo;
use App\Frame\System\Validation;

/**
 * Class to manage creation of field.
 *
 * @package    app
 * @subpackage Util\Gui\Html
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  14/03/2017 C-Book
 */
class Field
{

    /**
     * Property to store the right of the page.
     *
     * @var \App\Frame\System\Validation $Validation
     */
    private $Validation;

    /**
     * Field constructor.
     *
     * @param \App\Frame\System\Validation $validation To set the validation of the field.
     *
     */
    public function __construct(Validation $validation)
    {
        if ($validation !== null) {
            $this->Validation = $validation;
        } else {
            Message::throwMessage('Not allowed null value for the validation object inside the form.');
        }
    }

    /**
     * Function to get text field.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     *
     * @return \App\Frame\Gui\Html\Fields\Text
     */
    public function getText(string $fieldId, $value = ''): Text
    {

        $oldValue = $this->Validation->getOldValue($fieldId);
        if (empty($oldValue) === false || $oldValue === '0') {
            $value = $oldValue;
        }

        return new Text($fieldId, $value);
    }

    /**
     * Function to get color field.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     *
     * @return \App\Frame\Gui\Html\Fields\Color
     */
    public function getColor(string $fieldId, $value = ''): Color
    {

        $oldValue = $this->Validation->getOldValue($fieldId);
        if (empty($oldValue) === false || $oldValue === '0') {
            $value = $oldValue;
        }

        return new Color($fieldId, $value);
    }

    /**
     * Function to get currency field.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     *
     * @return \App\Frame\Gui\Html\Fields\Text
     */
    public function getNumber(string $fieldId, $value = ''): Text
    {
        $oldValue = $this->Validation->getOldValue($fieldId);
        if (empty($oldValue) === false || $oldValue === '0') {
            $value = $oldValue;
        }

        return new Number($fieldId, $value);
    }

    /**
     * Function to get hidden field.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     *
     * @return \App\Frame\Gui\Html\Fields\Hidden
     */
    public function getHidden(string $fieldId, $value = ''): Hidden
    {
        return new Hidden($fieldId, $value);
    }

    /**
     * Function to get submit button.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     *
     * @return \App\Frame\Gui\Html\Buttons\Button
     */
    public function getSubmitButton(string $fieldId, $value = ''): Button
    {

        $button = new Button($fieldId, $value);
        $button->addAttribute('type', 'submit');

        return $button;
    }

    /**
     * Function to get submit button.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     *
     * @return \App\Frame\Gui\Html\Buttons\Button
     */
    public function getButton(string $fieldId, $value = ''): Button
    {

        return new Button($fieldId, $value);
    }

    /**
     * Function to get hyper link button.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     * @param string $url     To store the address of link.
     *
     * @return \App\Frame\Gui\Html\Buttons\HyperLink
     */
    public function getHyperLink(string $fieldId, $value, string $url): HyperLink
    {

        return new HyperLink($fieldId, $value, $url);
    }

    /**
     * Function to get Yes No field.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     *
     * @return \App\Frame\Gui\Html\Fields\YesNo
     */
    public function getYesNo(string $fieldId, $value): YesNo
    {
        $oldValue = $this->Validation->getOldValue($fieldId);
        if (empty($oldValue) === false || $oldValue === '0') {
            $value = $oldValue;
        }
        return new YesNo($fieldId, $value);
    }

    /**
     * Function to get radio group field.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     *
     * @return \App\Frame\Gui\Html\Fields\RadioGroup
     */
    public function getRadioGroup(string $fieldId, $value): RadioGroup
    {

        return new RadioGroup($fieldId, $value);
    }


    /**
     * Function to get drop down select field.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     *
     * @return \App\Frame\Gui\Html\Fields\Select
     */
    public function getSelect(string $fieldId, $value): Select
    {
        $oldValue = $this->Validation->getOldValue($fieldId);
        if (empty($oldValue) === false || $oldValue === '0') {
            $value = $oldValue;
        }

        return new Select($fieldId, $value);
    }

    /**
     * Function to get single select field.
     *
     * @param string $callbackRoute    To store the call back route for ajax.
     * @param string $fieldId          To store the id of the field.
     * @param string $value            To store the value of the field.
     * @param string $callBackFunction To store the call back function for ajax.
     *
     * @return \App\Frame\Gui\Html\Fields\SingleSelect
     */
    public function getSingleSelect(string $callbackRoute, string $fieldId, $value, string $callBackFunction = 'loadSingleSelectData'): SingleSelect
    {
        $oldValue = $this->Validation->getOldValue($fieldId);
        if (empty($oldValue) === false || $oldValue === '0') {
            $value = $oldValue;
        }

        return new SingleSelect($callbackRoute, $callBackFunction, $fieldId, $value);
    }

    /**
     * Function to get single select field.
     *
     * @param string $callbackRoute    To store the call back route for ajax.
     * @param string $fieldId          To store the id of the field.
     * @param string $value            To store the value of the field.
     * @param string $callBackFunction To store the call back function for ajax.
     *
     * @return \App\Frame\Gui\Html\Fields\SingleSelectTable
     */
    public function getSingleSelectTable(string $callbackRoute, string $fieldId, $value, string $callBackFunction = 'loadSingleSelectData'): SingleSelectTable
    {
        $oldValue = $this->Validation->getOldValue($fieldId);
        if (empty($oldValue) === false || $oldValue === '0') {
            $value = $oldValue;
        }

        return new SingleSelectTable($callbackRoute, $callBackFunction, $fieldId, $value);
    }

    /**
     * Function to get text area field.
     *
     * @param string $fieldId      To store the id of the field.
     * @param string $value        To store the value of the field.
     * @param int    $numberOfRows To store the total rows for the text area.
     * @param int    $numberOfCols To store the total cols for the text area.
     *
     * @return \App\Frame\Gui\Html\Fields\TextArea
     */
    public function getTextArea(string $fieldId, $value, $numberOfRows = 3, $numberOfCols = 8): TextArea
    {
        $oldValue = $this->Validation->getOldValue($fieldId);
        if (empty($oldValue) === false || $oldValue === '0') {
            $value = $oldValue;
        }

        return new TextArea($fieldId, $value, $numberOfRows, $numberOfCols);
    }


    /**
     * Function to get text area field.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     * @param bool   $checked To store the check attribute.
     *
     * @return \App\Frame\Gui\Html\Fields\Checkbox
     */
    public function getCheckBox(string $fieldId, $value, bool $checked = false): Checkbox
    {
        $oldValue = $this->Validation->getOldValue($fieldId);
        if (empty($oldValue) === false || $oldValue === '0') {
            $value = $oldValue;
        }

        return new Checkbox($fieldId, $value, $checked);
    }

    /**
     * Function to get file field.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     *
     * @return \App\Frame\Gui\Html\Fields\File
     */
    public function getFile(string $fieldId, $value): File
    {
        return new File($fieldId, $value);
    }

    /**
     * Function to get calendar field.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     *
     * @return \App\Frame\Gui\Html\Fields\Calendar
     */
    public function getCalendar(string $fieldId, $value): Calendar
    {
        $oldValue = $this->Validation->getOldValue($fieldId);
        if (empty($oldValue) === false && $oldValue !== null && $oldValue !== '') {
            $value = $oldValue;
        }

        return new Calendar($fieldId, $value);
    }

    /**
     * Function to get time field.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     *
     * @return \App\Frame\Gui\Html\Fields\Time
     */
    public function getTime(string $fieldId, $value): Time
    {
        $oldValue = $this->Validation->getOldValue($fieldId);
        if (empty($oldValue) === false && $oldValue !== null && $oldValue !== '') {
            $value = $oldValue;
        }

        return new Time($fieldId, $value);
    }

    /**
     * Function to get radio field.
     *
     * @param string  $fieldId To store the id of the field.
     * @param string  $value   The value to be given on the radio.
     * @param boolean $checked When the radio button must be checked.
     *
     * @return \App\Frame\Gui\Html\Fields\Radio
     */
    public function getRadio(string $fieldId, $value, $checked = false): Radio
    {
        $oldValue = $this->Validation->getOldValue($fieldId);
        if (empty($oldValue) === false && $oldValue !== null && $oldValue !== '') {
            $value = $oldValue;
        }

        return new Radio($fieldId, $value, $checked);
    }


    /**
     * Function to get text field.
     *
     * @param string $fieldId To store the id of the field.
     * @param string $value   To store the value of the field.
     *
     * @return \App\Frame\Gui\Html\Fields\Password
     */
    public function getPassword(string $fieldId, $value = ''): Password
    {

        $oldValue = $this->Validation->getOldValue($fieldId);
        if (empty($oldValue) === false || $oldValue === '0') {
            $value = $oldValue;
        }

        return new Password($fieldId, $value);
    }

    /**
     * Function to get check box group field.
     *
     * @param string $fieldId To store the id of the field.
     *
     * @return CheckBoxGroup
     */
    public function getCheckBoxGroup(string $fieldId): CheckBoxGroup
    {
        $oldValue = $this->Validation->getOldValue($fieldId);
        $values = [];
        $oldValueExist = false;
        if (is_array($oldValue) === true) {
            $values = $oldValue;
            $oldValueExist = true;
        }

        return new CheckBoxGroup($fieldId, $values, $oldValueExist);
    }
}
