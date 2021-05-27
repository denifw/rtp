<?php
/**
 * Created by PhpStorm.
 * User: nosurino
 * Date: 2/20/2017
 * Time: 7:52 PM
 */

namespace App\Frame\Gui\Html\Fields;


use App\Frame\Exceptions\Message;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\SingleSelectInterface;
use App\Frame\Gui\Icon;

class SingleSelect extends Text implements SingleSelectInterface
{
    /**
     * Property to store the name of the callback method.
     *
     * @var string $CallBackMethod The name of the method.
     */
    private $CallBackMethod;
    /**
     * Property to store the name of the callback class.
     *
     * @var string $CallBackRoute The name of the class.
     */
    private $CallBackRoute;

    /**
     * The html id from the auto suggest text field.
     *
     * @var string $Id
     */
    private $FieldId;

    /**
     * Property used to store the hidden field values.
     *
     * @var \App\Frame\Gui\Html\Fields\Hidden $HiddenField
     */
    private $HiddenField;

    /**
     * Property to store the list parameters
     *
     * @var array $Parameters ;
     */
    private $Parameters = [];

    /**
     * Property to store the list parameter by field id.
     *
     * @var array $ParameterByIds ;
     */
    private $ParameterByIds = [];

    /**
     * Property to store the list parameter by field id.
     *
     * @var array $OptionalParameterByIds ;
     */
    private $OptionalParameterByIds = [];

    /**
     * Property to store the list label parameter.
     *
     * @var array $ParameterLabels ;
     */
    private $ParameterLabels = [];

    /**
     * Property to store all the field that will be reset if the single select field is cleared.
     *
     * @var array $OnClearFields ;
     */
    private $OnClearFields = [];
    /**
     * Property to store the detail reference code.
     *
     * @var array $DetailReferenceCode ;
     */
    private $DetailReferenceCode = '';
    /**
     * Property to store the trigger to enable new button.
     *
     * @var bool $EnableNewBtn ;
     */
    private $EnableNewBtn = true;
    /**
     * Property to store the trigger to enable detail button.
     *
     * @var bool $EnableDetailBtn ;
     */
    private $EnableDetailBtn = true;
    /**
     * Property to store the trigger to enable Delete button.
     *
     * @var bool $EnableDeleteBtn ;
     */
    private $EnableDeleteBtn = true;

    /**
     * Property to know if the value inside single select can be modified or not.
     *
     * @var bool $ReadOnly The value of modifiable setting
     */
    private $ReadOnly = false;

    /**
     * Property to store auto complete field.
     *
     * @var $AutoCompleteFields
     */
    private $AutoCompleteFields = [];

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $callbackRoute  The name of the callback calss.
     * @param string $callBackMethod The name of the callback method.
     * @param string $fieldId        The unique id from the text field.
     * @param string $value          The value from the text field.
     */
    public function __construct(string $callbackRoute, string $callBackMethod, string $fieldId, $value)
    {
        parent::__construct($fieldId, $value);
        $this->addAttribute('class', 'form-control input-sm');
        $this->CallBackMethod = $callBackMethod;
        $this->CallBackRoute = '/' . $callbackRoute;
        $this->FieldId = $fieldId;
    }

    /**
     * Set modified able single select.
     *
     * @param boolean $readOnly The value to be set inside it.
     *
     * @return void
     */
    public function setReadOnly(bool $readOnly = true): void
    {
        $this->ReadOnly = $readOnly;
        parent::setReadOnly($readOnly);
    }

    /**
     * Add extra hidden field parameter.
     *
     * @param string $fieldId The unique id used in html.
     * @param string $value   The value that the field will contain.
     *
     * @return void
     */
    public function setHiddenField($fieldId, $value = ''): void
    {
        $this->HiddenField = new Hidden($fieldId, $value);
    }

    /**
     * Add extra hidden field parameter.
     *
     * @param string $value The value that the field will contain.
     *
     * @return void
     */
    public function setHiddenFieldValue($value = ''): void
    {
        $this->HiddenField->addAttribute('value', $value);
    }

