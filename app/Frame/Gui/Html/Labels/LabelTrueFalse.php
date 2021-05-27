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
use App\Frame\Gui\Icon;

/**
 * Class to handle html label aqua
 *
 * @package    app
 * @subpackage Frame\Gui\Html\Labels
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class LabelTrueFalse extends Label
{
    /**
     * Constructor to load when there is a new object created.
     *
     * @param bool $value The test of the element.
     */
    public function __construct(bool $value)
    {
        $type = 'danger';
        $icon = Icon::Close;
        if ($value === true) {
            $icon = Icon::Check;
            $type = 'info';
        }
        $text = '<i class="' . $icon . '"></i>';
        parent::__construct($text, $type);
    }

}
