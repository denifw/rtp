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
        ]);

        $table->addRows($this->doPrepareData());

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
    private function doPrepareData(): array
    {
        $helper = new SqlHelper();
        $helper->addStringWhere('rtp_status', 'D', '<>');
        $data = RtPintarDao::loadData($helper);
        $results = [];
        foreach ($data as $row) {
            $results[] = $row;
        }


        return $results;

    }
}
