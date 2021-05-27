<?php

namespace App\Frame\Formatter;

use App\Frame\System\Session\UserSession;

class NumberFormatter
{
    /**
     * Attribute for decimal separator
     *
     * @var string $DecimalSeparator
     */
    private $DecimalSeparator = '.';

    /**
     * Attribute for thousand separator
     *
     * @var string $ThousandSeparator
     */
    private $ThousandSeparator = ',';

    /**
     * Attribute for total character of the decimal.
     *
     * @var integer $NumberOfDecimal
     */
    private $NumberOfDecimal = 2;

    /**
     * DecimalFormatter constructor.
     *
     * @param UserSession $user To store the session data.
     */
    public function __construct(UserSession $user = null)
    {
        if($user === null) {
            $user = new UserSession();
        }
        if ($user->isSet()) {
            $this->NumberOfDecimal = $user->Settings->getDecimalNumber();
            $this->DecimalSeparator = $user->Settings->getDecimalSeparator();
            $this->ThousandSeparator = $user->Settings->getThousandSeparator();
        }

    }

    /**
     * Do format the data as a float data.
     *
     * @param string $value            To store the value that will be formatted.
     * @param bool   $showEmptyDecimal To trigger the value of empty decimal.
     *
     * @return string
     */
    public function doFormatFloat($value, $showEmptyDecimal = false): string
    {
        $result = '';
        if (is_numeric($value) === true && $value !== null && $value !== '') {
            $floatValue = (float)$value;
            $intValue = (int)$value;
            $decimalNumber = 0;
            if (($floatValue - $intValue) > 0 || $showEmptyDecimal === true) {
                $decimalNumber = $this->NumberOfDecimal;
            }
            $result = number_format($value, $decimalNumber, $this->DecimalSeparator, $this->ThousandSeparator);
        }

        return $result;
    }

    /**
     * Do format the data as a float data.
     *
     * @param float $value To store the value that will be formatted.
     *
     * @return float
     */
    public function doRoundNumber(float $value): float
    {
        return round($value, $this->NumberOfDecimal);
    }

    /**
     * Do format the data as a integer data.
     *
     * @param string $value To store the value that will be formatted.
     *
     * @return string
     */
    public function doFormatInteger($value): string
    {
        $result = '';
        if (is_numeric($value) === true && $value !== '') {
            $result = number_format($value, 0, $this->DecimalSeparator, $this->ThousandSeparator);
        }

        return $result;
    }

    /**
     * Do format the data as a currency data.
     *
     * @param int $value To store the value that will be formatted.
     *
     * @return string
     */
    public function doFormatCurrency($value): string
    {
        return $this->doFormatFloat($value);
    }

    /**
     * Do format the data as a float data.
     *
     * @param string $strValue To store the value that will be formatted.
     *
     * @return float
     */
    public function doParseFloat($strValue): float
    {
        $result = 0;
        if (empty($strValue) === false) {
            $strValue = str_replace($this->ThousandSeparator, '', $strValue);
            if ($this->DecimalSeparator === ',') {
                $strValue = str_replace($this->DecimalSeparator, '.', $strValue);
            }
            $result = (float)$strValue;
        }

        return $result;
    }

    /**
     * Do format the data as a integer data.
     *
     * @param string $strValue To store the value that will be formatted.
     *
     * @return float
     */
    public function doParseInteger($strValue): float
    {
        $result = 0;
        if (empty($strValue) === false) {
            $result = (int)$this->doParseFloat($strValue);
        }

        return $result;
    }

    /**
     * Do format the data as a integer data.
     *
     * @param string $amount To store the value that will be formatted.
     *
     * @return string
     */
    public function doFormatAmount($amount): string
    {
        if (is_numeric($amount) === false) {
            return $amount;
        }
        if ($amount >= 1000000000000 || $amount <= -1000000000000) {
            $postfix = ' T';
            $divider = 1000000000000;
        } else if ($amount >= 1000000000 || $amount <= -1000000000) {
            $postfix = ' B';
            $divider = 1000000000;
        } else if ($amount >= 10000000 || $amount <= -10000000) {
            $postfix = ' M';
            $divider = 1000000;
        } else if ($amount >= 10000 || $amount <= -10000) {
            $postfix = ' K';
            $divider = 1000;
        } else {
            $postfix = '';
            $divider = 0;
        }
        if ($divider > 0) {
            $amount /= $divider;
        }
        $result = $this->doFormatFloat($amount);

        return $result . $postfix;
    }
}
