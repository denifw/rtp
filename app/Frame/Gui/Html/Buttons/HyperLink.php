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

/**
 * Class to hadle creation of hyperlink
 *
 * @package    app
 * @subpackage Util\Gui\Html\Buttons
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  29/08/2018 C-Book
 */
class HyperLink extends Button
{

    /**
     * Property to trigger is it hyperlink action or popup
     *
     * @var bool $IsLink
     */
    private $IsLink = true;
    /**
     * Property to store the url
     *
     * @var string $Url
     */
    private $Url = '';

    /**
     * Property to store position style
     *
     * @var bool $ButtonView
     */
    private $ButtonView = false;

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id The id of the element.
     * @param string $value The value of the element.
     * @param string $url The link address.
     * @param bool $isLink The link address.
     */
    public function __construct($id, $value, $url, $isLink = true)
    {
        parent::__construct($id, $value);
        $this->Url = $url;
        $this->IsLink = $isLink;

    }

    /**
     * Function to set view as a button
     * @param bool $enable To store the trigger
     * @return self
     */
    public function viewAsButton(bool $enable = true): self
    {
        $this->ButtonView = $enable;
        return $this;
    }

    /**
     * Function to set view as hyperlink
     *
     * @return void
     */
    public function viewAsHyperlink(): void
    {
        $this->ButtonView = false;
    }

    public function __toString()
    {
        $this->setTag('a');
        if ($this->ButtonView === false) {
            $this->DefaultClass = '';
            $this->SizeStyle = '';
        } else {
            $this->DefaultClass = 'btn';
        }
        if ($this->IsLink === false) {
            $this->addAttribute('href', 'javascript:;');
            $this->addAttribute('onclick', "App.popup('" . $this->Url . "')");
        } else {
            $this->addAttribute('href', $this->Url);
        }
        return parent::__toString();
    }
}
