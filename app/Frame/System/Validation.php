<?php

/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 29/08/2018 C-Book
 */

namespace App\Frame\System;

use App\Frame\Exceptions\Message;
use App\Model\Helper\Job\Warehouse\InboundReceivePn;
use App\Model\Helper\Job\Warehouse\InboundReceiveSn;
use App\Model\Helper\Job\Warehouse\InboundStoringSn;
use App\Rules\AdvanceUnique;
use App\Rules\Bundling\BundlingDetailSerialNumber;
use App\Rules\Bundling\BundlingMaterialLotNumber;
use App\Rules\Bundling\BundlingMaterialSerialNumber;
use App\Rules\Bundling\CreatingGoodsBundleRule;
use App\Rules\CheckCurrentPassword;
use App\Rules\CheckEmptyQueryResult;
use App\Rules\CheckGoodsPrefixNumber;
use App\Rules\CheckSerialNumberPrefix;
use App\Rules\CheckSnReceiveAndStoringInbound;
use App\Rules\CheckSpecialCharacter;
use App\Rules\CheckUnique;
use App\Rules\Inbound\CheckReceivePn;
use App\Rules\Inbound\CheckReceiveSn;
use App\Rules\Inbound\CheckStoringSn;
use App\Rules\InboundSerialNumberRule;
use Illuminate\Support\MessageBag;
use Validator;

/**
 * Class to control the validation of field value.
 *
 * @package    app
 * @subpackage Util\System
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  29/08/2018 C-Book
 */
class Validation
{
    /**
     * Attribute to set the method of the form.
     *
     * @var array $Rules
     */
    private $Rules = [];
    /**
     * Attribute to set the method of the form.
     *
     * @var array $Inputs
     */
    private $Inputs = [];
    /**
     * Attribute to set the method of the form.
     *
     * @var bool $Valid
     */
    private $Valid = true;
    /**
     * Attribute to set the trigger if the validation already execute.
     *
     * @var bool $ExecuteStatus
     */
    private $ExecuteStatus = false;

    /**
     * Attribute to store the error validation.
     *
     * @var MessageBag $Errors
     */
    private $Errors;

    /**
     * Validation constructor.
     */
    public function __construct()
    {
    }


    /**
     * Function to validate all the input value base on the rules.
     *
     * @return void
     */
    public function doValidation(): void
    {
        if (empty($this->Rules) === false) {
            $validator = Validator::make($this->Inputs, $this->Rules);
            if ($validator->fails()) {
                $this->Valid = false;
                $this->Errors = $validator->errors();
            }
        }
        $this->ExecuteStatus = true;
    }

    /**
     * Function to set the inputs that will be validate.
     *
     * @param array $inputs To store all the input value.
     *
     * @return void
     */
    public function setInputs($inputs): void
    {
        $this->Inputs = $inputs;
    }

    /**
     * Function to set the inputs that will be validate.
     *
     * @return bool
     */
    public function isValidated(): bool
    {
        return $this->ExecuteStatus;
    }

    /**
     * Function to check is there any invalid parameters.
     *
     * @return bool
     */
    public function isValidInputs(): bool
    {
        return $this->Valid;
    }

    /**
     * Function to set the rules.
     *
     * @param array $rules To store all the rules.
     *
     * @return void
     * @deprecated
     */
    public function setRules(array $rules): void
    {
        $this->Rules = array_merge($this->Rules, $rules);
    }

    /**
     * Function to get the error message.
     *
     * @param string $fieldId    To set the id of the field.
     * @param string $fieldAlias To set the id of the field.
     *
     * @return string
     */
    public function getErrorMessage($fieldId, $fieldAlias = ''): string
    {
        $result = '';
        if ($this->isValid($fieldId) === false) {
            $result = $this->Errors->first($fieldId);
            if (empty($fieldAlias) === false) {
                $key = str_replace('_', ' ', $fieldId);
                $result = str_replace($key, $fieldAlias, $result);
            }
        }

        return $result;
    }

    /**
     * Function to get the error message.
     *
     * @return array
     */
    public function getAllErrorMessage(): array
    {
        $results = [];
        if ($this->Errors !== null) {
            $fields = array_keys($this->Rules);
            foreach ($fields as $field) {
                if ($this->Errors->has($field) === true) {
                    $key = str_replace('_', ' ', $field);
                    $message = $this->Errors->first($field);
                    $results[] = str_replace($key, $field, $message);
                }
            }
        }

        return $results;
    }

