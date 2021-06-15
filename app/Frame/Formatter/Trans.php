<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Formatter;

use App\Frame\Exceptions\Message;

/**
 *
 *
 * @package    app
 * @subpackage Frame\Formatter
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class Trans
{

    /**
     * Function to get the translation of the page as title.
     *
     * @param string $wordId To store the parameter translation.
     * @param string $fileName To store the file master translation.
     * @param string $default To store the default value if the translation is empty.
     * @param array $params To store parameter into the translation system.
     *
     * @return string
     */
    public static function getWord(string $wordId, string $fileName = 'default', string $default = '', array $params = []): string
    {
        if ($fileName === null || $wordId === null || $fileName === '' || $wordId === '') {
            Message::throwMessage('Invalid translation parameter for file name \'' . $fileName . '\' and param \'' . $wordId . '\'', 'DEBUG');
        }
        $key = $fileName . '.' . $wordId;
        $result = trans($key, $params);
        if ($result === $key) {
            if (empty($default) === true) {
                Message::throwMessage('Word : ' . $wordId . ' with module : \'' . $fileName . '\' did not translated yet.', 'DEBUG');
            } else {
                $result = $default;
            }
        }

        return $result;
    }

    /**
     * Function to get the translation of the page as title.
     *
     * @param string $wordId To store the parameter translation.
     * @param string $default To store the default value if the translation is empty.
     * @param array $params To store parameter into the translation system.
     *
     * @return string
     */
    public static function getMessageWord(string $wordId, string $default = '', array $params = []): string
    {
        return self::getWord($wordId, 'message', $default, $params);
    }

    /**
     * Function to get the translation of the page as title.
     *
     * @param string $wordId To store the parameter translation.
     * @param string $default To store the default value if the translation is empty.
     * @param array $params To store parameter into the translation system.
     *
     * @return string
     */
    public static function getMenuWord(string $wordId, string $default = '', array $params = []): string
    {
        return self::getWord($wordId, 'menu', $default, $params);
    }

    /**
     * Function to get the translation of the page as title.
     *
     * @param string $wordId To store the parameter translation.
     * @param string $default To store the default value if the translation is empty.
     * @param array $params To store parameter into the translation system.
     *
     * @return string
     */
    public static function getPageWord(string $wordId, string $default = '', array $params = []): string
    {
        return self::getWord($wordId, 'page', $default, $params);
    }
}
