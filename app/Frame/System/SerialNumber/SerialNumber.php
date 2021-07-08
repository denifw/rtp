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
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Model\Dao\System\Access\SerialHistoryDao;
use App\Model\Dao\System\Access\SerialNumberDao;
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
     * @var string $SsId
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
     * @param string $ssId To store system settings id
     */
    public function __construct(string $ssId)
    {
        $this->SsId = $ssId;
    }


    /**
     * Function to load serial number.
     *
     * @param string $serialCode To store the code of serial number.
     * @param string $ofId To store the office id.
     * @param string $relId To store the relation id.
     *
     * @return string
     */
    public function loadNumber(string $serialCode, string $ofId = '', string $relId = ''): string
    {
        if (empty($serialCode) === true) {
            Message::throwMessage('Can not load serial number for empty module.');
        }
        $config = $this->loadConfig($serialCode, $ofId);
        if (empty($config) === true) {
            Message::throwMessage(Trans::getWord('noSerialNumberFound', 'message', '', ['code' => $serialCode]), 'ERROR');
        }
        $relation = null;
        if ($config['sn_relation'] === 'Y') {
            $relation = $relId;
            if (empty($relId) === true) {
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
     * @param string $ofId To store the office id.
     *
     * @return array
     */
    private function loadConfig(string $serialCode, string $ofId = ''): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('sc.sc_code', $serialCode, '=', 'low');
        $wheres[] = SqlHelper::generateStringCondition('sn.sn_ss_id', $this->SsId);
        if (empty($ofId) === false) {
            $wheres[] = '(' . SqlHelper::generateNullCondition('sn.sn_of_id') . ' OR ' . SqlHelper::generateStringCondition('sn.sn_of_id', $ofId) . ')';
        } else {
            $wheres[] = SqlHelper::generateNullCondition('sn.sn_of_id');
        }
        $wheres[] = SqlHelper::generateStringCondition('sn.sn_active', 'Y');
        $wheres[] = SqlHelper::generateStringCondition('sc.sc_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('sn.sn_deleted_on');
        $wheres[] = SqlHelper::generateNullCondition('sc.sc_deleted_on');
        $data = SerialNumberDao::loadData($wheres);
        $result = [];
        if (empty($data) === false) {
            $matchOffice = [];
            $matchDefault = [];
            foreach ($data as $row) {
                $match = 0;
                if (empty($ofId) === false && $row['sn_of_id'] === $ofId) {
                    $match++;
                }
                switch ($match) {
                    case 0:
                        $matchDefault = $row;
                        break;
                    case 1:
                        $matchOffice = $row;
                        break;
                }
            }
            if (empty($matchOffice) === false) {
                $result = $matchOffice;
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
        $wheres[] = SqlHelper::generateStringCondition('sh_sn_id', $config['sn_id']);
        if ($config['sh_year'] === null) {
            $wheres[] = SqlHelper::generateNullCondition('sh_year');
        } else {
            $wheres[] = SqlHelper::generateStringCondition('sh_year', $config['sh_year']);
        }
        if ($config['sh_month'] === null) {
            $wheres[] = SqlHelper::generateNullCondition('sh_month');
        } else {
            $wheres[] = SqlHelper::generateStringCondition('sh_month', $config['sh_month']);
        }
        if ($config['sh_rel_id'] === null) {
            $wheres[] = SqlHelper::generateNullCondition('sh_rel_id');
        } else {
            $wheres[] = SqlHelper::generateStringCondition('sh_rel_id', $config['sh_rel_id']);
        }
        $data = SerialHistoryDao::loadData($wheres, [], 1);
        $shNumber = 0;
        if (count($data) === 1) {
            $result = $data[0];
            $shNumber = (int)$result['sh_number'];
        }
        $this->storeSerialHistory($config, $shNumber);
    }

    /**
     * Function to store the serial history.
     *
     * @param array $config To set the next number.
     * @param int $lasNumber To store the last number.
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
//            Message::throwMessage($e->getMessage(), 'ERROR');
            $this->storeSerialHistory($config, $lasNumber);
        }
    }

    /**
     * Function to format number.
     *
     * @param array $config To set the next number.
     * @param int $number To store the last number.
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
     * @param string $relId To store the id of relation.
     *
     * @return string
     */
    private function loadRelationShortName(string $relId): string
    {
        $result = '';
        $query = 'SELECT rel_id, rel_short_name FROM relation where ' . SqlHelper::generateStringCondition('rel_id', $relId);
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
