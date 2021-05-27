<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Frame\Formatter;

use App\Frame\Exceptions\Message;

/**
 * Class helper to generate sql query.
 *
 * @package    app
 * @subpackage Frame\Formatter
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class SqlHelper
{

    /**
     * List of all available operator for sql conditions.
     *
     * @var array $OperatoreList
     */
    private static $OperatorList = ['=', '>', '<', '>=', '<=', '<>'];

    /**
     * Function to load data from database.
     *
     * @param string $columnName to store the query selection.
     * @param ?string $value to store the query selection.
     * @param string $operator to store the query selection.
     *
     * @return string
     */
    public static function generateStringCondition(string $columnName, ?string $value, string $operator = '='): string
    {
        if (in_array($operator, self::$OperatorList, true) === false) {
            Message::throwMessage('Invalid operator (' . $operator . ') for generating sql string conditions.');
        }
        return '(' . $columnName . ' ' . $operator . ' \'' . $value . '\')';
    }

    /**
     * Function to generate lower string condition.
     *
     * @param string $columnName to store the query selection.
     * @param ?string $value to store the query selection.
     * @param string $operator to store the query selection.
     *
     * @return string
     */
    public static function generateLowerStringCondition(string $columnName, ?string $value, string $operator = '='): string
    {
        return self::generateStringCondition('LOWER(' . $columnName . ')', mb_strtolower($value), $operator);
    }

    /**
     * Function to generate upper string condition.
     *
     * @param string $columnName to store the query selection.
     * @param ?string $value to store the query selection.
     * @param string $operator to store the query selection.
     *
     * @return string
     */
    public static function generateUpperStringCondition(string $columnName, ?string $value, string $operator = '='): string
    {
        return self::generateStringCondition('(UPPER(' . $columnName . ')', mb_strtoupper($value), $operator);
    }

    /**
     * Function to load data from database.
     *
     * @param string $columnName to store the query selection.
     * @param ?int|?float $value to store the query selection.
     * @param string $operator to store the query selection.
     *
     * @return string
     */
    public static function generateNumericCondition(string $columnName, $value, string $operator = '='): string
    {
        if (in_array($operator, self::$OperatorList, true) === false) {
            Message::throwMessage('Invalid operator (' . $operator . ') for generating sql numeric conditions.');
        }
        return '(' . $columnName . ' ' . $operator . ' ' . $value . ')';
    }


    /**
     * Function to generate like query condition for sql
     *
     * @param string $columnName to store the query selection.
     * @param ?string $value to store the query selection.
     * @param string $matchingType to store the query selection
     *                             C => Contains
     *                             S => Start With
     *                             E => End With.
     *
     * @return string
     */
    public static function generateLikeCondition(string $columnName, ?string $value, string $matchingType = 'C'): string
    {
        $string = '';
        if ($value !== null & is_string($value)) {
            $string = mb_strtolower(trim($value));
        }
        $matchingType = mb_strtolower($matchingType);
        if ($matchingType === 's') {
            $like = '\'' . $string . '%\'';
        } elseif ($matchingType === 'e') {
            $like = '\'%' . $string . '\'';
        } else {
            $like = '\'%' . $string . '%\'';
        }
        return '(LOWER(' . $columnName . ') like ' . $like . ')';
    }


    /**
     * Function to generate OrLike Condition for sql query.
     *
     * @param ?string $value to store the query selection.
     * @param array $columnNames to store the query selection.
     * @param string $matchingType to store the query selection
     *                             C => Contains
     *                             S => Start With
     *                             E => End With.
     *
     * @return string
     */
    public static function generateOrLikeCondition(array $columnNames, ?string $value, string $matchingType = 'c'): string
    {
        if (empty($columnNames) === true) {
            Message::throwMessage('Invalid columns name for generating or like condition.');
        }

        $orWheres = [];
        foreach ($columnNames as $column) {
            $orWheres[] = self::generateLikeCondition($column, $value, $matchingType);
        }
        return '(' . implode(' OR ', $orWheres) . ')';
    }


    /**
     * Function to load data from database.
     *
     * @param string $columnName to store the query selection.
     * @param bool $isNull to store null selection.
     *
     * @return string
     */
    public static function generateNullCondition(string $columnName, bool $isNull = true): string
    {
        if ($isNull === true) {
            return '(' . $columnName . ' IS NULL)';
        }
        return '(' . $columnName . ' IS NOT NULL)';
    }

}
