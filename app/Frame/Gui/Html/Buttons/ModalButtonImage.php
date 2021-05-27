<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Frame\Gui\Html\Buttons;

use App\Frame\Exceptions\Message;
use App\Frame\Gui\Html\Html;

/**
 *
 *
 * @package    app
 * @subpackage Frame\Gui\Html\Buttons
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class ModalButtonImage extends Html
{
    /**
     * Property to store the button id.
     *
     * @var string $Id
     */
    private $Id;
    /**
     * Property to store the image path.
     *
     * @var string $ImagePath
     */
    private $ImagePath;
    /**
     * Property to store the button id.
     *
     * @var string $ModalId
     */
    private $ModalId = '';
    /**
     * Property to store the route option.
     *
     * @var string $Route
     */
    private $Route = '';
    /**
     * Property to store the list of parameters.
     *
     * @var array $Parameters
     */
    private $Parameters = [];

    /**
     * Property to store the tile background for button.
     *
     * @var string $TileBackground
     */
    private $TileBackground = 'tile-stats';

    /**
     * Property to store the label for button.
     *
     * @var string $Label
     */
    private $Label;

    /**
     * Attribute to store the body of the portlet.
     *
     * @var string $ColumnGridClass
     */
    private $ColumnGridClass = 'col-xs-12';

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id The id of the element.
     * @param string $label The label of the element.
     * @param string $modalId The id of modal.
     */
    public function __construct(string $id, string $label, string $modalId)
    {
        $this->Id = $id;
        $this->Label = $label;
        if (empty($modalId) === false) {
            $this->ModalId = $modalId;
        } else {
            Message::throwMessage('Missing parameter modal id for modal button');
        }
    }

    /**
     * Converts tha main property to a string and pass it to a variable.
     *
     * @return string
     */
    public function __toString()
    {
        $result = $this->createButton();
        $result .= $this->getJavascript();

        return $result;
    }


    /**
     * Set set tile background.
     *
     * @param string $tile to store the tile value.
     *
     * @return self
     */
    public function setTileBackground(string $tile): self
    {
        if (empty($tile) === false) {
            $this->TileBackground = 'tile-stats ' . $tile;
        }
        return $this;
    }

    /**
     * Function to set image path.
     *
     * @param string $path to store the path value.
     *
     * @return self
     */
    public function setImagePath(string $path): self
    {
        if (empty($path) === false) {
            $this->ImagePath = $path;
        }
        return $this;
    }

    /**
     * Set modified able single select.
     *
     * @param string $route To set the path of the call back page.
     * @param string $callBackFunction To store the call back function.
     *
     * @return self
     */
    public function setEnableCallBack(string $route, string $callBackFunction): self
    {
        if (empty($route) === true) {
            Message::throwMessage('Invalid parameter route to enable callback on Modal Button.');
        }
        if (empty($callBackFunction) === true) {
            Message::throwMessage('Invalid parameter callback function to enable callback on Modal Button.');
        }
        $this->Route = $route;
        $this->addParameter('callBackFunction', $callBackFunction);
        return $this;
    }

    /**
     * Function to set add parameter.
     *
     * @param string $parId The unique id used in html.
     * @param string $parValue The value that the field will contain.
     *
     * @return void
     */
    public function addParameter(string $parId, $parValue = ''): void
    {
        if (empty($parId) === false) {
            if (array_key_exists($parId, $this->Parameters) === false) {
                $this->Parameters[$parId] = $parValue;
            } else {
                Message::throwMessage('Duplicate parameter id for single select with id = ' . $parId . '.');
            }
        } else {
            Message::throwMessage('Invalid empty parameter id for single select.');
        }
    }

    /**
     * Function to add the portlet attribute.
     *
     * @param integer $large To set the grid amount for a large screen.
     * @param integer $medium To set the grid amount for a medium screen.
     * @param integer $small To set the grid amount for a small screen.
     * @param integer $extraSmall To set the grid amount for a extra small screen.
     *
     * @return self
     */
    public function setGridDimension(int $large = 3, int $medium = 4, int $small = 6, $extraSmall = 12): self
    {
        $this->ColumnGridClass = 'col-lg-' . $large . ' col-md-' . $medium . ' col-sm-' . $small . ' col-xs-' . $extraSmall;
        return $this;
    }


    /**
     * Returns a string if the javascript must be loaded.
     *
     * @return string
     */
    protected function createButton(): string
    {
        $image = asset('images/image-not-found.jpg');
        if (empty($this->ImagePath) === false) {
            $image = $this->ImagePath;
        }
        $result = '<div class="' . $this->ColumnGridClass . '" id="' . $this->Id . '">';
        $result .= '<a href="javascript:;">';
        $result .= '<div class="' . $this->TileBackground . '" style="height: auto !important;">';
        $result .= '<div class="count" style="margin: 5px 0 !important; text-align: center;"><img style="max-height: 100px; width: auto" src="' . $image . '" alt="' . $this->Label . '"/></div>';
        $result .= '</div>';
        $result .= '</a>';
        $result .= '</div>';

        return $result;
    }

    /**
     * Returns a string if the javascript must be loaded.
     *
     * @return string
     */
    public function getJavascript(): string
    {
        $varJs = $this->Id . 'BtnImg';
        $javascript = '<script type="text/javascript">';
        $javascript .= 'var ' . $varJs . " = new App.ModalButton('" . $this->Id . "', '" . $this->ModalId . "', '" . $this->Route . "');";
        foreach ($this->Parameters as $key => $value) {
            $javascript .= $varJs . ".addParameter('" . $key . "', '" . $value . "');";
        }
        $javascript .= $varJs . '.create();';
        $javascript .= '</script>';

        return $javascript;
    }


}
