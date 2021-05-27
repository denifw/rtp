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

use App\Frame\Exceptions\Message;
use App\Frame\Gui\Html\Html;

/**
 * Class to handle html Label
 *
 * @package    app
 * @subpackage Frame\Gui\Html\Labels
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Label extends Html
{

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $type The type of the element.
     * @param string $text The test of the element.
     */
    public function __construct($text, $type = 'primary')
    {
        $this->setTag('span');
        $this->addAttribute('class', 'label label-' . mb_strtolower($type));
        $this->setContent($text);
    }
}
