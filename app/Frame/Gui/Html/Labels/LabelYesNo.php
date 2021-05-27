<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Frame\Gui\Html\Labels;

use App\Frame\Formatter\Trans;

/**
 * Class to handle html label aqua
 *
 * @package    app
 * @subpackage Frame\Gui\Html\Labels
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class LabelYesNo extends Label
{
    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $yesNo The test of the element.
     */
    public function __construct($yesNo)
    {
        $type = 'warning';
        $text = Trans::getWord('no');
        if ($yesNo === 'Y') {
            $text = Trans::getWord('yes');
            $type = 'info';
        }
        parent::__construct($text, $type);
    }

}
