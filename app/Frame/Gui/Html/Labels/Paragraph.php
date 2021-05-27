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
 * Class to handle html paragraph
 *
 * @package    app
 * @subpackage Frame\Gui\Html\Labels
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Paragraph extends Html
{

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $text The test of the element.
     */
    public function __construct($text)
    {
        $this->setTag('p');
        $this->setContent($text);
    }

    /**
     * function to set paragraph as label large
     *
     * @return self.
     */
    public function setAsLabelLarge(): self
    {
        $this->addAttribute('class', 'label-large');
        return $this;
    }

    /**
     * function to set content as center align
     *
     * @return self.
     */
    public function setAlignCenter(): self
    {
        $this->addAttribute('style', 'text-align: center;');
        return $this;
    }


}
