<?php
/**
 * Created by PhpStorm.
 * User: nosurino
 * Date: 2/20/2017
 * Time: 7:52 PM
 */

namespace App\Frame\Gui\Html\Fields;


use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Field;
use App\Frame\Gui\Html\SingleSelectInterface;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\System\Validation;

class SingleSelectTable extends Text implements SingleSelectInterface
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
     * Property to store the filter field
     *
     * @var array $Filters ;
     */
    private $Filters = [];

    /**
     * Property to store the table columns.
     *
     * @var array $Columns ;
     */
    private $Columns = [];

    /**
     * Property to store the auto complete fields
     *
     * @var array $AutoCompleteFields ;
     */
    private $AutoCompleteFields = [];

    /**
     * Property to store the filter field
     *
     * @var array $FilterIds ;
     */
    private $FilterIds = [];

    /**
     * Property to store the table columns.
     *
     * @var array $ColumnIds ;
     */
    private $ColumnIds = [];

    /**
     * Property to store the detail reference code.
     *
     * @var array $ValueCode ;
     */
    private $ValueCode = '';

    /**
     * Property to store the detail reference code.
     *
     * @var array $LabelCode ;
     */
    private $LabelCode = '';

    /**
     * Property to store the trigger to enable Delete button.
     *
     * @var bool $EnableModal ;
     */
    private $EnableModal = true;

    /**
     * Property to store the trigger to enable Delete button.
     *
     * @var string $ParentModalId ;
     */
    private $ParentModalId = '';

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
     * @param string $columnName The value of path for the detail reference code.
     *
     * @return void
     */
    public function setValueCode($columnName): void
    {
        $this->ValueCode = $columnName;
    }

    /**
     * Function to set the detail reference.
     *
     * @param string $columnName The value of path for the detail reference code.
     *
     * @return void
     */
    public function setLabelCode($columnName): void
    {
        $this->LabelCode = $columnName;
    }

    /**
     * Function to set filters
     *
     * @param array $keyValues The key value for filters.
     *
     * @return void
     */
    public function setAutoCompleteFields(array $keyValues): void
    {
        $this->AutoCompleteFields = $keyValues;
    }

    /**
     * Function to set filters
     *
     * @param array $keyValues The key value for filters.
     *
     * @return void
     */
    public function setFilters(array $keyValues): void
    {
        $this->Filters = $keyValues;
    }

    /**
     * Function to set table columns
     *
     * @param array $keyValues The key value for filters.
     *
     * @return void
     */
    public function setTableColumns(array $keyValues): void
    {
        $this->Columns = $keyValues;
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
     * function Set disable modal view.
     *
     * @param boolean $disable The value to set enable the button.
     *
     * @return void
     */
    public function setDisableModal(bool $disable = true): void
    {
        $this->EnableModal = !$disable;
    }

    /**
     * function Set disable modal view.
     *
     * @param string  $parentModalId To store the id of parent modal.
     * @param boolean $disableModal  The value to set enable the button.
     *
     * @return void
     */
    public function setParentModal(string $parentModalId, bool $disableModal = true): void
    {
        $this->ParentModalId = $parentModalId;
        $this->EnableModal = !$disableModal;
    }

    /**
     * Magic method to convert the main property field into a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->createHtml();
    }

    /**
     * Crate the complete auto suggest field.
     *
     * @return string
     */
    private function createHtml(): string
    {
        $result = '';
        if ($this->HiddenField === null) {
            Message::throwMessage('Missing Hidden field for Single Select Table: ' . $this->FieldId);
        }
        if (empty($this->Columns) === true) {
            Message::throwMessage('Missing table columns for Single Select Table: ' . $this->FieldId);
        }
        if (empty($this->ValueCode) === true) {
            Message::throwMessage('Missing value code for Single Select Table: ' . $this->FieldId);
        }
        if (empty($this->LabelCode) === true) {
            Message::throwMessage('Missing label code for Single Select Table: ' . $this->FieldId);
        }
        $result .= '<div class="input-group input-group-sm">';
        $result .= parent::__toString();
        $result .= $this->HiddenField;
        $result .= $this->loadButtons();

        $result .= '</div>';
        if ($this->EnableModal === true) {
            $result .= $this->getModal()->createModal();
        }
        $result .= $this->getJavascript();

        return $result;
    }

    /**
     * Crate the complete auto suggest field.
     *
     * @return \App\Frame\Gui\Modal
     */
    public function getModal(): Modal
    {
        $mdl = new Modal($this->FieldId . '_mdl', Trans::getWord('search' ));
        if (empty($this->Filters) === false) {
            $fieldSet = new FieldSet(new Validation());
            $fieldSet->setGridDimension();
            $field = new Field(new Validation());
            foreach ($this->Filters as $id => $label) {
                $fieldSet->addField($label, $field->getText($id . '_' . $this->FieldId));
                $this->FilterIds[] = $id;
            }
            $mdl->addFieldSet($fieldSet);
        }
        $tbl = new Table($this->FieldId . '_tbl');
        $tbl->setAllowEmpty();
        $tbl->setHeaderRow($this->Columns);
        $this->ColumnIds = $tbl->getColumnIds();
        $tbl->addColumnAtTheEnd($this->FieldId . '_select', Trans::getWord('select' ));
        $tbl->addColumnAttribute($this->FieldId . '_select', 'style', 'text-align: center');
        $mdl->addTable($tbl);
        $mdl->setDisableBtnOk();
        $mdl->setDisableJavascript();

        return $mdl;
    }


    /**
     * Crate the complete auto suggest field.
     *
     * @return string
     */
    private function loadButtons(): string
    {
        $btnDelete = new Button($this->FieldId . '_delete_btn', '', 'button');
        $btnDelete->setIcon(Icon::Remove)->btnDanger();
        $btnSearch = new Button($this->FieldId . '_src_btn', '', 'button');
        $btnSearch->setIcon(Icon::Search)->btnPrimary();
        $result = '<div class="input-group-btn">';
        $result .= $btnSearch;
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
        $varJs = $this->FieldId . 'Sst';
        $javascript = '<script type="text/javascript">';
        $javascript .= 'var ' . $varJs . " = new App.SingleSelectTable('" . $this->FieldId . "', '" . $this->HiddenField->getAttribute('id') . "');";
        if ($this->ReadOnly === true) {
            $javascript .= $varJs . '.setReadOnly(true);';
        }
        $javascript .= $varJs . ".setCallBackRoute('" . $this->CallBackRoute . "');";
        $javascript .= $varJs . ".setCallBackFunction('" . $this->CallBackMethod . "');";
        $javascript .= $varJs . ".setValueCode('" . $this->ValueCode . "');";
        $javascript .= $varJs . ".setLabelCode('" . $this->LabelCode . "');";
        if (empty($this->ParentModalId) === false) {
            $javascript .= $varJs . ".setParentModal('" . $this->ParentModalId . "');";
        }
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
        foreach ($this->AutoCompleteFields as $key => $value) {
            $javascript .= $varJs . ".addAutoCompleteField('" . $key . "', '" . $value . "');";
        }
        foreach ($this->OnClearFields as $field) {
            $javascript .= $varJs . ".addFieldOnClear('" . $field . "');";
        }
        foreach ($this->FilterIds as $id) {
            $javascript .= $varJs . ".addFilterField('" . $id . "', '" . $id . '_' . $this->FieldId . "');";
        }
        foreach ($this->ColumnIds as $id) {
            $javascript .= $varJs . ".addTableColumn('" . $id . "', '" . $id . '_' . $this->FieldId . "');";
        }
        $javascript .= $varJs . '.createField();';
        $javascript .= '</script>';

        return $javascript;
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
}
