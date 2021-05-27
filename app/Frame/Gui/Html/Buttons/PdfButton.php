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
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Fields\Select;
use App\Frame\Gui\Modal;
use App\Frame\System\Validation;
use App\Model\Dao\System\Document\DocumentTemplateDao;

/**
 * Class to control the creation of modal confirmation
 *
 * @package    app
 * @subpackage Util\Gui\Buttons
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  29/08/2018 C-Book
 */
class PdfButton extends Button
{
    /**
     * Property to store the button id.
     *
     * @var string $Id
     */
    private $Id;
    /**
     * Property to store the document template type.
     *
     * @var string $DttCode
     */
    private $DttCode;
    /**
     * Property to store the route option.
     *
     * @var string $Route
     */
    private static $Route = '/documentPdf';
    /**
     * Property to store the list of parameters.
     *
     * @var array $Parameters
     */
    private $Parameters = [];
    /**
     * Property to store the template data.
     *
     * @var array $Templates
     */
    private $Templates = [];
    /**
     * Property to store the template data.
     *
     * @var string $Label
     */
    private $Label;

    /**
     * Property to store the trigger to enable Delete button.
     *
     * @var bool $EnableModal ;
     */
    private $EnableModal = true;

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id      The id of the element.
     * @param string $value   The value of the element.
     * @param string $dttCode The value of the element.
     */
    public function __construct($id, $value, $dttCode)
    {
        $this->Id = $id;
        $this->Label = $value;
        parent::__construct($id, $value);
        if (empty($dttCode) === true) {
            Message::throwMessage('Invalid document template type code for PDF button.');
        }
        $this->DttCode = mb_strtolower($dttCode);
        $this->loadTemplates();
        if (count($this->Templates) === 1) {
            $this->addParameter('path', $this->Templates[0]['dt_path']);
            $this->setTag('a');
            $this->addAttribute('href', 'javascript:;');
        }

    }

    /**
     * Converts tha main property to a string and pass it to a variable.
     *
     * @return string
     */
    public function __toString()
    {
        if (count($this->Templates) === 1) {
            $this->addAttribute('onclick', "App.popup('" . $this->loadUrl() . "')");
            return parent::__toString();
        }

        $result = parent::__toString();
        if ($this->isModalEnabled()) {
            $result .= $this->getModal()->createModal();
        }
        $result .= $this->getJavascript();

        return $result;
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
                Message::throwMessage('Duplicate parameter id for PDF button with id = ' . $parId . '.');
            }
        } else {
            Message::throwMessage('Invalid empty parameter id for PDF Button.');
        }
    }


    /**
     * function Set disable modal view.
     *
     * @param boolean $disable The value to set enable the button.
     *
     * @return void
     */
    public function setDisableModal(bool $disable = true): void
    {
        $this->EnableModal = !$disable;
    }

    /**
     * function Set disable modal view.
     *
     *
     * @return bool
     */
    public function isModalEnabled(): bool
    {
        return $this->EnableModal;
    }

    /**
     * Crate the complete auto suggest field.
     *
     * @return Modal
     */
    public function getModal(): Modal
    {
        $mdl = new Modal($this->Id . '_mdl', $this->Label);
        $fieldSet = new FieldSet(new Validation());
        $fieldSet->setGridDimension();
        $templateField = new Select($this->Id . '_select', '');
        $templateField->addOptions($this->Templates, 'dt_description', 'dt_path');
        $templateField->setPleaseSelect();
        $fieldSet->addField(Trans::getWord('templates'), $templateField);
        $mdl->addFieldSet($fieldSet);
        $mdl->setBtnOkName($this->Label);
        $mdl->setDisableJavascript();

        return $mdl;
    }


    /**
     * Returns a string if the javascript must be loaded.
     *
     * @return string
     */
    private function getJavascript(): string
    {
        $varJs = $this->Id . 'Btn';
        $javascript = '<script type="text/javascript">';
        $javascript .= 'var ' . $varJs . " = new App.PdfButton('" . $this->Id . "', '" . self::$Route . "');";
        foreach ($this->Parameters as $key => $value) {
            $javascript .= $varJs . ".addParameter('" . $key . "', '" . $value . "');";
        }
        $javascript .= $varJs . '.create();';
        $javascript .= '</script>';

        return $javascript;
    }

    /**
     * Function to load the template.
     *
     * @return void
     */
    private function loadTemplates(): void
    {
        $wheres = [];
        $wheres[] = "(dtt.dtt_code = '" . $this->DttCode . "')";
        $wheres[] = '(dt.dt_deleted_on IS NULL)';
        $wheres[] = "(dt.dt_active = 'Y')";
        $this->Templates = DocumentTemplateDao::loadData($wheres);
        if (empty($this->Templates) === true) {
            Message::throwMessage('No document template found for pdf button with id ' . $this->Id . '.');
        }
    }

    /**
     * Function to load the template.
     *
     * @return string
     */
    private function loadUrl(): string
    {
        $params = [];
        foreach ($this->Parameters as $par => $val) {
            $params[] = $par . '=' . $val;
        }

        return url(self::$Route . '?' . implode('&', $params));
    }

}
