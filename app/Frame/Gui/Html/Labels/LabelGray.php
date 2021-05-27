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

/**
 * Class to handle html label gray
 *
 * @package    app
 * @subpackage Frame\Gui\Html\Labels
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class LabelGray extends Label
{
    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $text The test of the element.
     */
    public function __construct($text)
    {
        parent::__construct($text, 'gray');
    }

}