    /**
     * Function to get the error message.
     *
     * @return string
     */
    public function getFirstErrorMessage(): string
    {
        $message = '';
        $errors = $this->getAllErrorMessage();
        if (empty($errors) === false) {
            $message = $errors[0];
        }

        return $message;
    }

    /**
     * Function to check is the field invalid or not.
     *
     * @param string $fieldId To set the id of the field.
     *
     * @return boolean
     */
    public function isValid($fieldId): bool
    {
        $result = true;
        if ($this->Errors !== null && $this->Errors->has($fieldId) === true) {
            $result = false;
        }

        return $result;
    }

    /**
     * Function to get old data.
     *
     * @param string $fieldId To set the id of the field.
     *
     * @return null|array|string
     */
    public function getOldValue($fieldId)
    {
        $result = '';
        if ($this->Valid === false && array_key_exists($fieldId, $this->Inputs) === true) {
            $result = $this->Inputs[$fieldId];
        }

        return $result;
    }

    /**
     * Function to add rule
     *
     * @param string $key  To store key of rule.
     * @param string $rule To store all the rule.
     *
     * @return void
     * @deprecated
     */
    public function addRule($key, $rule): void
    {
        if (empty($key) === false) {
            $this->Rules[$key] = $rule;
        }
    }

    /**
     * Function to check is the validation rule exist or not.
     *
     * @return boolean
     */
    public function isValidationExist(): bool
    {
        $result = true;
        if (empty($this->Rules) === true) {
            $result = false;
        }

        return $result;
    }

    /**
     * Function to check require
     *
     * @param string $fieldId   To set the id of the field.
     * @param int    $minLength To set the id of the field.
     * @param int    $maxLength To set the id of the field.
     *
     * @return void
     */
    public function checkRequire($fieldId, $minLength = -1, $maxLength = 0): void
    {
        if (empty($fieldId) === false) {
            $this->Rules[$fieldId][] = 'required';
            $this->checkMinLength($fieldId, $minLength);
            $this->checkMaxLength($fieldId, $maxLength);
        }
    }

    /**
     * Function to check require
     *
     * @param string $fieldId   To set the id of the field.
     * @param int    $minLength To set the id of the field.
     *
     * @return void
     */
    public function checkRequireArray($fieldId, $minLength = -1): void
    {
        if (empty($fieldId) === false) {
            $this->Rules[$fieldId][] = 'required';
            $this->Rules[$fieldId][] = 'array';
            $this->checkMinLength($fieldId, $minLength);
        }
    }

    /**
     * Function to check integer value
     *
     * @param string $fieldId  To set the id of the field.
     * @param string $minValue To set the min value of integer.
     * @param string $maxValue To set the id of the field.
     *
     * @return void
     */
    public function checkInt($fieldId, $minValue = 'undefined', $maxValue = 'undefined'): void
    {
        if (empty($fieldId) === false) {
            # generate the rule.
            $this->Rules[$fieldId][] = 'integer';
            if (is_numeric($minValue) === true) {
                $this->Rules[$fieldId][] = 'min:' . (int)$minValue;
            }
            if (is_numeric($maxValue) === true) {
                $this->Rules[$fieldId][] = 'max:' . (int)$maxValue;
            }
        }
    }

    /**
     * Function to check float value
     *
     * @param string $fieldId  To set the id of the field.
     * @param int    $minValue To set the min value of integer.
     * @param int    $maxValue To set the id of the field.
     *
     * @return void
     */
    public function checkFloat($fieldId, $minValue = null, $maxValue = null): void
    {
        if (empty($fieldId) === false) {
            # generate the rule.
            $this->Rules[$fieldId][] = 'numeric';
            if ($minValue !== null && is_numeric($minValue) === true) {
                $this->Rules[$fieldId][] = 'min:' . (int)$minValue;
            }
            if ($maxValue !== null && is_numeric($maxValue) === true) {
                $this->Rules[$fieldId][] = 'max:' . (int)$maxValue;
            }
        }
    }

