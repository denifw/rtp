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
     * @param DateTimeParser $DtParser
     */
    private $DtParser;

    /**
     * Property to store date time object
     *
     * @param DateTimeParser $DtParser
     */
    private $Index = [
        '9-2022',
        '10-2022',
        '11-2022',
        '12-2022',
        '1-2023',
        '2-2023',
        '3-2023',
        '4-2023',
        '5-2023',
        '6-2023',
        '7-2023',
        '8-2023',
        '9-2023',
    ];
    /**
     * Property to store date time object
     *
     * @param DateTimeParser $DtParser
     */
    private $Exceptions = [
        'H-H09-10',
        'H-H09-16',
        'H-H10-06',
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
        $this->DtParser = new DateTimeParser();
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
            'rtp_number' => 'Unit',
            'rtp_contact' => 'PIC',
            '9-2022' => 'Sep 2022',
            '10-2022' => 'Okt 2022',
            '11-2022' => 'Nov 2022',
            '12-2022' => 'Des 2022',
            '1-2023' => 'Jan 2023',
            '2-2023' => 'Feb 2023',
            '3-2023' => 'Mar 2023',
            '4-2023' => 'Apr 2023',
            '5-2023' => 'Mei 2023',
            '6-2023' => 'Jun 2023',
            '7-2023' => 'Jul 2023',
            '8-2023' => 'Agu 2023',
            '9-2023' => 'Sep 2023',
        ]);

        $table->addRows($this->doPrepareData($table));
        foreach ($this->Index as $in) {
            $table->setColumnType($in, 'float');
            $table->setFooterType($in, 'SUM');
        }
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
        $helper = new SqlHelper();
        $helper->addStringWhere('rtp_status', 'Y');
        $data = RtPintarDao::loadData($helper);
        $results = [];
        $tempUnit = [];
        foreach ($data as $row) {
            $keyColumn = $row['rtp_month'] . '-' . $row['rtp_year'];
            $unit = $row['rtp_block'] . '-' . $row['rtp_number'];
            if (in_array($unit, $this->Exceptions, true) === true) {
                $keyColumnIndex = array_search($keyColumn, $this->Index, true);
                $keyColumn = $this->Index[$keyColumnIndex - 1];
            }
            if (in_array($unit, $tempUnit, true) === false) {
                $temp = [
                    'rtp_number' => $row['rtp_number'],
                    'rtp_contact' => $row['rtp_contact'],
                    $keyColumn => (float)$row['rtp_amount'],
                ];
                $tempUnit[] = $unit;
                $results[] = $temp;
            } else {
                $index = array_search($unit, $tempUnit, true);
                if(array_key_exists($keyColumn, $results[$index]) === false) {
                    $results[$index][$keyColumn] = (float)$row['rtp_amount'];
                } else {
                    $results[$index][$keyColumn] += (float)$row['rtp_amount'];
                }
            }
        }
        return $results;

    }
}
