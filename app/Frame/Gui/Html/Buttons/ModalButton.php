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

use App\Frame\Exceptions\Message;

/**
 * Class to control the creation of modal confirmation
 *
 * @package    app
 * @subpackage Util\Gui\Buttons
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  29/08/2018 C-Book
 */
class ModalButton extends Button
{
    /**
     * Property to store the button id.
     *
     * @var string $Id
     */
    private $Id;
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
     * Constructor to load when there is a new object created.
     *
     * @param string $id      The id of the element.
     * @param string $value   The value of the element.
     * @param string $modalId The id of modal.
     */
    public function __construct($id, $value, $modalId)
    {
        parent::__construct($id, $value);
        $this->Id = $id;
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
        $result = parent::__toString();
        $result .= $this->getJavascript();

        return $result;
    }


    /**
     * Set modified able single select.
     *
     * @param string $route            To set the path of the call back page.
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
     * @param string $parId    The unique id used in html.
     * @param string $parValue The value that the field will contain.
     *
     * @return void
     */
    public function addParameter($parId, $parValue = ''): void
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
     * Returns a string if the javascript must be loaded.
     *
     * @return string
     */
    public function getJavascript(): string
    {
        $varJs = $this->Id . 'Button';
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
