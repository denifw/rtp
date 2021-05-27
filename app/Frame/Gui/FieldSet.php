<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Gui;

use App\Frame\Exceptions\Message;
use App\Frame\Gui\Html\Fields\SingleSelect;
use App\Frame\Gui\Html\Fields\SingleSelectTable;
use App\Frame\Gui\Html\FieldsInterface;
use App\Frame\Gui\Html\SingleSelectInterface;
use App\Frame\System\Validation;

/**
 * Class to generate field base on the table set.
 *
 * @package    app
 * @subpackage Util\Gui
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2017 C-Book
 */
class FieldSet
{
    /**
     * Attribute to store all the html field.
     *
     * @var array $Fields
     */
    protected $Fields = [];

    /**
     * Attribute to store all the hidden field.
     *
     * @var array $HiddenFields
     */
    protected $HiddenFields = [];

    /**
     * Attribute to store all the html field id.
     *
     * @var array $FieldIds
     */
    protected $FieldIds = [];

    /**
     * Attribute to store all the require field id.
     *
     * @var array $RequireFieldIds
     */
    protected $RequireFieldIds = [];

    /**
     * Attribute to store number of column field.
     *
     * @var string $ColumnGridClass
     */
    protected $ColumnGridClass = 'form-group col-sm-6 col-md-4 col-lg-3 col-xs-12';

    /**
     * Property to store the right of the page.
     *
     * @var \App\Frame\System\Validation $Validation
     */
    protected $Validation;

    /**
     * Form constructor.
     *
     * @param \App\Frame\System\Validation $validation To set the validation handler form the form.
     */
    public function __construct(Validation $validation)
    {
        if ($validation !== null) {
            $this->Validation = $validation;
        } else {
            Message::throwMessage('Not allowed null value for the validation object inside the form.', 'DEBUG');
        }

    }

    /**
     * Function to set the number of column.
     *
     * @param integer $large      To set the grid amount for a large screen.
     * @param integer $medium     To set the grid amount for a medium screen.
     * @param integer $small      To set the grid amount for a small screen.
     * @param integer $extraSmall To set the grid amount for a extra small screen.
     *
     * @return void
     * @deprecated User setGridDimension instead.
     */
    public function setColumnWidth(int $large = 3, int $medium = 4, int $small = 6, $extraSmall = 12): void
    {
        $this->ColumnGridClass = 'form-group col-lg-' . $large . ' col-md-' . $medium . ' col-sm-' . $small . ' col-xs-' . $extraSmall;
    }

    /**
     * Function to set the number of column.
     *
     * @param integer $large      To set the grid amount for a large screen.
     * @param integer $medium     To set the grid amount for a medium screen.
     * @param integer $small      To set the grid amount for a small screen.
     * @param integer $extraSmall To set the grid amount for a extra small screen.
     *
     * @return void
     */
    public function setGridDimension(int $large = 3, int $medium = 4, int $small = 6, $extraSmall = 12): void
    {
        $this->ColumnGridClass = 'form-group col-lg-' . $large . ' col-md-' . $medium . ' col-sm-' . $small . ' col-xs-' . $extraSmall;
    }

    /**
     * Function to add field to the form.
     *
     * @param string                              $label      To store the label of the field.
     * @param \App\Frame\Gui\Html\FieldsInterface $field      To set the value of the attribute.
     * @param boolean                             $isRequired To set the required field.
     *
     * @return void
     */
    public function addField(string $label, FieldsInterface $field, bool $isRequired = false): void
    {
        if ($field !== null) {
            $fieldId = $field->getAttribute('id');
            if (array_key_exists($fieldId, $this->Fields) === false) {
                $this->Fields[$fieldId] = [
                    'field' => $field,
                    'label' => $label,
                ];
                $this->FieldIds[] = $fieldId;
                if ($isRequired === true) {
                    $this->RequireFieldIds[] = $fieldId;
                }
            } else {
                Message::throwMessage('Duplicate Field with id ' . $fieldId . ' inside the field set.', 'DEBUG');
            }
        }
    }

    /**
     * Function to add field to the form.
     *
     * @param string                              $afterFieldId To store the field id.
     * @param string                              $label        To store the label of the field.
     * @param \App\Frame\Gui\Html\FieldsInterface $field        To set the value of the attribute.
     * @param boolean                             $isRequired   To set the required field.
     *
     * @return void
     */
    public function addFieldAfter(string $afterFieldId, string $label, FieldsInterface $field, bool $isRequired = false): void
    {
        if (array_key_exists($afterFieldId, $this->Fields) === false) {
            Message::throwMessage('Not found field with id ' . $afterFieldId . ' in the form data.', 'DEBUG');
        }
        if ($field !== null) {
            $fieldId = $field->getAttribute('id');
            if (array_key_exists($fieldId, $this->Fields) === false) {
                $newFields = [];
                $this->FieldIds = [];
                foreach ($this->Fields as $key => $value) {
                    $newFields[$key] = $value;
                    $this->FieldIds[] = $key;
                    if ($key === $afterFieldId) {
                        $newFields[$fieldId] = [
                            'field' => $field,
                            'label' => $label,
                        ];
                        $this->FieldIds[] = $fieldId;
                    }
                }
                $this->Fields = $newFields;
                if ($isRequired === true) {
                    $this->RequireFieldIds[] = $fieldId;
                }
            } else {
                Message::throwMessage('Duplicate Field with id ' . $fieldId . ' inside the field set.', 'DEBUG');
            }
        }
    }

