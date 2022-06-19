<?php

/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 16/03/2017 C-Book
 */

namespace App\Frame\Formatter;

use App\Frame\Gui\Html\Labels\Label;
use App\Frame\Gui\Html\Labels\LabelYesNo;

/**
 * Class to handle string formatter.
 *
 * @package    app
 * @subpackage Util\Formatter
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  16/03/2017 C-Book
 */
class StringFormatter
{

    /**
     * Function to parse the data from stdClass to array
     *
     * @param string $str to store the string to convert.
     *
     * @return bool
     */
    public static function isContainSpecialCharacter($str): bool
    {
        $match = [];
        preg_match('/[^A-Za-z0-9]/', $str, $match);

        return !empty($match);
    }

    /**
     * Function to parse the data from stdClass to array
     *
     * @param string $str to store the string to convert.
     * @param string $replaceTo to store the delimiter of conversion.
     *
     * @return string
     */
    public static function replaceSpecialCharacter($str, $replaceTo = ''): string
    {
        return preg_replace('/[^A-Za-z0-9]/', $replaceTo, $str);
    }

    /**
     * Function to replace new lin to br.
     *
     * @param string $str to store the string to convert.
     *
     * @return string
     */
    public static function replaceNewLineToBr($str): string
    {
        return nl2br($str);
    }

    /**
     * Function to parse the data from stdClass to array
     *
     * @param string $str to store the string to convert.
     *
     * @return string
     */
    public static function formatExcelSheetTitle($str): string
    {
        $result = self::replaceSpecialCharacter(trim($str), '_');
        if (mb_strlen($result) > 30) {
            return mb_substr($result, 0, 30);
        }

        return $result;
    }


    /**
     * Function to parse the data from stdClass to array
     *
     * @param string $str to store the string to convert.
     * @param string $delimiter to store the delimiter of conversion.
     *
     * @return string
     */
    public static function stringToJsonString($str, $delimiter): string
    {
        $str = preg_replace('/\s+/', '', $str);

        $delimiter = trim($delimiter);
        $arr = explode($delimiter, $str);
        $result = '';
        foreach ($arr as $row) {
            $words = explode('.', $row);
            $result .= "'" . $words[count($words) - 1] . "', ";
        }

        return $result;
    }


    /**
     * Function to load data from database.
     *
     * @param string $columnName to store the query selection.
     * @param string $value to store the query selection.
     *
     * @return string
     * @deprecated Use \App\Frame\Formatter\SqlHelper::generateLikeCondition instead.
     */
    public static function generateLikeQuery($columnName, $value): string
    {
        return '(LOWER(' . $columnName . ') like \'%' . mb_strtolower($value) . '%\')';
    }


    /**
     * Function to load data from database.
     *
     * @param string $keyWord to store the query selection.
     * @param array $columnNames to store the query selection.
     *
     * @return string
     * @deprecated Use \App\Frame\Formatter\SqlHelper::generateOrLikeCondition instead.
     */
    public static function generateOrLikeQuery(string $keyWord, array $columnNames): string
    {
        $result = '';
        $keyWord = trim($keyWord);
        $arrayWords = explode(' ', $keyWord);
        $words = [];
        foreach ($arrayWords as $key) {
            if (empty($key) === false) {
                $words[] = $key;
            }
        }
        if (empty($words) === true) {
            $words[] = '';
        }
        if (empty($columnNames) === false) {
            $orWheres = [];
            if (count($words) > 1) {
                $orWheres[] = '(LOWER(' . implode(" || ' ' || ", $columnNames) . ') like \'%' . mb_strtolower(implode(' ', $words)) . '%\')';
            } else {
                foreach ($columnNames as $column) {
                    $orWheres[] = self::generateLikeQuery($column, $words[0]);
                }
            }
            $result = '(' . implode(' OR ', $orWheres) . ')';
        }

        return $result;
    }


