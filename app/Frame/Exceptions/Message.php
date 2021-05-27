<?php
/**
 * Contains code written by the MBS Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 29/08/2018 MBS
 */

namespace App\Frame\Exceptions;

/**
 *
 *
 * @package    app
 * @subpackage Exceptions
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  29/08/2018 MBS
 */
class Message
{

    /**
     * Function to show the message.
     *
     * @param string $message Exception log message.
     * @param string $type    To store the type of Exception.
     *
     * @return void
     */
    public static function throwMessage(string $message, string $type = 'DEBUG'): void
    {
        if ($type === 'DEBUG') {
            self::throwDebugMessage($message);
        } elseif ($type === 'WARNING') {
            self::throwWarningMessage($message);
        } else {
            self::throwErrorMessage($message);
        }
    }

    /**
     * Function to throw debug exception.
     *
     * @param string $message Exception log message.
     *
     * @return void
     * @throws \App\Frame\Exceptions\DebugException
     */
    private static function throwDebugMessage(string $message): void
    {
        throw new DebugException($message);
    }

    /**
     * Function to throw warning exception.
     *
     * @param string $message Exception log message.
     *
     * @return void
     * @throws \App\Frame\Exceptions\WarningException
     */
    private static function throwWarningMessage(string $message): void
    {
        throw new WarningException($message);
    }

    /**
     * Function to show the message.
     *
     * @param string $message Exception log message.
     *
     * @return void
     * @throws \App\Frame\Exceptions\ErrorException
     */
    private static function throwErrorMessage(string $message): void
    {
        throw new ErrorException($message);
    }
}
