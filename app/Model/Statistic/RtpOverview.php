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
     * @param array $ColVal
     */
    private $ColVal = [];

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
        $this->setIndexColumn();
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    private function setIndexColumn(): void
    {
        $startYear = 2022;
        $startMonth = 9;
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m') -1;
        for ($i = $startYear; $i <= $currentYear; $i++) {
            if ($i === $startYear) {
                $start = $startMonth;
                $end = 12;
            } else if ($i === $currentYear) {
                $start = 1;
                $end = $currentMonth;
            } else {
                $start = 1;
                $end = 12;
            }
            for ($m = $start; $m <= $end; $m++) {
                $key = $m . '-' . $i;
                $this->Index[] = $key;
                $this->ColVal[$key] = $this->Month[$m] . ' ' . $i;
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
    }

    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadViews(): void
    {
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
        ]);
        foreach ($this->Index as $key) {
            $table->addColumnAtTheEnd($key, $this->ColVal[$key]);
            $table->setColumnType($key, 'float');
            $table->setFooterType($key, 'SUM');
        }

        $table->addRows($this->doPrepareData($table));
        $portlet = new Portlet('ResPtl', 'Results');
        $portlet->addTable($table);
        $this->addDatas('ResPtl', $portlet);

        return $portlet;
    }

    /**
     * Get query to get the quotation data.
     *
     *
     * @param Table $table
     * @return array
     */
    private function doPrepareData(Table $table): array
    {
        $data = $this->loadData();
        $results = [];
        $i = 0;
        foreach ($data as $row) {
            $rtp = [
                'rtp_unit' => $row['rtp_unit'],
            ];

            foreach ($this->Index as $key) {
                if (array_key_exists($key, $row) === true) {
                    $val = $row[$key];
                    $rtp[$key] = $val['amount'];
                    if($val['status'] === 'N') {
                        $table->addCellAttribute($key, $i, 'style', 'background-color: #FF0000;');
//                    } else if($val['type'] === 'A') {
//                        $table->addCellAttribute($key, $i, 'style', 'background-color: #00FF00;');
                    }
                } else {
                    $rtp[$key] = null;
                    $table->addCellAttribute($key, $i, 'style', 'background-color: #000000;');
                }
            }
            $results[] = $rtp;
            $i++;
        }
        return $results;

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
//        $helper->addStringWhere('rtp_status', 'Y');
//        $helper->addStringWhere('rtp_type', 'M');
        $data = RtPintarDao::loadData($helper);
        $results = [];
        $tempUnit = [];
        foreach ($data as $row) {
            $code = $row['rtp_code'];
            $keyColumn = $row['rtp_month'] . '-' . $row['rtp_year'];
            if (array_key_exists($code, $this->Exceptions) === true) {
                $keyColumn = $this->Exceptions[$code];
            }
            $unit = $row['rtp_unit'];
            if (in_array($unit, $tempUnit, true) === false) {
                $tempUnit[] = $unit;
                $results[] = [
                    'rtp_unit' => $unit,
                ];
            }
            $index = array_search($unit, $tempUnit, true);
            $temp = $results[$index];
//            if (array_key_exists($keyColumn, $temp) === false) {
//                $temp[$keyColumn] = [];
//            }
            $temp[$keyColumn] = [
                'code' => $row['rtp_code'],
                'amount' => (float)$row['rtp_amount'],
                'status' => $row['rtp_status'],
                'type' => $row['rtp_type'],
            ];
            $results[$index] = $temp;


        }
        return $results;

    }
}
