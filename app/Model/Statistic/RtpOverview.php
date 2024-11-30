<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Statistic;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractStatisticModel;
use App\Model\Dao\RtPintarDao;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class RtpOverview extends AbstractStatisticModel
{
    /**
     * Property to store date time object
     *
     * @param array $Index
     */
    private $Index = [];

    /**
     * Property to store date time object
     *
     * @param array $ColumnIndex
     */
    private $ColumnIndex = [];

    /**
     * Property to store date time object
     *
     * @param array $ColVal
     */
    private $ColVal = [];

    /**
     * Property to store date time object
     *
     * @param int $StartYear
     */
    private static $StartYear = 2022;

    /**
     * Property to store date time object
     *
     * @param int $StartMonth
     */
    private static $StartMonth = 9;

    /**
     * Property to store date time object
     *
     * @param DateTimeParser $DtParser
     */
    private $Month = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
    ];
    /**
     * Property to store date time object
     *
     * @param DateTimeParser $DtParser
     */
    private $Exceptions = [
        '12739311135686203309' => '12-2022',
        '12739324735686229640' => '1-2023',
        '12739282633274163948' => '9-2022',
        '12739282633265203314' => '9-2022',
        '12739282633274166330' => '10-2022',
        '12739282633274172178' => '11-2022',
        '12739311133274203310' => '12-2022',
        '12739324633274229639' => '1-2023',
    ];

    /**
     * GoodsDamageType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'rtp');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    private function setIndexColumn(): void
    {
        if ($this->isValidParameter('year') === true) {
            $currentYear = $this->getIntParameter('year');
            if ($currentYear === self::$StartYear) {
                $start = self::$StartMonth;
            } else {
                $start = 1;
            }
            $end = 12;
            for ($m = $start; $m <= $end; $m++) {
                $key = $m . '-' . $currentYear;
                $this->ColumnIndex[] = $key;
                $this->ColVal[$key] = $this->Month[$m] . ' ' . $currentYear;
            }
        } else {
            $currentYear = (int)date('Y');
            for ($i = self::$StartYear; $i <= $currentYear; $i++) {
                if ($i === self::$StartYear) {
                    $start = self::$StartMonth;
                } else {
                    $start = 1;
                }
                $end = 12;
                for ($m = $start; $m <= $end; $m++) {
                    $key = $m . '-' . $i;
                    $this->ColumnIndex[] = $key;
                    $this->ColVal[$key] = $this->Month[$m] . ' ' . $i;
                }
            }
        }
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $block = $this->Field->getSelect('block', $this->getIntParameter('block'));
        $block->addOption('H09', 1);
        $block->addOption('H10', 2);
        $block->addOption('H11', 3);

        $year = $this->Field->getSelect('year', $this->getIntParameter('year'));
        $today = (int)date('Y');
        for ($i = self::$StartYear; $i <= $today; $i++) {
            $year->addOption($i, $i);
        }

        $paid = $this->Field->getSelect('paid', $this->getStringParameter('paid'));
        $paid->addOption('Yes', 'Y');
        $paid->addOption('No', 'N');

        $this->StatisticForm->addField('Block', $block);
        $this->StatisticForm->addField('Year', $year);
        $this->StatisticForm->addField('Paid', $paid);
    }

    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadViews(): void
    {
        $this->Index = [];
        $this->setIndexColumn();
        $this->addContent('result', $this->getResultPortlet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
    }


    /**
     * Function to get the stock card table.
     *
     * @return Portlet
     */
    private function getResultPortlet(): Portlet
    {
        $table = new Table('ResTbl');
        $table->setHeaderRow([
            'rtp_unit' => 'Unit',
            'rtp_system_unit' => 'RTP Unit',
            'rtp_pic' => 'Name',
        ]);

        $data = $this->loadData();
        $rows = [];
        $i = 0;
        foreach ($data as $row) {
            foreach ($this->Index as $key) {
                if (array_key_exists($key, $row) === true) {
                    $amount = (float)$row[$key];
                    if ($amount < 100.0) {
                        $table->addCellAttribute('rtp_system_unit', $i, 'style', 'background-color: #FF0000; color: #000;');
                        if ($amount === 0.0) {
                            $table->addCellAttribute($key, $i, 'style', 'background-color: #FFFF00; color: #000;');
                        } else {
                            $table->addCellAttribute($key, $i, 'style', 'background-color: #FF0000; color: #000;');
                        }
//                        $row[$key] = null;
                    }
                } else {
                    $row[$key] = null;
                    $table->addCellAttribute($key, $i, 'style', 'background-color: #000000;');
                }
            }
            $rows[] = $row;
            $i++;
        }
        $table->addRows($rows);
        foreach ($this->ColumnIndex as $key) {
            if (in_array($key, $this->Index, true) === true) {
                $table->addColumnAtTheEnd($key, $this->ColVal[$key]);
                $table->setColumnType($key, 'float');
                $table->setFooterType($key, 'SUM');
            }
        }
        $table->addColumnAtTheEnd('rtp_total', 'Total');
        $table->setColumnType('rtp_total', 'float');
        $table->setFooterType('rtp_total', 'SUM');


        $portlet = new Portlet('ResPtl', 'Results');
        $portlet->addTable($table);
        $this->addDatas('ResPtl', $portlet);

        return $portlet;
    }

    /**
     * Get query to get the quotation data.
     *
     *
     * @return array
     */
    private function loadData(): array
    {
        $helper = new SqlHelper();
        $helper->addStringWhere('rtp_canceled', 'N');
        $helper->addStringWhere('rtp_paid', $this->getStringParameter('paid'));
        $helper->addNumericWhere('rtp_order', $this->getIntParameter('block'));
        $helper->addNumericWhere('rtp_year', $this->getIntParameter('year'));
        $data = RtPintarDao::loadData($helper);
        $results = [];
        $tempUnit = [];
        foreach ($data as $row) {
            $tempKey = $row['rtp_system_unit'];
            $keyColumn = $row['rtp_month'] . '-' . $row['rtp_year'];
            if (in_array($keyColumn, $this->Index, true) === false) {
                $this->Index[] = $keyColumn;
            }
            $amount = (float)$row['rtp_amount'];
            if ($row['rtp_paid'] === 'N') {
                if ($amount > 50000) {
                    $amount = 1.0;
                } else {
                    $amount = 0.0;
                }
            }
            if (in_array($tempKey, $tempUnit, true) === false) {
                $tempUnit[] = $tempKey;
                $temp = [
                    'rtp_unit' => $row['rtp_unit'],
                    'rtp_system_unit' => $row['rtp_system_unit'],
                    'rtp_pic' => $row['rtp_pic'],
                    'rtp_paid' => $row['rtp_paid'],
                    'rtp_total' => $amount,
                ];
                $temp[$keyColumn] = $amount;
                $results[] = $temp;
            } else {
                $index = array_search($tempKey, $tempUnit, true);
                $results[$index]['rtp_total'] += $amount;
                if (array_key_exists($keyColumn, $results[$index]) === false) {
                    $results[$index][$keyColumn] = $amount;
                } else {
                    $results[$index][$keyColumn] += $amount;
                }
            }
        }
        return $results;

    }
}
