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

/**
 * Class to handle converting of object.
 *
 * @package    app
 * @subpackage Util\Formatter
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  16/03/2017 C-Book
 */
class DataParser
{

    /**
     * Function to parse the data from stdClass to array
     *
     * @param mixed $data To store the data that will be parse.
     *
     * @return mixed
     */
    public function nullToEmptyString($data)
    {
        if ($data === null || empty($data) === true) {
            $data = '';
        }

        return $data;
    }

    /**
     * Function to parse the data from stdClass to array
     *
     * @param mixed  $data          To store the data that will be parse.
     * @param string $attributeName To store the attribute name that will be taken.
     *
     * @return null|string
     */
    public static function getAttributeValue($data, $attributeName): ?string
    {
        $value = '';
        if (\is_object($data) === true && property_exists($data, $attributeName) === true) {
            $value = $data->$attributeName;
        } elseif (\is_array($data) === true && array_key_exists($attributeName, $data) === true) {
            $value = $data[$attributeName];
        }

        return $value;
    }


    /**
     * Function to parse the array object data to normal array.
     *
     * @param array $arrayObject To store the data that will be parse.
     * @param array $attributes  To store the attribute name that will be taken.
     *
     * @return array
     */
    public static function arrayObjectToArray(array $arrayObject, array $attributes = []): array
    {
        $result = [];
        if (empty($arrayObject) === false) {
            foreach ($arrayObject as $obj) {
                $result[] = self::objectToArray($obj, $attributes);
            }
        }

        return $result;
    }

    /**
     * Function to parse the object data to normal array.
     *
     * @param \stdClass $object     To store the data that will be parse.
     * @param array     $attributes To store the attribute name that will be taken.
     *
     * @return array
     */
    public static function objectToArray($object, array $attributes = []): array
    {
        $result = [];
        if (\is_object($object) === true && $object !== null) {
            if (empty($attributes) === false) {
                foreach ($attributes as $attribute) {
                    $value = '';
                    if (property_exists($object, $attribute) === true) {
                        $value = $object->$attribute;
                    }
                    $result[$attribute] = $value;
                }
            } else {
                $result = get_object_vars($object);
            }
        }

        return $result;
    }

    /**
     * Function to parse the array object data to normal array.
     *
     * @param array $arrayObject To store the data that will be parse.
     *
     * @return array
     */
    public static function arrayObjectToArrayAPI(array $arrayObject): array
    {
        $result = [];
        if (empty($arrayObject) === false) {
            foreach ($arrayObject as $obj) {
                if (\is_object($obj) === true) {
                    $data = get_object_vars($obj);
                    $row = [];
                    foreach ($data as $key => $val) {
                        $value = '';
                        if (empty($val) === false) {
                            $value = $val;
                        }
                        $row[$key] = $value;
                    }
                    $result[] = $row;
                }

            }
        }

        return $result;
    }

    /**
     * Function to parse the object data to normal array.
     *
     * @param \stdClass $object     To store the data that will be parse.
     * @param array     $attributes To store the attribute name that will be taken.
     *
     * @return array
     */
    public static function objectToArrayAPI($object, array $attributes = []): array
    {
        $result = [];
        if (\is_object($object) === true && $object !== null) {
            if (empty($attributes) === false) {
                foreach ($attributes as $attribute) {
                    $value = '';
                    if (property_exists($object, $attribute) === true && $object->$attribute !== null) {
                        $value = $object->$attribute;
                    }
                    $result[$attribute] = $value;
                }
            } else {
                $result = self::doFormatApiData(get_object_vars($object));

            }
        }

        return $result;
    }


    /**
     * Function to add data into the results property
     *
     * @param mixed $data To store the response data.
     *
     * @return array
     */
    public static function doFormatApiData($data): array
    {
        $result = [];
        foreach ($data as $key => $val) {
            if ($val === null || $val === '') {
                $val = '';
            }
            $result[$key] = $val;
        }

        return $result;
    }

    /**
     * Function to add data into the results property
     *
     * @param array $data To store the response data.
     *
     * @return array
     */
    public static function doFormatArrayApiData(array $data): array
    {
        $result = [];
        foreach ($data as $row) {
            $temp = [];
            foreach ($row as $key => $val) {
                if ($val === null || $val === '') {
                    $val = '';
                }
                $temp[$key] = $val;
            }
            $result[] = $temp;
        }

        return $result;
    }

    /**
     * Function to add data into the results property
     *
     * @param array $data To store the response data.
     *
     * @return string
     */
    public static function doFormatAddress(array $data): string
    {
        $temp = [];
        if (array_key_exists('address', $data) === true && empty($data['address']) === false) {
            $temp[] = $data['address'];
        }
        if (array_key_exists('district', $data) === true && empty($data['district']) === false) {
            $temp[] = $data['district'];
        }
        if (array_key_exists('city', $data) === true && empty($data['city']) === false) {
            $temp[] = $data['city'];
        }
        if (array_key_exists('state', $data) === true && empty($data['state']) === false) {
            $temp[] = $data['state'];
        }
        if (array_key_exists('country', $data) === true && empty($data['country']) === false) {
            $temp[] = $data['country'];
        }
        if (array_key_exists('postalCode', $data) === true && empty($data['postalCode']) === false) {
            $temp[] = $data['postalCode'];
        }
        if (empty($temp) === false) {
            return implode(', ', $temp);
        }

        return '';
    }

}
