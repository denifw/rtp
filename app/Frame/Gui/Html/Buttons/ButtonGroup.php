<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 29/08/2018 C-Book
 */

namespace App\Frame\Gui\Html\Buttons;

use App\Frame\Gui\Html\ButtonInterface;

/**
 * Class to handle creation of button group
 *
 * @package    app
 * @subpackage Util\Gui\Buttons
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  29/08/2018 C-Book
 */
class ButtonGroup extends Button
{
    /**
     * Constructor to load when there is a new object created.
     *
     * @var string $Text
     */
    private $Text;

    /**
     * Constructor to load when there is a new object created.
     *
     * @var array $Buttons
     */
    private $Buttons = [];

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id    The id of the element.
     * @param string $value The value of the element.
     */
    public function __construct($id, $value)
    {
        parent::__construct($id, $value);
        $this->setTag('div');
        $this->addAttribute('id', $id);
        $this->addAttribute('class', 'btn-group pull-right px-2');
        $this->Text = $value;
    }

    /**
     * Set popup when button click.
     *
     * @param ButtonInterface $button To store the hyperlink.
     *
     * @return void
     */
    public function addButton(ButtonInterface $button): void
    {
        if ($button !== null) {
            if ($button instanceof Button) {
                $result = new HyperLink($button->getAttribute('id'), $button->getAttribute('value'), 'javascript:;');
                if ($button instanceof SubmitButton) {
                    $result .= $button->getJavascript();
                } else {
                    $result->addAttribute('onclick', $button->getAttribute('onclick'));
                }
            } else {
                $button->removeAttribute('class');
                $result = $button;
            }
            $this->Buttons[] = $result;
        }
    }


    /**
     * Returns the converted tag as a string if we call it with echo.
     *
     * @return string
     */
    public function __toString()
    {
        $content = $this->doGenerateContent();
        $this->setContent($content);
        return parent::__toString();
    }

    /**
     * Returns the converted tag as a string if we call it with echo.
     *
     * @return string
     */
    private function doGenerateContent(): string
    {
        $results = '';
        $results .= '<button type="button" class="btn btn-danger btn-sm">' . $this->Text . '</button>';
        $results .= '<button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">';
        $results .= '<span class="caret"></span>';
        $results .= '<span class="sr-only">Toggle Dropdown</span>';
        $results .= '</button>';
        $results .= '<ul class="dropdown-menu" role="menu">';
        foreach ($this->Buttons as $btn) {
            $results .= '<li>';
            $results .= $btn;
            $results .= '</li>';
        }
        $results .= '</ul>';
        $results .= '</div>';
        return $results;
    }

}
