<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\DashboardItem\Chart;

use App\Frame\Chart\Column;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractBaseChartDashboard;
/**
 *
 *
 * @package    app
 * @subpackage Model\DashboardItem\Chart
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class TestChart extends AbstractBaseChartDashboard
{
    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id The unique id from the chart.
     */
    public function __construct(string $id = '')
    {
        parent::__construct('TestChart', $id);
    }

    /**
     * Function to load the chart data.
     *
     * @return void
     */
    public function loadTable(): void
    {
        $this->Table = new Table($this->Id . 'Tbl');
        $this->Table->setHeaderRow([
            'no' => 'No',
            'name' => Trans::getWord('name'),
            'age' => 'Age'
        ]);
        $data = [
            ['name' => 'Dodi', 'age' => 70],
            [ 'name' => 'Susanti', 'age' => 98],
            [ 'name' => 'Anwar Januari', 'age' => 50],
        ];
        $this->Table->addRows($data);
    }

    /**
     * Function to load the chart data.
     *
     * @return void
     */
    public function loadChart(): void
    {
        $this->Chart = new Column($this->Id, 'Test Chart');
        $this->Chart->setTable($this->Table);
        $this->Chart->setXAxesColumn('name');
        $this->Chart->setYAxesColumn('age');
        $this->Chart->setXAxesLabel('Name');
        $this->Chart->setYAxesLabel('Age');
    }
}