    /**
     * Function to remove field from the form.
     *
     * @param string $fieldId To set the id of the field.
     *
     * @return void
     */
    public function removeField(string $fieldId): void
    {
        if (array_key_exists($fieldId, $this->Fields) === true) {
            unset($this->Fields[$fieldId]);
            $key = array_search($fieldId, $this->FieldIds, true);
            unset($this->FieldIds[$key]);
            # Reset the field ids to the correct values
            $this->FieldIds = array_values($this->FieldIds);
        } else {
            Message::throwMessage('Not found field with id ' . $fieldId . ' in the form data.', 'DEBUG');
        }
    }

    /**
     * Function to add hidden field to the form.
     *
     * @param \App\Frame\Gui\Html\FieldsInterface $field To set the value of the attribute.
     *
     * @return void
     */
    public function addHiddenField(FieldsInterface $field): void
    {
        if ($field !== null) {
            $fieldId = $field->getAttribute('id');
            if (array_key_exists($fieldId, $this->HiddenFields) === false) {
                $this->HiddenFields[$fieldId] = $field;
            } else {
                Message::throwMessage('Duplicate Field with id ' . $fieldId . ' inside the field set.', 'DEBUG');
            }
        }
    }

    /**
     * Function to set required fields.
     *
     * @param array $fieldIds To set the id of the required field.
     *
     * @return void
     */
    public function setRequiredFields(array $fieldIds): void
    {
        if (empty($fieldIds) === false) {
            $this->RequireFieldIds = array_merge($this->RequireFieldIds, $fieldIds);
        }
    }

    /**
     * Function to add hidden field to the form.
     *
     * @param array $orderedFieldIds To set the ordered field id.
     *
     * @return void
     */
    public function doReorderField(array $orderedFieldIds): void
    {
        if (empty($orderedFieldIds) === false) {
            $diff = array_diff($orderedFieldIds, $this->FieldIds);
            if (empty($diff) === false) {
                Message::throwMessage('Undefined field with id ' . implode(', ', $diff), 'DEBUG');
            } else {
                $this->FieldIds = $orderedFieldIds;
            }
        }
    }

    /**
     * Returns the converted fieldset as a string if we store it to view.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->createFieldSet();
    }

    /**
     * Function to generate the html field set.
     *
     * @return string
     */
    public function createFieldSet(): string
    {
        $result = '';
        $result .= $this->loadHiddenField();
        $result .= $this->loadField();

        return $result;
    }

    /**
     * Function to generate hidden fields.
     *
     * @return string
     */
    private function loadHiddenField(): string
    {
        $result = '';
        if (empty($this->HiddenFields) === false) {
            foreach ($this->HiddenFields as $key => $field) {
                $result .= $field;
            }
        }

        return $result;
    }

    /**
     * Function to generate the fields.
     *
     * @return string
     */
    protected function loadField(): string
    {
        $result = '';
        if (empty($this->FieldIds) === false) {
            foreach ($this->FieldIds as $fieldId) {
                if (array_key_exists($fieldId, $this->Fields) === true) {
                    # Check the validation field.
                    $field = $this->Fields[$fieldId];
                    $tempId = $fieldId;
                    $errorClass = '';
                    $errorMessage = '';
                    $isValidField = $this->Validation->isValid($tempId);
                    $fieldObject = $field['field'];
                    if ($isValidField === true && ($fieldObject instanceof SingleSelectInterface)) {
                        $tempId = $fieldObject->getHiddenFieldId();
                        $oldValue = $this->Validation->getOldValue($tempId);
                        if (empty($oldValue) === false || $oldValue === '0') {
                            $fieldObject->setHiddenFieldValue($oldValue);
                        }
                        $isValidField = $this->Validation->isValid($tempId);
                    }
                    if ($isValidField === false) {
                        $errorClass = 'bad';
                        $errorMessage = '<span class="input-alert">' . $this->Validation->getErrorMessage($tempId, $field['label']) . '</span>';
                    }
                    # Create the field.
                    $requireIcon = '';
                    if (\in_array($fieldId, $this->RequireFieldIds, true) === true) {
                        $requireIcon = ' <span class="require-flag">*</span>';
                    }
                    $result .= '<div class="' . $this->ColumnGridClass . ' ' . $errorClass . '">';
                    $result .= '<label class="control-label" for="' . $fieldId . '">';
                    $result .= $field['label'] . $requireIcon;
                    $result .= '</label>';
                    $result .= $field['field'];
                    $result .= $errorMessage;
                    $result .= '</div>';
                }
            }
        }

        return $result;
    }

    /**
     * Function to check if there is a field inside the field set.
     *
     * @return bool
     */
    public function isFieldsExist(): bool
    {
        return !empty($this->FieldIds);
    }

}
