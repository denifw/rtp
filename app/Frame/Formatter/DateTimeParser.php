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

use App\Frame\Exceptions\Message;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class to handle converting of object.
 *
 * @package    app
 * @subpackage Util\Formatter
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  16/03/2017 C-Book
 */
class DateTimeParser
{

    /**
     * Function to create dateTime
     *
     * @param ?string        $time     To store the string time.
     * @param ?DateTimeZone $timeZone To store the output format.
     *
     * @return DateTime
     */
    public static function createDateTime(?string $time = 'now', ?DateTimeZone $timeZone = null): DateTime
    {
        $result = null;
        try {
            $result = new DateTime($time, $timeZone);
        } catch (Exception $e) {
            Message::throwMessage('Creation of date time failed.');
        }

        return $result;
    }

    /**
     * Function to create dateTime
     *
     * @return DateTime
     */
    public static function createLastMonthDateTime(): DateTime
    {
        $result = null;
        try {
            $now = new DateTime();
            $d = $now->format('Y-m') . '-04';
            $result = DateTime::createFromFormat('Y-m-d', $d);
            $result->modify('-1 month');
        } catch (Exception $e) {
            Message::throwMessage('Creation of date time failed.');
        }

        return $result;
    }

    /**
     * Function to parse the object date into text.
     *
     * @param ?string $strDate   To store the string date.
     * @param string $inFormat  To store the output format.
     * @param string $outFormat To store the output format.
     *
     * @return string
     */
    public function formatDateTime(?string $strDate, string $inFormat = 'Y-m-d H:i:s', string $outFormat = 'H:i d.M.Y'): string
    {
        $result = '';
        if ($strDate !== null && $strDate !== '') {
            try {
                $date = DateTime::createFromFormat($inFormat, $strDate);
                $result = $date->format($outFormat);
            } catch (Exception $e) {
                Message::throwMessage($e->getMessage());
            }
        }

        return $result;
    }
    /**
     * Function to parse the object date into text.
     *
     * @param ?string $strDate   To store the string date.
     * @param string $inFormat  To store the output format.
     * @param string $outFormat To store the output format.
     *
     * @return string
     */
    public function formatDate(?string $strDate, string $inFormat = 'Y-m-d', string $outFormat = 'd.M.Y'): string
    {
        $result = '';
        if ($strDate !== null && $strDate !== '') {
            try {
                $date = DateTime::createFromFormat($inFormat, $strDate);
                $result = $date->format($outFormat);
            } catch (Exception $e) {
                Message::throwMessage($e->getMessage());
            }
        }

        return $result;
    }
    /**
     * Function to parse the object date into text.
     *
     * @param ?string $strTime   To store the string Time.
     * @param string $inFormat  To store the output format.
     * @param string $outFormat To store the output format.
     *
     * @return string
     */
    public function formatTime(?string $strTime, string $inFormat = 'H:i:s', string $outFormat = 'H:i'): string
    {
        $result = '';
        if ($strTime !== null && $strTime !== '') {
            try {
                $date = DateTime::createFromFormat($inFormat, $strTime);
                $result = $date->format($outFormat);
            } catch (Exception $e) {
                Message::throwMessage($e->getMessage());
            }
        }

        return $result;
    }
    /**
     * Function to parse the object date into text.
     *
     * @param ?string $strDate   To store the string date.
     * @param string $inFormat  To store the output format.
     * @param string $outFormat To store the output format.
     *
     * @return string
     */
    public static function format(?string $strDate, string $inFormat = 'Y-m-d H:i:s', string $outFormat = 'H:i - d.M.Y'): string
    {

        $result = '';
        if ($strDate !== null && $strDate !== '' && empty($inFormat) === false && empty($outFormat) === false) {
            try {
                $date = DateTime::createFromFormat($inFormat, $strDate);
                $result = $date->format($outFormat);
            } catch (Exception $e) {
                $result = '';
            }
        }

        return $result;
    }

    /**
     * Function to parse the object date into text.
     *
     * @param ?string $strDate To store the string date.
     * @param string $format  To store the output format.
     *
     * @return null|DateTime
     */
    public static function createFromFormat(?string $strDate, string $format = 'Y-m-d H:i:s'): ?DateTime
    {

        $result = null;
        if ($strDate !== null && $strDate !== '' && empty($format) === false) {
            try {
                $date = DateTime::createFromFormat($format, $strDate);
                if ($date instanceof DateTime) {
                    $result = $date;
                }
            } catch (Exception $e) {
                Message::throwMessage($e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Function to parse the object date into text.
     *
     * @param ?string $strDate   To store the string date.
     * @param string $inFormat  To store the output format.
     * @param string $outFormat To store the output format.
     *
     * @return DateTime
     */
    public static function parse(?string $strDate, string $inFormat = 'Y-m-d H:i:s', string $outFormat = 'd M Y H:i:s'): ?DateTime
    {

        $result = null;
        if ($strDate !== null && $strDate !== '' && empty($inFormat) === false && empty($outFormat) === false) {
            try {
                $date = DateTime::createFromFormat($inFormat, $strDate);
                if ($date !== false) {
                    $result = $date;
                }
            } catch (Exception $e) {
                Message::throwMessage($e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Function to parse the object date into text.
     *
     * @param DateTime $fromDate  To store the string date.
     * @param DateTime $untilDate To store the output format.
     *
     * @return array
     */
    public static function different(DateTime $fromDate, DateTime $untilDate): array
    {
        $diffDate = $fromDate->diff($untilDate);
        $result = [];
        if (is_object($diffDate)) {
            $result = get_object_vars($diffDate);
        }

        return $result;
    }
}