    /**
     * Function to check date
     *
     * @param string $fieldId To set the id of the field.
     * @param string $format  To set the format of the date.
     * @param string $before  To set the validation before date.
     * @param string $after   To set the validation after date.
     *
     * @return void
     */
    public function checkDate($fieldId, $before = '', $after = '', $format = 'Y-m-d'): void
    {
        if (empty($fieldId) === false) {
            $this->Rules[$fieldId][] = 'date_format:' . $format;
            if (empty($before) === false) {
                $this->Rules[$fieldId][] = 'before:' . $before;
            }
            if (empty($after) === false) {
                $this->Rules[$fieldId][] = 'after:' . $after;
            }
        }
    }

    /**
     * Function to check time
     *
     * @param string $fieldId To set the id of the field.
     * @param string $format  To set the format of the time.
     *
     * @return void
     */
    public function checkTime($fieldId, $format = 'H:i'): void
    {
        if (empty($fieldId) === false) {
            $this->Rules[$fieldId][] = 'date_format:' . $format;
        }
    }

    /**
     * Function to check unique
     *
     * @param string $fieldId          To set the id of the field.
     * @param string $tableName        To set the table name.
     * @param array  $ignoreFieldValue To set the ignore field value.
     * @param array  $uniqueFieldValue To set the unique field value.
     *
     * @return void
     */
    public function checkUnique($fieldId, $tableName, array $ignoreFieldValue = [], array $uniqueFieldValue = []): void
    {
        if (empty($fieldId) === false && empty($tableName) === false && empty($ignoreFieldValue) === false) {
            $this->Rules[$fieldId][] = new CheckUnique($tableName, $ignoreFieldValue, $uniqueFieldValue);
        }
    }

    /**
     * Function to check advance unique
     *
     * @param string $fieldId          To set the id of the field.
     * @param string $tableName        To set the table name.
     * @param string $columnName       To set the column name.
     * @param array  $ignoreFieldValue To set the ignore field value.
     * @param array  $uniqueFieldValue To set the unique field value.
     *
     * @return void
     */
    public function checkAdvanceUnique($fieldId, $tableName, string $columnName, array $ignoreFieldValue = [], array $uniqueFieldValue = []): void
    {
        if (empty($fieldId) === false && empty($tableName) === false && empty($ignoreFieldValue) === false) {
            $this->Rules[$fieldId][] = new AdvanceUnique($tableName, $columnName, $ignoreFieldValue, $uniqueFieldValue);
        }
    }

    /**
     * Function to check unique
     *
     * @param string $fieldId To set the id of the field.
     * @param int    $jirId   To set the goods Id.
     *
     * @return void
     */
    public function checkGoodsSnPrefix($fieldId, $jirId): void
    {
        if (empty($fieldId) === false && empty($jirId) === false) {
            $this->Rules[$fieldId][] = new CheckSerialNumberPrefix($jirId);
        }
    }

    /**
     * Function to check unique
     *
     * @param string $fieldId To set the id of the field.
     * @param int    $gdId    To set the goods Id.
     *
     * @return void
     */
    public function checkGoodsSnPrefixByGdId($fieldId, $gdId): void
    {
        if (empty($fieldId) === false && empty($gdId) === false) {
            $this->Rules[$fieldId][] = new CheckGoodsPrefixNumber($gdId);
        }
    }

    /**
     * Function to check unique
     *
     * @param string $fieldId To set the id of the field.
     *
     * @return void
     */
    public function checkSpecialCharacter($fieldId): void
    {
        $this->Rules[$fieldId][] = new CheckSpecialCharacter();
    }

    /**
     * Function to check unique
     *
     * @param string $fieldId To set the id of the field.
     * @param int    $jiId    To set the id of job inbound.
     * @param int    $jidId   To set the id of job inbound detail.
     * @param int    $gdId    To set the id of goods.
     * @param string $divider To set the divider for multiple serial number.
     *
     * @return void
     */
    public function checkInboundSerialNumber($fieldId, int $jiId, int $jidId, int $gdId, string $divider = ','): void
    {
        $this->Rules[$fieldId][] = new InboundSerialNumberRule($jiId, $jidId, $gdId, $divider);
    }

    /**
     * Function to check unique
     *
     * @param string $fieldId To set the id of the field.
     * @param int    $jirId   To set the id of job inbound.
     *
     * @return void
     */
    public function checkSnReceiveAndStoringInbound($fieldId, int $jirId): void
    {
        $this->Rules[$fieldId][] = new CheckSnReceiveAndStoringInbound($jirId);
    }

