<?php
/**
 * Contains code written by the Republik Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Republik
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2017 Republik
 */

namespace App\Frame\Gui;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;

/**
 *
 *
 * @package    app
 * @subpackage Util\Gui
 * @author     Deni Firdaus Waruwu <deni.fw@optilog-global.com>
 * @copyright  2017 Republik
 */
class Modal
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
     * @var string $FormId ;
     */
    private $FormId = '';
    /**
     * Property to store the button id.
     *
     * @var string $ActionName
     */
    private $ActionName = '';
    /**
     * Property to store the list of parameters.
     *
     * @var boolean $ShowOnLoad
     */
    private $ShowOnLoad = false;
    /**
     * Attribute title of the portlet
     *
     * @var string $Title
     */
    private $Title;

    /**
     * Attribute to store the modal attribute of the portlet.
     *
     * @var array $ModalAttributes
     */
    private $ModalAttributes = [];

    /**
     * Attribute to store the header attribute of the portlet.
     *
     * @var array $HeaderAttributes
     */
    private $HeaderAttributes = [];

    /**
     * Attribute to store the body attribute of the portlet.
     *
     * @var array $BodyAttributes
     */
    private $BodyAttributes = [];

    /**
     * Attribute to store the body of the portlet.
     *
     * @var array $Body
     */
    private $Body = [];

    /**
     * Attribute to disable enable button
     *
     * @var bool $DisableBtnOk
     */
    private $DisableBtnOk = false;

    /**
     * Attribute to store button name
     *
     * @var string $BtnOkName
     */
    private $BtnOkName = '';

    /**
     * Attribute to store trigger to enable javascript
     *
     * @var boolean $EnableJavaScript
     */
    private $EnableJavaScript = true;

    /**
     * Constructor for the portlet  class.
     *
     * @param string $modalId    To store the portlet id.
     * @param string $modalTitle To store the title of the table.
     */
    public function __construct(string $modalId, string $modalTitle = '')
    {
        if (empty($modalId) === false) {
            $this->Id = $modalId;
            $this->addModalAttribute('id', $modalId);
        } else {
            Message::throwMessage('Invalid id for modal ');
        }
        $this->addModalAttribute('class', 'modal fade');
        $this->addModalAttribute('tabindex', '-1');
        $this->addModalAttribute('role', 'dialog');
        $this->addModalAttribute('aria-hidden', 'true');
        $this->addModalAttribute('data-backdrop', 'static');
        $this->addModalAttribute('data-keyboard', 'false');
        if (empty($modalTitle) === false) {
            $this->Title = $modalTitle;
            $this->addHeaderAttribute('class', 'modal-header');
        }
        $this->addBodyAttribute('class', 'modal-body');
        $this->setBtnOkName(Trans::getWord('save'));

    }

    /**
     * Function to set the form submit
     *
     * @param string $formId     To set the attribute name.
     * @param string $actionName To set the attribute value.
     *
     * @return void
     */
    public function setFormSubmit(string $formId, string $actionName): void
    {
        if (empty($formId) === false && empty($actionName) === false) {
            $this->FormId = $formId;
            $this->ActionName = $actionName;
        } else {
            Message::throwMessage('Invalid form attributes for submit modal.');
        }
    }

    /**
     * Set show modal on load.
     *
     * @param boolean $show The value to show the modal.
     *
     * @return void
     */
    public function setShowOnLoad(bool $show = true): void
    {
        $this->ShowOnLoad = $show;
    }

    /**
     * function to set disable javascript
     *
     * @param boolean $disable The java script.
     *
     * @return void
     */
    public function setDisableJavascript(bool $disable = true): void
    {
        $this->EnableJavaScript = !$disable;
    }

    /**
     * Function to set the modal title.
     *
     * @param string $title The title value.
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        if (empty($title) === false) {
            $this->Title = $title;
        }
    }

    /**
     * Function to add the portlet attribute.
     *
     * @param string $attributeName  To set the attribute name.
     * @param string $attributeValue To set the attribute value.
     *
     * @return void
     */
    public function addModalAttribute(string $attributeName, string $attributeValue): void
    {
        $this->ModalAttributes[$attributeName] = $attributeValue;
    }

    /**
     * Function to add the header attribute.
     *
     * @param string $attributeName  To set the attribute name.
     * @param string $attributeValue To set the attribute value.
     *
     * @return void
     */
    public function addHeaderAttribute(string $attributeName, string $attributeValue): void
    {
        $this->HeaderAttributes[$attributeName] = $attributeValue;
    }

    /**
     * Function to add the body attribute.
     *
     * @param string $attributeName  To set the attribute name.
     * @param string $attributeValue To set the attribute value.
     *
     * @return void
     */
    public function addBodyAttribute(string $attributeName, string $attributeValue): void
    {
        $this->BodyAttributes[$attributeName] = $attributeValue;
    }

    /**
     * Function to set disable button.
     *
     * @return void
     */
    public function setDisableBtnOk(): void
    {
        $this->DisableBtnOk = true;
    }

    /**
     * Function to set button's name.
     *
     * @param string $btnName variable button name
     *
     * @return void
     */
    public function setBtnOkName(string $btnName): void
    {
        $this->BtnOkName = $btnName;
    }

    /**
     * Function to add the table to the body.
     *
     * @param \App\Frame\Gui\Table $table    To set the table data.
     * @param integer              $position To set the data position.
     *
     * @return void
     */
    public function addTable(Table $table, $position = 0): void
    {
        if ($table !== null) {
            if (empty($position) === false && is_numeric($position) === true) {
                if (array_key_exists($position, $this->Body) === true) {
                    $newBody = [];
                    foreach ($this->Body as $key => $data) {
                        if ($position === $key) {
                            $newBody[] = $table;
                            $newBody[] = $data;
                        } else {
                            $newBody[] = $data;
                        }
                    }
                    $this->Body = $newBody;
                } else {
                    $this->Body[$position] = $table;
                }
            } else {
                $this->Body[] = $table;
            }
        }
    }

    /**
     * Function to add field set to the form
     *
     * @param \App\Frame\Gui\FieldSet $fieldSet To set the field set data data.
     * @param integer                 $position To set the data position.
     *
     * @return void
     */
    public function addFieldSet(FieldSet $fieldSet, $position = 0): void
    {
        if ($fieldSet !== null) {
            if (empty($position) === false && is_numeric($position) === true) {
                if (array_key_exists($position, $this->Body) === true) {
                    $newBody = [];
                    foreach ($this->Body as $key => $data) {
                        if ($position === $key) {
                            $newBody[] = $fieldSet;
                            $newBody[] = $data;
                        } else {
                            $newBody[] = $data;
                        }
                    }
                    $this->Body = $newBody;
                } else {
                    $this->Body[$position] = $fieldSet;
                }
            } else {
                $this->Body[] = $fieldSet;
            }
        }
    }


    /**
     * Function to add the string to the body.
     *
     * @param string  $text     To set the table data.
     * @param integer $position To set the data position.
     *
     * @return void
     */
    public function addText(string $text, $position = 0): void
    {
        if (empty($text) === false) {
            if (empty($position) === false && is_numeric($position) === true) {
                if (array_key_exists($position, $this->Body) === true) {
                    $newBody = [];
                    foreach ($this->Body as $key => $data) {
                        if ($position === $key) {
                            $newBody[] = $text;
                            $newBody[] = $data;
                        } else {
                            $newBody[] = $data;
                        }
                    }
                    $this->Body = $newBody;
                } else {
                    $this->Body[$position] = $text;
                }
            } else {
                $this->Body[] = $text;
            }
        }
    }

    /**
     * Function to convert the portlet data to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->createModal();
    }

    /**
     * Function to create the portlet.
     *
     * @return string
     */
    public function createModal(): string
    {
        $result = '<div ' . $this->getModalAttribute() . '>';
        $result .= '<div class="modal-dialog modal-lg modal-content">';
        $result .= $this->getHeader();
        $result .= $this->getBody();
        $result .= $this->getFooter();
        $result .= '</div>';
        $result .= '</div>';
        $result .= $this->getJavascript();

        return $result;
    }

    /**
     * Function to get the id of the portlet.
     *
     * @return string
     */
    public function getModalId(): string
    {
        return $this->ModalAttributes['id'];
    }

    /**
     * Function to get portlet attribute.
     *
     * @return string
     */
    private function getModalAttribute(): string
    {
        $result = ' ';
        if (empty($this->ModalAttributes) === false) {
            foreach ($this->ModalAttributes as $key => $value) {
                $result .= $key . '="' . $value . '"';
            }
        }

        return $result;
    }

    /**
     * Function to get portlet header.
     *
     * @return string
     */
    private function getHeader(): string
    {
        $result = '';
        if (empty($this->Title) === false) {
            $result = '<div ' . $this->getHeaderAttribute() . '>';
            $result .= '<h4 class="modal-title" id="' . $this->getModalId() . 'Title">' . $this->Title . '</h4>';
            $result .= '</div>';
        }

        return $result;
    }

    /**
     * Function to get header attribute.
     *
     * @return string
     */
    private function getHeaderAttribute(): string
    {
        $result = ' ';
        if (empty($this->HeaderAttributes) === false) {
            foreach ($this->HeaderAttributes as $key => $value) {
                $result .= $key . '="' . $value . '"';
            }
        }

        return $result;
    }

    /**
     * Function to get portlet body.
     *
     * @return string
     */
    private function getBody(): string
    {
        $result = ' ';
        if (empty($this->Body) === false) {
            $result .= '<div ' . $this->getBodyAttribute() . '>';
            $result .= '<div class="row">';

            foreach ($this->Body as $key => $body) {
                if ($body instanceof Table) {
                    $result .= $body;
                } elseif ($body instanceof FieldSet) {
                    $result .= $body->createFieldSet();
                } else {
                    $result .= $body;
                }
            }
            $result .= '</div>';
            $result .= '</div>';

        }

        return $result;
    }

    /**
     * Function to get body attribute.
     *
     * @return string
     */
    private function getBodyAttribute(): string
    {
        $result = ' ';
        if (empty($this->BodyAttributes) === false) {
            foreach ($this->BodyAttributes as $key => $value) {
                $result .= $key . '="' . $value . '"';
            }
        }

        return $result;
    }


    /**
     * Function to get portlet body.
     *
     * @return string
     */
    private function getFooter(): string
    {
        $result = '<div class="modal-footer">';
        $result .= '<button type="button" id="' . $this->getModalId() . 'BtnClose" class="btn btn-default">' . Trans::getWord('close') . '</button>';
        if ($this->DisableBtnOk === false) {
            $result .= '<button type="button" id="' . $this->getModalId() . 'BtnOk" class="btn btn-primary">' . $this->BtnOkName . '</button>';
        }
        $result .= '</div>';

        return $result;
    }

    /**
     * Returns a string if the javascript must be loaded.
     *
     * @return string
     */
    private function getJavascript(): string
    {
        if ($this->EnableJavaScript === false) {
            return '';
        }
        $varJs = $this->Id . 'Modal';
        $javascript = '<script type="text/javascript">';
        $javascript .= 'var ' . $varJs . " = App.ModalHandler().createModal('" . $this->Id . "', '" . $this->FormId . "', '" . $this->ActionName . "');";
        if ($this->ShowOnLoad === true) {
            $javascript .= $varJs . '.show();';
        }
        $javascript .= '</script>';

        return $javascript;
    }

}