    /**
     * Function to generate table view.
     *
     * @param array $data To store the data.
     * @param string $label To store the data.
     * @param string $key To store the data.
     * @param bool $isShowEmptyVal To store the trigger to show empty value data
     * @param string $tableStyle To store the table style.
     *
     * @return string
     */
    public static function generateKeyValueTableView(array $data = [], $label = 'label', $key = 'value', $isShowEmptyVal = false, string $tableStyle = ''): string
    {
        $style = 'width: 100%;';
        if (empty($tableStyle) === false) {
            $style .= $tableStyle;
        }
        $content = '<div>';
        $content .= '<table style="' . $style . '">';
        foreach ($data as $row) {
            $val = $row[$key];
            if ($isShowEmptyVal === true || ($val !== null && $val !== '')) {
                $content .= '<tr>';
                $content .= '<td style="text-align: left;">' . $row[$label] . '</td>';
                $content .= '<td> : </td>';
                $content .= '<td style="text-align: right;">' . $val . '</td>';
                $content .= '</tr>';
            }
        }
        $content .= '</table>';
        $content .= '</div>';

        return $content;
    }


    /**
     * Function to generate table view.
     *
     * @param array $data To store the data.
     * @param string $rowStyle To store row style
     *
     * @return string
     */
    public static function generateTableView(array $data = [], $rowStyle = ''): string
    {
        $content = '<div>';
        $content .= '<table style="width: 100%;">';
        $style = '';
        if (empty($rowStyle) === false) {
            $style = 'style="' . $rowStyle . '"';
        }
        foreach ($data as $val) {
            if ($val !== null && $val !== '') {
                $content .= '<tr ' . $style . '>';
                $content .= '<td>' . $val . '</td>';
                $content .= '</tr>';
            }
        }
        $content .= '</table>';
        $content .= '</div>';

        return $content;
    }

    /**
     * Function to generate label yes no.
     *
     * @param string $yesNo To store the data.
     *
     * @return string
     */
    public static function generateYesNoLabel($yesNo): string
    {
        return new LabelYesNo($yesNo);
    }

    /**
     * Function to generate label yes no.
     *
     * @param string $label To store the data.
     * @param string $type To store the data.
     *
     * @return string
     */
    public static function generateLabel(string $label, string $type): string
    {
        return new Label($label, $type);
    }

    /**
     * Function to validate special character.
     *
     * @param array $inputs To store the input.
     *
     * @return bool
     */
    public static function isContainsSpecialCharacter(array $inputs): bool
    {
        $valid = true;
        $match = [];
        foreach ($inputs as $data) {
            $data = trim($data);
            if (empty($data) === false) {
                preg_match('/[^A-Za-z0-9]/', $data, $match);
                if ($valid === true) {
                    $valid = empty($match);
                }
                $match = [];
            }

        }

        return $valid;
    }

    /**
     * Function to generate table view.
     *
     * @param array $data To store the data.
     * @param integer $large To set the grid amount for a large screen.
     * @param integer $medium To set the grid amount for a medium screen.
     * @param integer $small To set the grid amount for a small screen.
     * @param bool $isShowEmptyVal To set the grid amount for a extra small screen.
     *
     * @return string
     */
    public static function generateCustomTableView(array $data = [], int $large = 12, int $medium = 12, int $small = 12, $isShowEmptyVal = true): string
    {
        $content = '<div class="col-lg-' . $large . ' col-md-' . $medium . ' col-sm-' . $small . ' col-xs-12">';
        $content .= '<table class="table">';
        $i = 0;
        foreach ($data as $row) {
            $val = $row['value'];
            if ($isShowEmptyVal === true || empty($val) === false) {
                if ($val === null || $val === '') {
                    $val = '-';
                }
                if (($i % 2) === 0) {
                    $content .= '<tr style="background: #E0E0FF">';
                } else {
                    $content .= '<tr>';
                }
                $content .= '<td>' . $row['label'] . '</td>';
                $content .= '<td style="font-weight: bold">' . $val . '</td>';
                $content .= '</tr>';
                $i++;
            }
        }
        $content .= '</table>';
        $content .= '</div>';

        return $content;
    }

}
