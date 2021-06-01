<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Frame\System\Session;

/**
 *
 *
 * @package    app
 * @subpackage Frame\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class SystemSettingSession
{

    /**
     * Property to store all the right for current page.
     *
     * @var array
     */
    private $Data;

    /**
     * Base model constructor.
     *
     * @param array $data to store the data.
     */
    public function __construct(array $data)
    {
        $this->Data = $data;
    }

    /**
     * Function to get user id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->getStringValue('ss_id');
    }

    /**
     * Function to get user id
     *
     * @param array $data to store the data.
     * @return void
     */
    public function setData(array $data): void
    {
        $this->Data = $data;
    }

    /**
     * Function to get system name space
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getStringValue('ss_relation');
    }


    /**
     * Function to get system name space
     *
     * @return string
     */
    public function getOwnerId(): string
    {
        return $this->getStringValue('ss_rel_id');
    }

    /**
     * Function to get system name space
     *
     * @return string
     */
    public function getNameSpace(): string
    {
        $ns = $this->getStringValue('ss_name_space');
        if (empty($ns) === false) {
            return mb_strtolower($ns);
        }

        return $ns;
    }

    /**
     * Function to get user userName
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->getStringValue('ss_lg_locale');
    }

    /**
     * Function to get user userName
     *
     * @return string
     */
    public function getLanguageIso(): string
    {
        return $this->getStringValue('ss_lg_iso');
    }

    /**
     * Function to get decimal number
     *
     * @return int
     */
    public function getDecimalNumber(): int
    {
        return $this->getIntValue('ss_decimal_number');
    }

    /**
     * Function to get decimal separator
     *
     * @return string
     */
    public function getDecimalSeparator(): string
    {
        return $this->getStringValue('ss_decimal_separator');
    }

    /**
     * Function to get thousand separator
     *
     * @return string
     */
    public function getThousandSeparator(): string
    {
        return $this->getStringValue('ss_thousand_separator');
    }

    /**
     * Function to get logo
     *
     * @return string
     */
    public function getLogo(): string
    {
        return $this->getStringValue('ss_logo');
    }

    /**
     * Function to get currency uid
     *
     * @return string
     */
    public function getCurrencyId(): string
    {
        return $this->getStringValue('ss_cur_id');
    }

    /**
     * Function to get currency
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->getStringValue('ss_currency');
    }


    /**
     * Function to get currency ISO
     *
     * @return string
     */
    public function getCurrencyIso(): string
    {
        return $this->getStringValue('ss_currency_iso');
    }

    /**
     * Function to check is setting System
     *
     * @return bool
     */
    public function isSystem(): bool
    {
        $val = $this->getStringValue('ss_system');
        return ($val === 'Y');
    }


    /**
     * Function to get user id
     *
     * @param string $keyWord To store the keyword.
     *
     * @return string
     */
    private function getStringValue(string $keyWord): string
    {
        if (array_key_exists($keyWord, $this->Data) === true && $this->Data[$keyWord] !== null) {
            return $this->Data[$keyWord];
        }
        return '';
    }

    /**
     * Function to get user id
     *
     * @param string $keyWord To store the keyword.
     *
     * @return int
     */
    private function getIntValue(string $keyWord): int
    {
        if (array_key_exists($keyWord, $this->Data) === true && $this->Data[$keyWord] !== null && is_numeric($this->Data[$keyWord]) === true) {
            return (int)$this->Data[$keyWord];
        }
        return 0;
    }

}