    /**
     * Function to check empty query data.
     *
     * @param string $fieldId To set the id of the field.
     * @param string $query   To set the query for database.
     * @param string $message To set the custom error message.
     *
     * @return void
     */
    public function checkEmptyQueryResult($fieldId, $query, $message = ''): void
    {
        if (empty($fieldId) === false && empty($query) === false) {
            $this->Rules[$fieldId][] = new CheckEmptyQueryResult($query, $message);
        } else {
            Message::throwMessage('Invalid parameter for check empty query result validation.');
        }
    }

    /**
     * Function to check min length
     *
     * @param string  $fieldId  To set the id of the field.
     * @param integer $minValue To set the min value of integer.
     *
     * @return void
     */
    public function checkMinLength($fieldId, $minValue): void
    {
        if (empty($fieldId) === false && is_int($minValue) === true && $minValue >= 0) {
            $this->Rules[$fieldId][] = 'min:' . $minValue;
        }
    }

    /**
     * Function to check max length
     *
     * @param string  $fieldId  To set the id of the field.
     * @param integer $maxValue To set the id of the field.
     *
     * @return void
     */
    public function checkMaxLength($fieldId, $maxValue): void
    {
        if (empty($fieldId) === false && is_int($maxValue) === true && $maxValue > 0) {
            $this->Rules[$fieldId][] = 'max:' . $maxValue;
        }
    }

    /**
     * Function to check min length
     *
     * @param string  $fieldId  To set the id of the field.
     * @param integer $minValue To set the min value of integer.
     *
     * @return void
     */
    public function checkMinValue($fieldId, $minValue): void
    {
        if (empty($fieldId) === false && is_numeric($minValue) === true) {
            $this->Rules[$fieldId][] = 'min:' . $minValue;
        }
    }

    /**
     * Function to check max length
     *
     * @param string  $fieldId  To set the id of the field.
     * @param integer $maxValue To set the id of the field.
     *
     * @return void
     */
    public function checkMaxValue($fieldId, $maxValue): void
    {
        if (empty($fieldId) === false && is_numeric($maxValue) === true) {
            $this->Rules[$fieldId][] = 'max:' . $maxValue;
        }
    }

    /**
     * Function to check max length
     *
     * @param string $fieldId      To set the id of the field.
     * @param string $compareField To set the id of the field.
     *
     * @return void
     */
    public function checkDifferent($fieldId, $compareField): void
    {
        if (empty($fieldId) === false && empty($compareField) === false) {
            $this->Rules[$fieldId][] = 'different:' . $compareField;
        }
    }

    /**
     * Function to check email
     *
     * @param string $fieldId   To set the id of the field.
     * @param int    $minLength To set the id of the field.
     * @param int    $maxLength To set the id of the field.
     *
     * @return void
     */
    public function checkEmail($fieldId, $minLength = -1, $maxLength = 0): void
    {
        if (empty($fieldId) === false) {
            $this->Rules[$fieldId][] = 'email';
            $this->checkMinLength($fieldId, $minLength);
            $this->checkMaxLength($fieldId, $maxLength);
        }
    }

    /**
     * Function to check confirmation value
     *
     * @param string $fieldId To set the id of the field.
     *
     * @return void
     */
    public function checkConfirmed($fieldId): void
    {
        if (empty($fieldId) === false) {
            $this->Rules[$fieldId][] = 'confirmed';
        }
    }

    /**
     * Function to check max length
     *
     * @param string  $fieldId To set the id of the field.
     * @param integer $maxSize To set the id of the field.
     *
     * @return void
     */
    public function checkFile($fieldId, $maxSize = 10240): void
    {
        if (empty($fieldId) === false) {
            $this->Rules[$fieldId][] = 'file';
            $this->checkMaxLength($fieldId, $maxSize);
        }
    }


    /**
     * Function to check max length
     *
     * @param string $fieldId To set the id of the field.
     *
     * @return void
     */
    public function checkImage($fieldId): void
    {
        if (empty($fieldId) === false) {
            $this->Rules[$fieldId][] = 'image';
        }
    }


    /**
     * Function to check max length
     *
     * @param string $fieldId To set the id of the field.
     * @param array  $mimes   To set the id of the field.
     *
     * @return void
     */
    public function checkMimes($fieldId, array $mimes): void
    {
        if (empty($fieldId) === false && empty($mimes) === false) {
            $this->Rules[$fieldId][] = 'mimes:' . implode(',', $mimes);
        }
    }

