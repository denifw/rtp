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
     * Property to store the wheres condition.
     *
     * @var array $Wheres
     */
    private $Wheres;

    /**
     * Property to store the wheres condition.
     *
     * @var array $Wheres
     */
    private $OrWheres;

    /**
     * Property to store the orders condition.
     *
     * @var array $Orders
     */
    private $Orders;

    /**
     * Property to store the groups field.
     *
     * @var array $Groups
     */
    private $Groups;


    /**
     * Property to store the limit of sql statement.
     *
     * @var int $Limit
     */
    private $Limit;

    /**
     * Property to store the offset of sql statement.
     *
     * @var int $Offset
     */
    private $Offset;

    /**
     * Constructor for sql helper
     */
    public function __construct()
    {
        $this->Wheres = [];
        $this->OrWheres = [];
        $this->Orders = [];
        $this->Groups = [];
        $this->Limit = 0;
        $this->Offset = 0;
    }

    /**
     * Function to load data from database.
     *
     * @param string $columnName to store the query selection.
     * @param ?string $value to store the query selection.
     * @param string $operator to store the query selection.
     * @param string $convert to store the trigger to convert to lover or upper.
     *
     * @return string
     */
    public static function generateStringCondition(string $columnName, ?string $value, string $operator = '=', string $convert = ''): string
    {
        if (in_array($operator, self::$OperatorList, true) === false) {
            Message::throwMessage('Invalid operator (' . $operator . ') for generating sql string conditions.');
        }
        if (empty($convert) === false) {
            if (mb_strtolower($convert) === 'low') {
                return '(LOWER(' . $columnName . ') ' . $operator . ' \'' . mb_strtolower($value) . '\')';
            }
            if (mb_strtolower($convert) === 'up') {
                return '(UPPER(' . $columnName . ') ' . $operator . ' \'' . mb_strtoupper($value) . '\')';
            }
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

    /**
     * Function to load data from database.
     *
     * @param string $columnName to store the query selection.
     * @param ?string $value to store the query selection.
     * @param string $operator to store the query selection.
     * @param string $convert to store the trigger to convert to lover or upper.
     * @param string $orWheresId To Trigger the or where statement.
     *
     * @return void
     */
    public function addStringWhere(string $columnName, ?string $value, string $operator = '=', string $convert = '', string $orWheresId = ''): void
    {
        if ($value !== null) {
            if (in_array($operator, self::$OperatorList, true) === false) {
                Message::throwMessage('Invalid operator (' . $operator . ') for generating sql string conditions.');
            }
            if (empty($convert) === false) {
                $convert = mb_strtolower($convert);
            }
            if ($convert === 'low') {
                $where = '(LOWER(' . $columnName . ') ' . $operator . ' \'' . mb_strtolower($value) . '\')';
            } elseif ($convert === 'up') {
                $where = '(UPPER(' . $columnName . ') ' . $operator . ' \'' . mb_strtoupper($value) . '\')';
            } else {
                $where = '(' . $columnName . ' ' . $operator . ' \'' . $value . '\')';
            }
            if (empty($orWheresId) === false) {
                $this->setOrWhere($orWheresId, $where);
            } else {
                $this->Wheres[] = $where;
            }
        }
    }

    /**
     * Function to load data from database.
     *
     * @param string $columnName to store the query selection.
     * @param ?string $startDate to store the query selection.
     * @param ?string $endDate to store the query selection.
     * @param string $orWheresId To Trigger the or where statement.
     *
     * @return void
     */
    public function addRangeDateWhere(string $columnName, ?string $startDate, ?string $endDate, string $orWheresId = ''): void
    {
        $startWhere = '';
        $endWhere = '';
        if ($startDate !== null) {
            if ($endDate !== null) {
                $startWhere = "(" . $columnName . " >= '" . $startDate . "')";
            } else {
                $startWhere = "(" . $columnName . " = '" . $startDate . "')";
            }
        }
        if ($endDate !== null) {
            if ($startDate !== null) {
                $endWhere = "(" . $columnName . " <= '" . $endDate . "')";
            } else {
                $endWhere = "(" . $columnName . " = '" . $endDate . "')";
            }
        }
        if (empty($orWheresId) === false) {
            if (empty($startWhere) === false) {
                $this->setOrWhere($orWheresId, $startWhere);
            }
            if (empty($endWhere) === false) {
                $this->setOrWhere($orWheresId, $endWhere);
            }
        } else {
            if (empty($startWhere) === false) {
                $this->Wheres[] = $startWhere;
            }
            if (empty($endWhere) === false) {
                $this->Wheres[] = $endWhere;
            }
        }
    }

    /**
     * Function to load data from database.
     *
     * @param string $columnName to store the query selection.
     * @param ?string $startDate to store the query selection.
     * @param ?string $endDate to store the query selection.
     * @param bool $generateTime to store the query selection.
     * @param string $orWheresId To Trigger the or where statement.
     *
     * @return void
     */
    public function addRangeDateTimeWhere(string $columnName, ?string $startDate, ?string $endDate, bool $generateTime = false, string $orWheresId = ''): void
    {
        $startWhere = '';
        $endWhere = '';
        $startTime = '';
        $endTime = '';
        if ($generateTime === true) {
            $startTime = ' 00:00:01';
            $endTime = ' 23:59:59';
        }
        if ($startDate !== null) {
            $start = $startDate . $startTime;
            $startWhere = "(" . $columnName . " >= '" . $start . "')";
            if ($endDate === null) {
                $end = $startDate . $endTime;
                $endWhere = "(" . $columnName . " <= '" . $end . "')";
            }
        }
        if ($endDate !== null) {
            if ($startDate === null) {
                $start = $endDate . $startTime;
                $startWhere = "(" . $columnName . " >= '" . $start . "')";
            }
            $end = $endDate . $endTime;
            $endWhere = "(" . $columnName . " <= '" . $end . "')";
        }
        if (empty($orWheresId) === false) {
            if (empty($startWhere) === false) {
                $this->setOrWhere($orWheresId, $startWhere);
            }
            if (empty($endWhere) === false) {
                $this->setOrWhere($orWheresId, $endWhere);
            }
        } else {
            if (empty($startWhere) === false) {
                $this->Wheres[] = $startWhere;
            }
            if (empty($endWhere) === false) {
                $this->Wheres[] = $endWhere;
            }
        }
    }

    /**
     * Function to load data from database.
     *
     * @param string $columnName to store the query selection.
     * @param ?int|?float $value to store the query selection.
     * @param string $operator to store the query selection.
     * @param string $orWheresId To Trigger the or where statement.
     *
     * @return void
     */
    public function addNumericWhere(string $columnName, $value, string $operator = '=', string $orWheresId = ''): void
    {
        if ($value !== null) {
            if (in_array($operator, self::$OperatorList, true) === false) {
                Message::throwMessage('Invalid operator (' . $operator . ') for generating sql numeric conditions.');
            }
            $where = '(' . $columnName . ' ' . $operator . ' ' . $value . ')';
            if (empty($orWheresId) === false) {
                $this->setOrWhere($orWheresId, $where);
            } else {
                $this->Wheres[] = $where;
            }
        }
    }

    /**
     * Function to load data from database.
     *
     * @param string $columnName to store the query selection.
     * @param array $list to store the query selection.
     * @param bool $inArray to store the query selection.
     * @param string $orWheresId To Trigger the or where statement.
     *
     * @return void
     */
    public function addInArrayNumericWhere(string $columnName, array $list, bool $inArray = true, string $orWheresId = ''): void
    {
        if (empty($list) === false) {
            if ($inArray === true) {
                $where = '(' . $columnName . ' IN (' . implode(', ', $list) . '))';

            } else {
                $where = '(' . $columnName . ' NOT IN (' . implode(', ', $list) . '))';
            }
            if (empty($orWheresId) === false) {
                $this->setOrWhere($orWheresId, $where);
            } else {
                $this->Wheres[] = $where;
            }
        }
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
     * @param string $orWheresId To Trigger the or where statement.
     *
     * @return void
     */
    public function addLikeWhere(string $columnName, ?string $value, string $matchingType = 'C', string $orWheresId = ''): void
    {
        if ($value !== null) {
            $string = '';
            if (is_string($value)) {
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
            $where = '(LOWER(' . $columnName . ') like ' . $like . ')';
            if (empty($orWheresId) === false) {
                $this->setOrWhere($orWheresId, $where);
            } else {
                $this->Wheres[] = $where;
            }
        }
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
     * @return void
     */
    public function addOrLikeWhere(array $columnNames, ?string $value, string $matchingType = 'c'): void
    {
        if ($value !== null) {
            if (empty($columnNames) === true) {
                Message::throwMessage('Invalid columns name for generating or like condition.');
            }
            $string = '';
            if (is_string($value)) {
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
            $orWheres = [];
            foreach ($columnNames as $column) {
                $orWheres[] = '(LOWER(' . $column . ') like ' . $like . ')';
            }
            $this->Wheres[] = '(' . implode(' OR ', $orWheres) . ')';
        }
    }


    /**
     * Function to load data from database.
     *
     * @param string $columnName to store the query selection.
     * @param bool $isNull to store null selection.
     * @param string $orWheresId To Trigger the or where statement.
     *
     * @return void
     */
    public function addNullWhere(string $columnName, bool $isNull = true, string $orWheresId = ''): void
    {
        if ($isNull === true) {
            $where = '(' . $columnName . ' IS NULL)';
        } else {
            $where = '(' . $columnName . ' IS NOT NULL)';
        }
        if (empty($orWheresId) === false) {
            $this->setOrWhere($orWheresId, $where);
        } else {
            $this->Wheres[] = $where;
        }
    }

    /**
     * Function to add where condition.
     *
     * @param string $where To store the where statement
     * @param string $orWheresId To Trigger the or where statement.
     *
     * @return void
     */
    public function addWhere(string $where, string $orWheresId = ''): void
    {
        if (empty($where) === false) {
            if (empty($orWheresId) === false) {
                $this->setOrWhere($orWheresId, $where);
            } else {
                $this->Wheres[] = $where;
            }
        }
    }

    /**
     * Function to add where condition.
     *
     * @param string $where To store the where statement
     * @param string $orWheresId To Trigger the or where statement.
     *
     * @return void
     */
    public function setOrWhere(string $orWheresId, string $where): void
    {
        if (empty($where) === false) {
            if (array_key_exists($orWheresId, $this->OrWheres) === false) {
                $this->OrWheres[$orWheresId] = [];
            }
            $this->OrWheres[$orWheresId][] = $where;
        }
    }

    /**
     * Function to get generated where statement.
     *
     * @return string
     */
    public function getWhereStatement(): string
    {
        if (empty($this->OrWheres) === false) {
            $keys = array_keys($this->OrWheres);
            foreach ($keys as $key) {
                $this->Wheres[] = '(' . implode(' OR ', $this->OrWheres[$key]) . ')';
            }
        }
        if (empty($this->Wheres) === false) {
            return ' WHERE ' . implode(' AND ', $this->Wheres);
        }
        return '';
    }


    /**
     * Function to set limit and offset data.
     *
     * @param int $limit To store the limit.
     * @param int $offset To store the offset.
     *
     * @return void
     */
    public function setLimit(int $limit, int $offset = 0): void
    {
        $this->Limit = $limit;
        $this->Offset = $offset;
    }

    /**
     * Function to get generated limit statement.
     *
     * @return string
     */
    public function getLimitStatement(): string
    {
        if ($this->Limit > 0) {
            return ' LIMIT ' . $this->Limit . ' OFFSET ' . $this->Offset;
        }
        return '';
    }


    /**
     * Function to set the orders data
     *
     * @param array $orders To store the list of order field.
     *
     * @return void
     */
    public function setOrderBy(array $orders): void
    {
        $this->Orders = array_merge($this->Orders, $orders);
    }

    /**
     * Function to add the order field
     *
     * @param string $order To store the list of order field.
     * @param bool $isDescending To store the trigger for descending order.
     *
     * @return void
     */
    public function addOrderBy(string $order, bool $isDescending = false): void
    {
        if (empty($order) === false) {
            if ($isDescending === true) {
                $this->Orders[] = $order . ' DESC';
            } else {
                $this->Orders[] = $order;
            }
        }
    }

    /**
     * Function to add the order field
     *
     * @param string $order To store the list of order field.
     *
     * @return void
     */
    public function addOrderByString(string $order): void
    {
        if (empty($order) === false) {
            $orders = explode(',', $order);
            foreach ($orders as $o) {
                $this->Orders[] = trim($o);
            }
        }
    }

    /**
     * Function to get generated limit statement.
     *
     * @return string
     */
    public function getOrderByStatement(): string
    {
        if (empty($this->Orders) === false) {
            return ' ORDER BY ' . implode(', ', $this->Orders);
        }
        return '';
    }


    /**
     * Function to set the groups data
     *
     * @param array $groups To store the list of group field.
     *
     * @return void
     */
    public function setGroupBy(array $groups): void
    {
        $this->Groups = array_merge($this->Groups, $groups);
    }

    /**
     * Function to add the group field
     *
     * @param string $group To store the list of group field.
     *
     * @return void
     */
    public function addGroupBy(string $group): void
    {
        if (empty($group) === false) {
            $groups = explode(',', $group);
            foreach ($groups as $g) {
                $this->Groups[] = trim($g);
            }
        }
    }

    /**
     * Function to get generated limit statement.
     *
     * @return string
     */
    public function getGroupByStatement(): string
    {
        if (empty($this->Groups) === false) {
            return ' GROUP BY ' . implode(', ', $this->Groups);
        }
        return '';
    }

    /**
     * Function to get generated limit statement.
     *
     * @return bool
     */
    public function hasOrderBy(): bool
    {
        return !empty($this->Orders);
    }

    /**
     * Function to convert object into string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getWhereStatement() . $this->getGroupByStatement() . $this->getOrderByStatement() . $this->getLimitStatement();
    }


    /**
     * Function to convert object into string.
     *
     * @return string
     */
    public function getConditionForCountData(): string
    {
        return $this->getWhereStatement();
    }


}
