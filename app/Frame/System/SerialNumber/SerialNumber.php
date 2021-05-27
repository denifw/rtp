<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */

namespace App\Frame\System\SerialNumber;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Setting\SerialHistoryDao;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class to generate serial number.
 *
 * @package    app
 * @subpackage Frame\System\SerialNumber
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class SerialNumber
{
    /**
     * Property to store system settings id.
     *
     * @var int $SsId
     */
    private $SsId;

    /**
     * Property to store the available number.
     *
     * @var int $Number
     */
    private $Number = 0;
    /**
     * Property to store the roman numbers for month.
     *
     * @var array $MonthRoman
     */
    private static $MonthRoman = [
        '01' => 'I',
        '02' => 'II',
        '03' => 'III',
        '04' => 'IV',
        '05' => 'V',
        '06' => 'VI',
        '07' => 'VII',
        '08' => 'VIII',
        '09' => 'IX',
        '10' => 'X',
        '11' => 'XI',
        '12' => 'XII',
    ];

    /**
     * SerialNumber constructor.
     *
     * @param int $ssId To store system settings id
     */
    public function __construct($ssId)
    {
        $this->SsId = $ssId;
    }


    /**
     * Function to load serial number.
     *
     * @param string $serialCode To store the code of serial number.
     * @param int    $ofId       To store the office id.
     * @param int    $relId      To store the relation id.
     * @param int    $srvId      To store the service id.
     * @param int    $srtId      To store the service term id.
     *
     * @return string
     */
    public function loadNumber(string $serialCode, int $ofId = 0, int $relId = 0, int $srvId = 0, int $srtId = 0): string
    {
        if (empty($serialCode) === true) {
            Message::throwMessage('Can not load serial number for empty module.');
        }
        $config = $this->loadConfig($serialCode, $ofId, $srvId, $srtId);
        if (empty($config) === true) {
            Message::throwMessage(Trans::getWord('noSerialNumberFound', 'message', '', ['code' => $serialCode]), 'ERROR');
        }
        $relation = null;
        if ($config['sn_relation'] === 'Y') {
            $relation = $relId;
            if ($relId <= 0) {
                Message::throwMessage(Trans::getWord('noRelationSerialNumberFound', 'message', '', ['code' => $serialCode]), 'ERROR');
            }
        }
        $config['sh_rel_id'] = $relation;
        # Set Date
        $date = DateTimeParser::createDateTime();
        # Set year
        $year = null;
        if ($config['sn_yearly'] === 'Y') {
            $year = $date->format('y');
        }
        $config['sh_year'] = $year;
        # Set month
        $month = null;
        if ($config['sn_monthly'] === 'Y') {
            $month = $date->format('m');
        }
        $config['sh_month'] = $month;
        # Set Relation

        # Load Next Number.
        $this->loadNextNumber($config);

        return $this->doFormatNumber($config, $this->Number);

    }


    /**
     * Function to load serial number config.
     *
     * @param string $serialCode To store the code of serial number.
     * @param int    $ofId       To store the office id.
     * @param int    $srvId      To store the service id.
     * @param int    $srtId      To store the service term id.
     *
     * @return array
     */
    private function loadConfig(string $serialCode, int $ofId, int $srvId, int $srtId): array
    {
        $wheres = [];
        $wheres[] = "(lower(sc.sc_code) = '" . mb_strtolower($serialCode) . "')";
        $wheres[] = '(sn.sn_ss_id = ' . $this->SsId . ')';
        $wheres[] = '((sn.sn_of_id IS NULL) OR (sn.sn_of_id = ' . $ofId . '))';
        $wheres[] = '((sn.sn_srv_id IS NULL) OR (sn.sn_srv_id = ' . $srvId . '))';
        $wheres[] = '((sn.sn_srt_id IS NULL) OR (sn.sn_srt_id = ' . $srtId . '))';
        $wheres[] = "(sn.sn_active = 'Y')";
        $wheres[] = '(sn.sn_deleted_on IS NULL)';
        $wheres[] = "(sc.sc_active = 'Y')";
        $wheres[] = '(sc.sc_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sn.sn_id, sn.sn_ss_id, sn.sn_of_id, sn.sn_relation, sn.sn_srv_id, sn.sn_srt_id, sn.sn_prefix, 
                            sn.sn_separator, sn.sn_postfix, sn.sn_length, sn.sn_yearly, 
                      sn.sn_monthly, sn.sn_increment, sc.sc_code, sn.sn_format
                        FROM serial_number as sn INNER JOIN
                        serial_code AS sc ON sn.sn_sc_id = sc.sc_id ' . $strWhere;
        $sqlResult = DB::select($query);

        $result = [];
        if (empty($sqlResult) === false) {
            $temp = DataParser::arrayObjectToArray($sqlResult);

            $matchAll = [];
            $matchTwo = [];
            $matchOne = [];
            $matchDefault = [];
            foreach ($temp as $row) {
                $match = 0;
                if ($ofId > 0 && (int)$row['sn_of_id'] === $ofId) {
                    $match++;
                }
                if ($srvId > 0 && (int)$row['sn_srv_id'] === $srvId) {
                    $match++;
                }
                if ($srtId > 0 && (int)$row['sn_srt_id'] === $srtId) {
                    $match++;
                }
                switch ($match) {
                    case 0:
                        $matchDefault = $row;
                        break;
                    case 1:
                        $matchOne = $row;
                        break;
                    case 2:
                        $matchTwo = $row;
                        break;
                    case 4:
                        $matchAll = $row;
                        break;
                }
            }
            if (empty($matchAll) === false) {
                $result = $matchAll;
            } elseif (empty($matchTwo) === false) {
                $result = $matchTwo;
            } elseif (empty($matchOne) === false) {
                $result = $matchOne;
            } else {
                $result = $matchDefault;
            }

        }
        return $result;

    }

    /**
     * Function to load the next number for serial number.
     *
     * @param array $config To set the next number.
     *
     * @return void
     */
    private function loadNextNumber(array $config): void
    {
        $wheres = [];
        $wheres[] = '(sh_sn_id = ' . $config['sn_id'] . ')';
        if ($config['sh_year'] === null) {
            $wheres[] = '(sh_year IS NULL)';
        } else {
            $wheres[] = "(sh_year = '" . $config['sh_year'] . "')";
        }
        if ($config['sh_month'] === null) {
            $wheres[] = '(sh_month IS NULL)';
        } else {
            $wheres[] = "(sh_month = '" . $config['sh_month'] . "')";
        }
        if ($config['sh_rel_id'] === null) {
            $wheres[] = '(sh_rel_id IS NULL)';
        } else {
            $wheres[] = '(sh_rel_id = ' . $config['sh_rel_id'] . ')';
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sh_id, sh_number
                        FROM serial_history ' . $strWhere;
        $query .= ' ORDER BY sh_number DESC, sh_id';
        $query .= '  LIMIT 1 OFFSET 0';

        $sqlResult = DB::select($query);
        $shNumber = 0;
        if (count($sqlResult) === 1) {
            $result = DataParser::objectToArray($sqlResult[0]);
            $shNumber = (int)$result['sh_number'];
        }
        $this->storeSerialHistory($config, $shNumber);
    }

    /**
     * Function to store the serial history.
     *
     * @param array $config    To set the next number.
     * @param int   $lasNumber To store the last number.
     *
     * @return void
     */
    private function storeSerialHistory(array $config, int $lasNumber): void
    {
        $lasNumber += (int)$config['sn_increment'];
        try {
            $colVal = [
                'sh_sn_id' => $config['sn_id'],
                'sh_year' => $config['sh_year'],
                'sh_month' => $config['sh_month'],
                'sh_rel_id' => $config['sh_rel_id'],
                'sh_number' => $lasNumber,
            ];
            $shDao = new SerialHistoryDao();
            $shDao->doInsertTransaction($colVal);
            $this->Number = $lasNumber;
        } catch (Exception $e) {
            $this->storeSerialHistory($config, $lasNumber);
        }
    }

    /**
     * Function to format number.
     *
     * @param array $config To set the next number.
     * @param int   $number To store the last number.
     *
     * @return string
     */
    private function doFormatNumber(array $config, int $number): string
    {
        $relation = '';
        if ($config['sh_rel_id'] !== null) {
            $relation = $this->loadRelationShortName($config['sh_rel_id']);
        }
        $data = [
            'prefix' => $config['sn_prefix'],
            'separator' => $config['sn_separator'],
            'postfix' => $config['sn_postfix'],
            'relation' => $relation,
            'year' => $config['sh_year'],
            'month' => $config['sh_month'],
            'number' => $number,
            'length_number' => (int)$config['sn_length'],
        ];
        if ($config['sn_format'] === 'B') {
            # PRE-YEARMONTHNUMBER-POST-REL
            return $this->doSecondFormat($data);
        }
        if ($config['sn_format'] === 'C') {
            # NUMBER-PRE-REL-POST-MONTH-YEAR
            return $this->doThirdFormat($data);
        }
        # PRE-REL-YEARMONTHNUMBER-POST
        return $this->doDefaultFormat($data);
    }

    /**
     * Function to get relation short name.
     *
     * @param int $relId To store the id of relation.
     *
     * @return string
     */
    private function loadRelationShortName(int $relId): string
    {
        $result = '';
        $query = 'SELECT rel_id, rel_short_name FROM relation where (rel_id = ' . $relId . ')';
        $sqlResults = DB::select($query);
        if (count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0])['rel_short_name'];
        }
        return $result;
    }


    /**
     * Function to do default format of serial number.
     *
     * PRE-REL-YEARMONTHNUMBER-POST
     *
     * @param array $data To set the data.
     *
     * @return string
     */
    private function doDefaultFormat(array $data): string
    {
        # Format the serial number.
        $number = $data['year'] . $data['month'];
        $number .= str_pad($data['number'], $data['length_number'], '0', STR_PAD_LEFT);
        $temp = [];
        if (empty($data['prefix']) === false) {
            $temp[] = $data['prefix'];
        }
        if (empty($data['relation']) === false) {
            $temp[] = $data['relation'];
        }
        $temp[] = $number;
        if (empty($data['postfix']) === false) {
            $temp[] = $data['postfix'];
        }
        return implode($data['separator'], $temp);
    }

    /**
     * Function to do default format of serial number.
     *
     * PRE-YEARMONTHNUMBER-POST-REL
     *
     * @param array $data To set the data.
     *
     * @return string
     */
    private function doSecondFormat(array $data): string
    {
        # Format the serial number.
        $number = $data['year'] . $data['month'];
        $number .= str_pad($data['number'], $data['length_number'], '0', STR_PAD_LEFT);
        $temp = [];
        if (empty($data['prefix']) === false) {
            $temp[] = $data['prefix'];
        }
        $temp[] = $number;
        if (empty($data['postfix']) === false) {
            $temp[] = $data['postfix'];
        }
        if (empty($data['relation']) === false) {
            $temp[] = $data['relation'];
        }
        return implode($data['separator'], $temp);
    }

    /**
     * Function to do default format of serial number.
     *
     * NUMBER-PRE-REL-POST-MONTH-YEAR
     *
     * @param array $data To set the data.
     *
     * @return string
     */
    private function doThirdFormat(array $data): string
    {
        # Format the serial number.
        $temp = [];
        $temp[] = str_pad($data['number'], $data['length_number'], '0', STR_PAD_LEFT);
        if (empty($data['prefix']) === false) {
            $temp[] = $data['prefix'];
        }
        if (empty($data['relation']) === false) {
            $temp[] = $data['relation'];
        }
        if (empty($data['postfix']) === false) {
            $temp[] = $data['postfix'];
        }
        if (empty($data['month']) === false && array_key_exists($data['month'], self::$MonthRoman) === true) {
            $temp[] = self::$MonthRoman[$data['month']];
        }
        if (empty($data['year']) === false) {
            $temp[] = mb_substr(date('Y'), 0, 2) . $data['year'];
        }
        return implode($data['separator'], $temp);
    }

}