    /**
     * Function to check current password
     *
     * @param string  $fieldId To set the id of the field.
     * @param integer $userId  To set the id of the current user.
     *
     * @return void
     */
    public function checkCurrentPassword($fieldId, $userId): void
    {
        if (empty($fieldId) === false && empty($userId) === false) {
            $this->Rules[$fieldId][] = new CheckCurrentPassword($userId);
        } else {
            Message::throwMessage('Invalid parameter for check current password.');
        }
    }

    /**
     * Function to check creation goods bundling
     *
     * @param string  $fieldId     To set the id of the field.
     * @param integer $jbId        To set the id of the current user.
     * @param float   $qtyRequired To set the id of the current user.
     * @param integer $userId      To set the id of the current user.
     *
     * @return void
     */
    public function checkCreatingNewBundle($fieldId, $jbId, $qtyRequired, $userId): void
    {
        if (empty($fieldId) === false && empty($userId) === false) {
            $this->Rules[$fieldId][] = new CreatingGoodsBundleRule($jbId, $qtyRequired, $userId);
        }
    }

    /**
     * Function to check creation goods bundling
     *
     * @param string  $fieldId To set the id of the field.
     * @param integer $gdId    To set the id of the current user.
     * @param integer $jbdId   To set the id of job bundling detail.
     * @param string  $divider To store the multiple data divider
     *
     * @return void
     */
    public function checkSnBundlingDetail($fieldId, $gdId, $jbdId, $divider = ','): void
    {
        if (empty($fieldId) === false) {
            $this->Rules[$fieldId][] = new BundlingDetailSerialNumber($gdId, $jbdId, $divider);
        }
    }

    /**
     * Function to check creation goods bundling
     *
     * @param string  $fieldId To set the id of the field.
     * @param integer $jogId   To set the id of the job goods id.
     * @param integer $jobId   To set the id of job outbound.
     * @param integer $jbId    To set the id of job bundling.
     * @param integer $jbmId   To set the id of job bundling material.
     *
     * @return void
     */
    public function checkLotBundlingMaterial($fieldId, $jogId, $jobId, $jbId, $jbmId): void
    {
        if (empty($fieldId) === false) {
            $this->Rules[$fieldId][] = new BundlingMaterialLotNumber($jogId, $jobId, $jbId, $jbmId);
        }
    }

    /**
     * Function to check creation goods bundling
     *
     * @param string  $fieldId To set the id of the field.
     * @param integer $jogId   To set the id of the job goods id.
     * @param integer $jobId   To set the id of job outbound.
     * @param integer $jbId    To set the id of job bundling.
     * @param integer $jbmId   To set the id of job bundling material.
     *
     * @return void
     */
    public function checkSnBundlingMaterial($fieldId, $jogId, $jobId, $jbId, $jbmId): void
    {
        if (empty($fieldId) === false) {
            $this->Rules[$fieldId][] = new BundlingMaterialSerialNumber($jogId, $jobId, $jbId, $jbmId);
        }
    }

    /**
     * Function to check unique
     *
     * @param string           $fieldId To set the id of the field.
     * @param InboundReceiveSn $jir     To set the goods Id.
     *
     * @return void
     */
    public function checkInboundReceiveSn($fieldId, InboundReceiveSn $jir): void
    {
        if (empty($fieldId) === false && $jir !== null) {
            $this->Rules[$fieldId][] = new CheckReceiveSn($jir);
        }
    }

    /**
     * Function to check unique
     *
     * @param string           $fieldId To set the id of the field.
     * @param InboundReceivePn $jir     To set the goods Id.
     *
     * @return void
     */
    public function checkInboundReceivePn($fieldId, InboundReceivePn $jir): void
    {
        if (empty($fieldId) === false && $jir !== null) {
            $this->Rules[$fieldId][] = new CheckReceivePn($jir);
        }
    }

    /**
     * Function to check unique
     *
     * @param string           $fieldId To set the id of the field.
     * @param InboundStoringSn $data    To set the goods Id.
     *
     * @return void
     */
    public function checkInboundStoringSn($fieldId, InboundStoringSn $data): void
    {
        if (empty($fieldId) === false && $data !== null) {
            $this->Rules[$fieldId][] = new CheckStoringSn($data);
        }
    }

}