    /**
     * Add extra hidden field parameter.
     *
     * @return string
     */
    public function getHiddenFieldId(): string
    {
        return $this->HiddenField->getAttribute('id');
    }

    /**
     * Function to set add parameter.
     *
     * @param string $parId    The unique id used in html.
     * @param string $parValue The value that the field will contain.
     *
     * @return void
     */
    public function addParameter($parId, $parValue = ''): void
    {
        if (empty($parId) === false) {
            if (array_key_exists($parId, $this->Parameters) === false) {
                $this->Parameters[$parId] = $parValue;
            } else {
                Message::throwMessage('Duplicate parameter id for single select with id = ' . $parId . '.');
            }
        } else {
            Message::throwMessage('Invalid empty parameter id for single select.');
        }
    }

    /**
     * Function to set add parameter by field id.
     *
     * @param string $parId    The unique id used in html.
     * @param string $fieldId  The value that the field will contain.
     * @param string $parLabel The label for parameter.
     *
     * @return void
     */
    public function addParameterById($parId, $fieldId, $parLabel): void
    {
        if (empty($parId) === false && empty($fieldId) === false) {
            if (array_key_exists($parId, $this->ParameterByIds) === false) {
                $this->ParameterByIds[$parId] = $fieldId;
                $this->ParameterLabels[$parId] = $parLabel;
            } else {
                Message::throwMessage('Duplicate parameter id for single select with id = ' . $parId . '.');
            }
        } else {
            Message::throwMessage('Invalid parameter by field id for single select.');
        }
    }

    /**
     * Function to set add optional parameter by field id.
     *
     * @param string $parId   The unique id used in html.
     * @param string $fieldId The value that the field will contain.
     *
     * @return void
     */
    public function addOptionalParameterById($parId, $fieldId): void
    {
        if (empty($parId) === false && empty($fieldId) === false) {
            if (array_key_exists($parId, $this->ParameterByIds) === false) {
                $this->ParameterByIds[$parId] = $fieldId;
                $this->OptionalParameterByIds[$parId] = $fieldId;

            } else {
                Message::throwMessage('Duplicate parameter id for single select with id = ' . $parId . '.');
            }
        } else {
            Message::throwMessage('Invalid parameter by field id for single select.');
        }
    }

    /**
     * Function to set add clear field when we clear the single select.
     *
     * @param string $fieldId The value that the field will contain.
     *
     * @return void
     */
    public function addClearField($fieldId): void
    {
        if (\in_array($fieldId, $this->OnClearFields, true) === false) {
            $this->OnClearFields[] = $fieldId;
        }
    }

    /**
     * Function to set the detail reference.
     *
     * @param string $referenceCode The value of path for the detail reference code.
     *
     * @return void
     */
    public function setDetailReferenceCode($referenceCode): void
    {
        $this->DetailReferenceCode = $referenceCode;
    }


    /**
     * function Set enable new button.
     *
     * @param boolean $enable The value to set enable the button.
     *
     * @return void
     */
    public function setEnableNewButton(bool $enable = true): void
    {
        $this->EnableNewBtn = $enable;
    }

    /**
     * function Set enable detail button.
     *
     * @param boolean $enable The value to set enable the button.
     *
     * @return void
     */
    public function setEnableDetailButton(bool $enable = true): void
    {
        $this->EnableDetailBtn = $enable;
    }

    /**
     * function to set auto complete field.
     *
     * @param array $fields The list field.
     *
     * @return void
     */
    public function setAutoCompleteFields(array $fields): void
    {
        $this->AutoCompleteFields = $fields;
    }

    /**
     * function Set enable delete button.
     *
     * @param boolean $enable The value to set enable the button.
     *
     * @return void
     */
    public function setEnableDeleteButton(bool $enable = true): void
    {
        $this->EnableDeleteBtn = $enable;
    }

