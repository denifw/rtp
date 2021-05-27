<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Mvc;

use App\Frame\Formatter\DataParser;
use Illuminate\Support\Facades\DB;

/**
 * Class to manage the model of the ajax.
 *
 * @package    app
 * @subpackage Model
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class AbstractBaseAjaxModel
{
    /**
     * Property to store the name space of the model.
     *
     * @var array $Parameters
     */
    private $Parameters = [];


    /**
     * Base model for ajax
     *
     * @param array $parameters .
     */
    public function __construct(array $parameters)
    {
        $this->setParameters($parameters);
    }

    /**
     * Function to set post value from the request.
     *
     * @param array $parameters To store the list input from request.
     *
     * @return void
     */
    public function setParameters(array $parameters): void
    {
        if (empty($parameters) === false) {
            $this->Parameters = array_merge($this->Parameters, $parameters);
        }
    }

    /**
     * Function to set parameter value from the request.
     *
     * @param string           $id    To store the parameter id
     * @param string|int|float $value To store the value of parameter
     *
     * @return void
     */
    public function setParameter(string $id, $value): void
    {
        if (empty($id) === false) {
            $this->Parameters[$id] = $value;
        }
    }


    /**
     * Function to get float parameter value.
     *
     * @param string $key     To store the key of the value
     * @param float  $default To store the default value if the parameter is empty
     *
     * @return null|float
     */
    public function getFloatParameter($key, $default = null): ?float
    {
        $result = $default;
        if (array_key_exists($key, $this->Parameters) === true && is_numeric($this->Parameters[$key]) === true) {
            $result = (float)$this->Parameters[$key];
        }

        return $result;
    }

    /**
     * Function to get parameter value.
     *
     * @param string  $key     To store the key of the value
     * @param integer $default To store the default value if the parameter is empty
     *
     * @return null|integer
     */
    public function getIntParameter($key, $default = null): ?int
    {

        $result = $default;
        if (array_key_exists($key, $this->Parameters) === true && is_numeric($this->Parameters[$key]) === true) {
            $result = (int)$this->Parameters[$key];
        }

        return $result;
    }

    /**
     * Function to get string parameter value.
     *
     * @param string $key     To store the key of the value
     * @param string $default To store the default value if the parameter is empty
     *
     * @return string
     */
    public function getStringParameter($key, $default = null): ?string
    {
        $result = $default;
        if (array_key_exists($key, $this->Parameters) === true && empty($this->Parameters[$key]) === false) {
            $result = $this->Parameters[$key];
        }

        return $result;
    }

    /**
     * Function to check is the parameter has value or not.
     *
     * @param string $key To store the key of the value
     *
     * @return bool
     */
    public function isValidParameter($key): bool
    {
        $result = false;
        if (array_key_exists($key, $this->Parameters) === true && empty($this->Parameters[$key]) === false) {
            $result = true;
        }

        return $result;
    }

    /**
     * Function to get all parameter.
     *
     * @return array
     */
    public function getAllParameters(): array
    {
        return $this->Parameters;
    }

    /**
     * Function to load data from database.
     *
     * @param string $query        To store the query selection.
     * @param string $textColumn   To store the column name that will be show as a text.
     * @param string $valueColumn  To store the column name that will be show as a value.
     * @param bool   $autoComplete To store the trigger if we need to provide more data except text and value.
     *
     * @return array
     */
    protected function loadDataForSingleSelect(string $query, $textColumn, $valueColumn, bool $autoComplete = true): array
    {
        $data = DB::select($query);
        if (empty($data) === false) {
            $fields = [];
            if ($autoComplete === false) {
                $fields = [$textColumn, $valueColumn];
            }
            $tempResult = DataParser::arrayObjectToArray($data, $fields);

            return $this->doPrepareSingleSelectData($tempResult, $textColumn, $valueColumn, $autoComplete);
        }

        # return the data.
        return [];
    }

    /**
     * Function to load data from database.
     *
     * @param array  $data         To store the query selection.
     * @param string $textColumn   To store the column name that will be show as a text.
     * @param string $valueColumn  To store the column name that will be show as a value.
     * @param bool   $autoComplete To store the trigger if we need to provide more data except text and value.
     *
     * @return array
     */
    protected function doPrepareSingleSelectData(array $data, $textColumn, $valueColumn, bool $autoComplete = false): array
    {
        $results = [];
        if ($autoComplete === false) {
            foreach ($data as $row) {
                $results[] = [
                    'text' => $row[$textColumn],
                    'value' => $row[$valueColumn],
                ];
            }
        } else {
            foreach ($data as $row) {
                $row['text'] = $row[$textColumn];
                $row['value'] = $row[$valueColumn];
                $results[] = $row;
            }
        }
        # return the data.
        return $results;
    }

}
