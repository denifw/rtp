<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Gui\Templates;

use App\Frame\Exceptions\Message;
use App\Frame\Gui\Html\ButtonInterface;

/**
 * Abstract class to manage the creation of the template.
 *
 * @package    app
 * @subpackage Frame\Gui\Templates
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
abstract class AbstractTemplate
{
    /**
     * Attribute to store the id
     *
     * @var string $Id
     */
    public $Id = '';
    /**
     * Attribute to store all the data for the template.
     *
     * @var array $Data
     */
    protected $Data = [];
    /**
     * Attribute to store all the grid column settings.
     *
     * @var array $GridColumns
     */
    protected $GridColumns = [];
    /**
     * Attribute to store all the inline style for the template.
     *
     * @var string $Height
     */
    protected $Height = '';

    /**
     * Attribute to store button action for portlet.
     *
     * @var array $Buttons
     */
    private $Buttons = [];

    /**
     * Attribute to store button id action for portlet.
     *
     * @var array $ButtonIds
     */
    private $ButtonIds = [];

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id To set the id of the template.
     */
    public function __construct(string $id = '')
    {
        $this->Id = $id;
        $this->setGridDimension();
    }

    /**
     * Function to create the view.
     *
     * @return string
     */
    abstract protected function createTemplate(): string;

    /**
     * Function to get content body of widget,load by ajax.
     *
     * @return string
     */
    abstract public function getContentBody(): string;

    /**
     * Function to set the data.
     *
     * @param array $data To store the data.
     *
     * @return void
     */
    public function setData(array $data): void
    {
        if (empty($data) === true) {
            Message::throwMessage('Invalid parameter to generate template.');
        }
        $this->Data = $data;
    }

    /**
     * Function to add the portlet attribute.
     *
     * @param integer $large      To set the grid amount for a large screen.
     * @param integer $medium     To set the grid amount for a medium screen.
     * @param integer $small      To set the grid amount for a small screen.
     * @param integer $extraSmall To set the grid amount for a extra small screen.
     *
     * @return void
     */
    public function setGridDimension(int $large = 3, int $medium = 4, int $small = 6, $extraSmall = 12): void
    {
        $this->GridColumns = [
            'lg' => $large,
            'md' => $medium,
            'sm' => $small,
            'xs' => $extraSmall,
        ];
    }

    /**
     * Function to add the portlet attribute.
     *
     * @param integer $height To set the height number data.
     * @param string  $uom    To set unit of measure height.
     *
     * @return void
     */
    public function setHeight(int $height, string $uom = 'px'): void
    {
        if (empty($uom) === true) {
            $uom = 'px';
        }
        if ($height !== null) {
            $this->Height = $height . $uom;
        }
    }


    /**
     * Function to check if the data is valid or not.
     *
     * @param string $key To store the key of the data.
     *
     * @return bool
     */
    protected function isValidData(string $key): bool
    {
        $valid = false;
        if (empty($key) === false && array_key_exists($key, $this->Data) === true && $this->Data[$key] !== null && $this->Data[$key] !== '') {
            $valid = true;
        }

        return $valid;
    }

    /**
     * Function to check if the data is valid or not.
     *
     * @param string $key To store the key of the data.
     *
     * @return bool
     */
    protected function isExistData(string $key): bool
    {
        $valid = false;
        if (empty($key) === false && array_key_exists($key, $this->Data) === true) {
            $valid = true;
        }

        return $valid;
    }

    /**
     * Function to get the value data.
     *
     * @param string $key To store the key of the data.
     *
     * @return mixed
     */
    protected function getData(string $key)
    {
        if ($this->isValidData($key) === false) {
            Message::throwMessage('No data found for key ' . $key . ' inside the template data.');
        }

        return $this->Data[$key];
    }

    /**
     * Function to convert class into string data.
     *
     * @return string
     */
    public function createView(): string
    {
        if (empty($this->Height) === true) {
            Message::throwMessage('Missing height attribute for the template container.');
        }
//        $this->createTemplate();
//        $result = '<div class="template-container ' . $this->getGridClass() . '" style="margin-bottom: 10px; ' . $this->getHeightStyle() . '">';
        $result = '<div id = "' . $this->Id . '" class="template-container ' . $this->getGridClass() . '">';
        $result .= $this->createTemplate();
        $result .= '</div>';

        return $result;
    }

    /**
     * Function to get the container template.
     *
     * @return string
     */
    public function getContainer(): string
    {
        if (empty($this->Height) === true) {
            Message::throwMessage('Missing height attribute for the template container.');
        }
        if (empty($this->Id) === true) {
            Message::throwMessage('Invalid id for template container.');
        }

        $result = '<div id="' . $this->Id . 'grid" class="template-container ' . $this->getGridClass() . '">';
        $result .= '<div id="' . $this->Id . 'panel" class="x_panel_widget tile-stats" style="' . $this->getHeightStyle() . '">';
        $result .= '<div class="x_title_widget">';
        $result .= '<h5 class="title pull-left" id="' . $this->Id . 'title"></h5>';
        $result .= $this->getButtonAction();
        $result .= '<div class="clearfix"></div>';
        $result .= '</div>';
        $result .= '<div id = "' . $this->Id . '">';
        $result .= '</div>';
        $result .= '</div>';
        $result .= '</div>';

        return $result;
    }

    /**
     * Function to get the view as json for ajax response.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->createTemplate();
    }

    /**
     * Function to add button to the portlet.
     *
     * @param \App\Frame\Gui\Html\ButtonInterface $button To set the button field.
     *
     * @return void
     */
    public function addButton($button): void
    {
        if ($button instanceof ButtonInterface) {
            $buttonId = $button->getAttribute('id');
            if (array_key_exists($buttonId, $this->Buttons) === false) {
                $this->Buttons[$buttonId] = $button;
                $this->ButtonIds[] = $buttonId;
            } else {
                Message::throwMessage('Button with id ' . $buttonId . ' already added to the portlet.');
            }
        } else {
            Message::throwMessage('Button must be instance of Button Interface');
        }
    }

    /**
     * Function to get portlet action.
     *
     * @return string
     */
    public function getButtonAction(): string
    {
        $result = ' ';
        if (empty($this->ButtonIds) === false) {
            foreach ($this->ButtonIds as $buttonId) {
                if (array_key_exists($buttonId, $this->Buttons) === true) {
                    $result .= $this->Buttons[$buttonId];
                }
            }
        }

        return $result;
    }

    /**
     * Function to get the grid class.
     *
     * @return string
     */
    protected function getGridClass(): string
    {
        return 'col-lg-' . $this->GridColumns['lg'] . ' col-md-' . $this->GridColumns['md'] . ' col-sm-' . $this->GridColumns['sm'] . ' col-xs-' . $this->GridColumns['xs'];
    }


    /**
     * Function to get the height style.
     *
     * @return string
     */
    protected function getHeightStyle(): string
    {
        return 'height: ' . $this->Height . ' !important;';
    }


}