    /**
     * Magic method to convert the main property field into a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->createSingleSelect();
    }

    /**
     * Crate the complete auto suggest field.
     *
     * @return string
     */
    private function createSingleSelect(): string
    {
        $result = '';
        if ($this->HiddenField === null) {
            Message::throwMessage('Missing Hidden field for SingleSelect: ' . $this->FieldId);
        }
        $result .= '<div class="input-group input-group-sm">';
        $result .= parent::__toString();
        $result .= $this->HiddenField;
        if ($this->ReadOnly === true) {
            $this->setEnableDeleteButton(false);
            $this->setEnableNewButton(false);
        }
        $result .= $this->loadButtons();
        $result .= $this->getJavascript();

        $result .= '</div>';

        return $result;
    }

    /**
     * Crate the complete auto suggest field.
     *
     * @return string
     */
    private function loadButtons(): string
    {
        $btnNew = new Button($this->FieldId . '_new_btn', '', 'button');
        $btnNew->btnDefault();
        if ($this->EnableNewBtn === false) {
            $btnNew->addAttribute('style', 'color: transparent');
        }
        $btnNew->setIcon(Icon::FileO);
        $btnDetail = new Button($this->FieldId . '_detail_btn', '', 'button');
        $btnDetail->setIcon(Icon::ExternalLink)->btnPrimary();
        $btnDelete = new Button($this->FieldId . '_delete_btn', '', 'button');
        $btnDelete->setIcon(Icon::Remove)->btnDanger();
        $result = '<div class="input-group-btn">';
        $result .= $btnNew;
        $result .= $btnDetail;
        $result .= $btnDelete;
        $result .= '</div>';

        return $result;
    }

    /**
     * Returns a string if the javascript must be loaded.
     *
     * @return string
     */
    private function getJavascript(): string
    {
        $varJs = $this->FieldId . 'SingleSelect';
        $javascript = '<script type="text/javascript">';
        $javascript .= 'var ' . $varJs . " = new App.SingleSelect('" . $this->FieldId . "', '" . $this->HiddenField->getAttribute('id') . "');";
        if ($this->ReadOnly === true) {
            $javascript .= $varJs . '.setReadOnly(true);';
        }
        if ($this->EnableNewBtn === false) {
            $javascript .= $varJs . '.disableNewBtn();';
        }
        if ($this->EnableDetailBtn === false) {
            $javascript .= $varJs . '.disableDetailBtn();';
        }
        if ($this->EnableDeleteBtn === false) {
            $javascript .= $varJs . '.disableDeleteBtn();';
        }
        $javascript .= $varJs . ".setCallBackRoute('" . $this->CallBackRoute . "');";
        $javascript .= $varJs . ".setCallBackFunction('" . $this->CallBackMethod . "');";
        $javascript .= $varJs . ".setDetailReferenceCode('" . $this->DetailReferenceCode . "');";

        foreach ($this->Parameters as $key => $value) {
            $javascript .= $varJs . ".addCallBackParameter('" . $key . "', '" . $value . "');";
        }
        foreach ($this->ParameterByIds as $key => $value) {
            $javascript .= $varJs . ".addCallBackParameterById('" . $key . "', '" . $value . "');";
        }
        foreach ($this->OptionalParameterByIds as $key => $value) {
            $javascript .= $varJs . ".addOptionalCallBackParameterById('" . $key . "', '" . $value . "');";
        }
        foreach ($this->ParameterLabels as $key => $value) {
            $javascript .= $varJs . ".addParameterLabel('" . $key . "', '" . $value . "');";
        }
        foreach ($this->OnClearFields as $field) {
            $javascript .= $varJs . ".addFieldOnClear('" . $field . "');";
        }
        foreach ($this->AutoCompleteFields as $key => $value) {
            $javascript .= $varJs . ".addAutoCompleteField('" . $key . "', '" . $value . "');";
        }
        $javascript .= $varJs . '.createSingleSelect();';
        $javascript .= '</script>';

        return $javascript;
    }
}
