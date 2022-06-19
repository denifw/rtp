<?php
/**
 * Contains code written by the MBS Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   MBS
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Mvc;

use App\Frame\Gui\Html\Field;
use App\Frame\Gui\Html\FieldsInterface;

/**
 * Class to control  listing sort.
 *
 * @package    app
 * @subpackage Frame\Mvc
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class ListingSort
{
    /**
     * The listing option for sorting field.
     *
     * @var array $Options
     */
    private $Options = [];
    /**
     * The listing id for option sort.
     *
     * @var array $OptionIds
     */
    private $OptionIds = [];
    /**
     * The selected option.
     *
     * @var string $SelectedField
     */
    private $SelectedField = '';
    /**
     * The selected option.
     *
     * @var string $SortId To set the id field for the selection.
     */
    private $SortId = 'sort_by';


    /**
     * Attribute for the field object.
     *
     * @var \App\Frame\Gui\Html\Field $Field
     */
    private $Field;

    /**
     * Listing sort constructor.
     *
     * @param Field $field To store field object.
     */
    public function __construct(Field $field)
    {
        $this->Field = $field;
    }


    /**
     * Function to add string option for the sorting field.
     *
     * @param string $columnName To store the value of the option.
     * @param string $text       To store the Text of the option.
     *
     * @return void
     */
    public function addOption(string $columnName, string $text): void
    {
        if (array_key_exists($columnName, $this->OptionIds) === false) {
            $this->Options[$columnName] = [
                'id' => $columnName,
                'text' => $text,
            ];
            $this->OptionIds[] = $columnName;
        }
    }

    /**
     * Function to get the option of the sorting field.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->Options;
    }

    /**
     * Function to set the selected field.
     *
     * @param string $columnName To store the value of the option.
     *
     * @return void
     */
    public function setSelectedField(string $columnName): void
    {
        $this->SelectedField = $columnName;
    }

    /**
     * Function to set the selected field.
     *
     * @return string
     */
    public function getSelectedField(): string
    {
        return $this->SelectedField;
    }

    /**
     * Function to set the selected field.
     *
     * @return string
     */
    public function getOrderByQuery(): string
    {
        $result = '';
        if (array_key_exists($this->SelectedField, $this->Options) === true) {
            $result = ' ORDER BY ' . $this->Options[$this->SelectedField]['id'];
        }

        return $result;
    }


    /**
     * Function to set the selected field.
     *
     * @return array
     */
    public function getOrderByFields(): array
    {
        $results = [];
        if (array_key_exists($this->SelectedField, $this->Options) === true) {
            $results[] = $this->Options[$this->SelectedField]['id'];
        }

        return $results;
    }

    /**
     * Function to get the sort id.
     *
     * @return string
     */
    public function getSortId(): string
    {
        return $this->SortId;
    }

    /**
     * Function to check is there any sorting field.
     *
     * @return bool
     */
    public function isExist(): bool
    {
        return !empty($this->OptionIds);
    }

    /**
     * Function to get sorting field.
     *
     * @return FieldsInterface
     */
    public function getSortingField(): FieldsInterface
    {
        $field = $this->Field->getSelect($this->SortId, $this->SelectedField);
        foreach ($this->OptionIds as $key) {
            $field->addOption($this->Options[$key]['text'], $this->Options[$key]['id']);
        }
        $field->setPleaseSelect();

        return $field;
    }

    /**
     * Function to set the selected field.
     *
     * @return string
     */
    public function getOrderByFieldsString(): string
    {
        $fields = $this->getOrderByFields();
        if (empty($fields) === false) {
            return implode(', ', $fields);
        }
        return '';
    }
}
