<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Frame\Gui\Html;

use App\Frame\Exceptions\Message;

/**
 * Class to create the chart contatiner.
 *
 * @package    app
 * @subpackage Frame\Gui\Html
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class ChartContainer extends Html
{
    /**
     * Property to store the chart data.
     *
     * @var \App\Frame\Chart\AbstractBaseChart
     */
    private $Chart;
    /**
     * Property to store the id of the container.
     *
     * @var string $Id
     */
    private $Id;

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id The id of the element.
     */
    public function __construct($id)
    {
        if (empty($id) === true) {
            Message::throwMessage('Invalid ID for the chart container.');
        }
        $this->Id = $id;
        $this->setTag('div');
        $this->addAttribute('type', 'text');
        $this->addAttribute('id', $id);
//        $this->addAttribute('class', 'form-control input-sm');
    }


    /**
     * Function to set the chart into container.
     *
     * @param \App\Frame\Chart\AbstractBaseChart $chart To store the chart object.
     *
     * @return void
     */
    public function setChart(\App\Frame\Chart\AbstractBaseChart $chart): void
    {
        $this->Chart = $chart;
    }

    /**
     * Converts tha main property to a string and pass it to a variable.
     *
     * @return string
     */
    public function __toString()
    {
        $content = parent::__toString();
        if ($this->Chart !== null) {
            $content .= $this->Chart->renderChart($this->Id);
        }

        return $content;
    }

}
