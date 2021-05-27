<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 15/03/2017 C-Book
 */

namespace App\Frame\System;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\ButtonInterface;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Html\Buttons\SubmitButton;
use App\Frame\Gui\Html\Fields\Hidden;
use App\Frame\Gui\Modal;


/**
 * Class to generate view.
 *
 * @package    app
 * @subpackage Model
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  15/03/2017 C-Book
 */
class View
{
    /**
     * Property to store all form content.
     *
     * @var array $Contents
     */
    private $Contents = [];
    /**
     * Property to store all content ids.
     *
     * @var array $ContentIds
     */
    private $ContentIds = [];

    /**
     * Property to store all error message.
     *
     * @var array $Errors
     */
    private $Errors = [];
    /**
     * Property to store all error message.
     *
     * @var array $Info
     */
    private $Info = [];

    /**
     * Property to store all error message.
     *
     * @var array $Warnings
     */
    private $Warnings = [];

    /**
     * Property to store the title of the view
     *
     * @var string $Title
     */
    private $Title = 'Undefined';

    /**
     * Property to store the description of the view
     *
     * @var string $Description
     */
    private $Description = 'Undefined';

    /**
     * Property to store buttons.
     *
     * @var array $Buttons
     */
    private $Buttons = [];

    /**
     * Property to store button ids.
     *
     * @var array $ButtonIds
     */
    private $ButtonIds = [];

    /**
     * Property to store the total button to show in the view.
     *
     * @var int $NumberOfButton
     */
    private $NumberOfButton = 6;

    /**
     * Property to trigger view to create the modal.
     *
     * @var boolean $EnableModal
     */
    private $EnableModal = false;


    /**
     * Property to trigger view to create the menu
     *
     * @var boolean $EnableMenu
     */
    private $EnableMenu = true;


    /**
     * Property to trigger view to create the header
     *
     * @var boolean $EnableHeader
     */
    private $EnableHeader = true;

    /**
     * Property to trigger view to create the header
     *
     * @var int $ReloadTime
     */
    private $ReloadTime = 0;


    /**
     * Property to store the active modal
     *
     * @var string $ActiveModal
     */
    private $ActiveModal = '';

    /**
     * Property to store the attribute of the form.
     *
     * @var array $FormAttributes
     */
    private $FormAttributes = [];


    /**
     * Property to store the attribute of the form.
     *
     * @var array $ContentAttributes
     */
    private $ContentAttributes = [];

    /**
     * Property to store the active page path.
     *
     * @var string $ActivePage
     */
    private $ActivePage = '';

    /**
     * Property to store the path for custom style.
     *
     * @var string $PathCustomStyle
     */
    private $PathCustomStyle = '';

    /**
     * Property to store the path for custom script.
     *
     * @var string $PathCustomScript
     */
    private $PathCustomScript = '';

    /**
     * View constructor.
     *
     * @param string $formId to store the title of the view.
     * @param string $title to store the title of the view.
     * @param string $description to store the description of the view.
     */
    public function __construct(string $formId, string $title, string $description)
    {
        if (empty($formId) === false) {
            $this->addFormAttribute('id', $formId);
        } else {
            Message::throwMessage('Invalid id for the view.', 'DEBUG');
        }
        if (empty($title) === false) {
            $this->Title = $title;
        }
        if (empty($description) === false) {
            $this->Description = $description;
        }
        $this->addFormAttribute('method', 'POST');
        $this->addFormAttribute('autocomplete', 'off');
        $this->addFormAttribute('enctype', 'multipart/form-data');

    }


    /**
     * Function to set data.
     *
     * @param string $contentId To store the id of the content.
     * @param string $content To store the data.
     *
     * @return void
     */
    public function addContent(string $contentId, string $content): void
    {
        if (empty($contentId) === true) {
            $contentId = 'undefined';
        }
        if (array_key_exists($contentId, $this->Contents) === true) {
            $this->Contents[$contentId] .= $content;
        } else {
            $this->Contents[$contentId] = $content;
            $this->ContentIds[] = $contentId;
        }
    }

    /**
     * Function to set data.
     *
     * @param \App\Frame\Gui\Modal $modal To store the modal object.
     *
     * @return void
     */
    public function addModal(Modal $modal): void
    {
        if ($modal !== null) {
            $this->addContent($modal->getModalId() . 'modal_content', $modal);
        }
    }

    /**
     * Function to add information.
     *
     * @param string $massage To store the data.
     *
     * @return void
     */
    public function addInfoMessage(string $massage): void
    {
        $this->Info[] = $massage;
    }

    /**
     * Function to add error.
     *
     * @param string $massage To store the data.
     *
     * @return void
     */
    public function addErrorMessage(string $massage): void
    {
        $this->Errors[] = $massage;
    }

    /**
     * Function to add warning.
     *
     * @param string $massage To store the data.
     *
     * @return void
     */
    public function addWarningMessage(string $massage): void
    {
        $this->Warnings[] = $massage;
    }

    /**
     * Function to add information.
     *
     * @param array $messages To store the data.
     *
     * @return void
     */
    public function setInfoMessages(array $messages): void
    {
        $this->Info = array_merge($this->Info, $messages);
    }

    /**
     * Function to add error.
     *
     * @param array $messages To store the data.
     *
     * @return void
     */
    public function setErrorMessages(array $messages): void
    {
        $this->Errors = array_merge($this->Errors, $messages);
    }

    /**
     * Function to add warning.
     *
     * @param array $messages To store the data.
     *
     * @return void
     */
    public function setWarningMessages(array $messages): void
    {
        $this->Warnings = array_merge($this->Warnings, $messages);
    }

    /**
     * Function to get information message
     *
     * @return array
     */
    public function getInfoMessages(): array
    {
        return $this->Info;
    }

    /**
     * Function to get error message.
     *
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->Errors;
    }

    /**
     * Function to get warning message.
     *
     * @return array
     */
    public function getWarningMessages(): array
    {
        return $this->Warnings;
    }

    /**
     * Function to add form attribute
     *
     * @param string $attributeId To store the id of the attribute.
     * @param string $attributeValue To store the value of the attribute.
     *
     * @return void
     */
    public function addFormAttribute(string $attributeId, string $attributeValue): void
    {
        if (empty($attributeId) === false && empty($attributeValue) === false) {
            $this->FormAttributes[$attributeId] = $attributeValue;
        }
    }

    /**
     * Function to add form attribute
     *
     * @param string $contentId To store the id of the content.
     * @param string $attributeId To store the id of the attribute.
     * @param string $attributeValue To store the value of the attribute.
     *
     * @return void
     */
    public function addContentAttribute(string $contentId, string $attributeId, string $attributeValue): void
    {
        if (empty($contentId) === false && empty($attributeId) === false && empty($attributeValue) === false) {
            if (array_key_exists($contentId, $this->ContentAttributes) === false) {
                $this->ContentAttributes[$contentId] = [];
                $this->ContentAttributes[$contentId][$attributeId] = $attributeValue;
            } else {
                $this->ContentAttributes[$contentId][$attributeId] = $attributeValue;
            }
        }
    }

    /**
     * Function to get form attribute
     *
     * @param string $attributeId To store the id of the attribute.
     *
     * @return string
     */
    public function getFormAttribute(string $attributeId): string
    {
        $result = '';
        if (array_key_exists($attributeId, $this->FormAttributes) === true) {
            $result = $this->FormAttributes[$attributeId];
        }

        return $result;
    }

    /**
     * Function to set the active modal.
     *
     * @param string $modalId to store the id of the active modal.
     *
     * @return void
     */
    public function setActiveModal($modalId): void
    {
        $this->ActiveModal = $modalId;
    }

    /**
     * Function to set the custom style path
     *
     * @param string $path to store the path for custom style.
     *
     * @return void
     */
    public function setPathCustomStyle($path): void
    {
        $this->PathCustomStyle = $path;
    }

    /**
     * Function to set the custom script path
     *
     * @param string $path to store the path for custom script.
     *
     * @return void
     */
    public function setPathCustomScript($path): void
    {
        $this->PathCustomScript = $path;
    }

    /**
     * Function to set the active page path.
     *
     * @param string $pagePath To store the active path of page.
     *
     * @return void
     */
    public function setActivePage($pagePath): void
    {
        $this->ActivePage = $pagePath;
    }

    /**
     * Function to set title page.
     *
     * @param string $title To store the title.
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->Title = $title;
    }

    /**
     * Function to set reload time.
     *
     * @param int $reloadTime .
     *
     * @return void
     */
    public function setReloadTime(int $reloadTime): void
    {
        if ($reloadTime !== null && is_numeric($reloadTime) === true) {
            $this->ReloadTime = $reloadTime;
        }
    }

    /**
     * Function to set description page.
     *
     * @param string $description To store the description of the page.
     *
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->Description = $description;
    }

    /**
     * Function to set the number of button to show and the rest will be go to action.
     *
     * @param int $number To number of buttons.
     *
     * @return void
     */
    public function setNumberOfButton(int $number): void
    {
        if ($number > 0) {
            $this->NumberOfButton = $number;
        }
    }

    /**
     * Function to add button to the form.
     *
     * @param \App\Frame\Gui\Html\ButtonInterface $button To set the button field.
     *
     * @return void
     */
    public function addButton(ButtonInterface $button): void
    {
        if ($button instanceof ButtonInterface) {
            $buttonId = $button->getAttribute('id');
            if (array_key_exists($buttonId, $this->Buttons) === false) {
                $this->Buttons[$buttonId] = $button;
                $this->ButtonIds[] = $buttonId;
            } else {
                Message::throwMessage('Button with id ' . $buttonId . ' already added.', 'DEBUG');
            }
        } else {
            Message::throwMessage('Button must be instance of Button Interface', 'DEBUG');
        }
    }

    /**
     * Function to add button after existing button to the form.
     *
     * @param \App\Frame\Gui\Html\ButtonInterface $button To set the button field.
     * @param string $existingButtonId To set the existing id of the field.
     *
     * @return void
     */
    public function addButtonAfter(ButtonInterface $button, string $existingButtonId): void
    {
        if (array_key_exists($existingButtonId, $this->Buttons) === false) {
            Message::throwMessage('Not found button with id ' . $existingButtonId . ' in the form data.', 'DEBUG');
        }
        if ($button instanceof ButtonInterface) {
            $buttonId = $button->getAttribute('id');
            if (array_key_exists($buttonId, $this->Buttons) === false) {
                $newButtons = [];
                $this->ButtonIds = [];
                foreach ($this->Buttons as $key => $oldButton) {
                    $newButtons[$key] = $oldButton;
                    $this->ButtonIds[] = $key;
                    if ($key === $existingButtonId) {
                        $newButtons[$buttonId] = $button;
                        $this->ButtonIds[] = $buttonId;
                    }
                }
                $this->Buttons = $newButtons;
            } else {
                Message::throwMessage('Duplicate Field with id ' . $buttonId . ' inside the form.', 'DEBUG');
            }
        } else {
            Message::throwMessage('Button must be instance of Button Interface', 'DEBUG');
        }
    }

    /**
     * Function to add button at the beginning.
     *
     * @param \App\Frame\Gui\Html\ButtonInterface $button To set the value of the attribute.
     *
     * @return void
     */
    public function addButtonAtTheBeginning(ButtonInterface $button): void
    {
        if ($button instanceof ButtonInterface) {
            $buttonId = $button->getAttribute('id');
            if (array_key_exists($buttonId, $this->Buttons) === false) {
                $newButtons = [];
                $this->ButtonIds = [];
                $newButtons[$buttonId] = $button;
                $this->ButtonIds[] = $buttonId;
                foreach ($this->Buttons as $key => $oldButton) {
                    $newButtons[$key] = $oldButton;
                    $this->ButtonIds[] = $key;
                }
                $this->Buttons = $newButtons;
            } else {
                Message::throwMessage('Duplicate button with id ' . $buttonId . ' inside the view.', 'DEBUG');
            }
        } else {
            Message::throwMessage('Button must be instance of Button Interface', 'DEBUG');
        }
    }

    /**
     * Function to remove button from the form.
     *
     * @param string $buttonId To set the id of the field.
     *
     * @return void
     */
    public function removeButton(string $buttonId): void
    {
        if (array_key_exists($buttonId, $this->Buttons) === true) {
            unset($this->Buttons[$buttonId]);
            $key = array_search($buttonId, $this->ButtonIds, true);
            unset($this->ButtonIds[$key]);
            # Reset the field ids to the correct values
            $this->ButtonIds = array_values($this->ButtonIds);
        } else {
            Message::throwMessage('Not found button with id ' . $buttonId . ' in the view data.', 'DEBUG');
        }
    }


    /**
     * Function to set the enable modal in view.
     *
     * @param bool $enable to store the trigger to enable the modal.
     *
     * @return void
     */
    public function setEnableModal(bool $enable = true): void
    {
        $this->EnableModal = $enable;
    }

    /**
     * Function to set the enable menu in view.
     *
     * @param bool $enable to store the trigger to enable the menu.
     *
     * @return void
     */
    public function setEnableMenu(bool $enable = true): void
    {
        $this->EnableMenu = $enable;
    }

    /**
     * Function to set the enable header in view.
     *
     * @param bool $enable to store the trigger to enable the menu.
     *
     * @return void
     */
    public function setEnableHeader(bool $enable = true): void
    {
        $this->EnableHeader = $enable;
    }

    /**
     * Function to create the view.
     *
     * @return array
     */
    public function createView(): array
    {
        $view = '<form ' . $this->loadFormAttribute() . '>';
        $view .= csrf_field();
        $view .= new Hidden($this->getFormAttribute('id') . '_action', '');
        if ($this->EnableHeader === true) {
            $view .= $this->loadHeaderPage();
        }
        $view .= $this->loadMessageBox();
        $view .= $this->loadContent();
        $view .= '</form>';
        $data = [];
        if ($this->EnableMenu === true) {
            $menu = new Menu($this->ActivePage);
            $data['content_menu'] = $menu->createMenu();
            $view .= $this->loadJavascript();
        }

        $data['content_body'] = $view;
        $data['page_title'] = $this->Title;
        $data['custom_style'] = $this->PathCustomStyle;
        $data['custom_script'] = $this->PathCustomScript;

        return $data;
    }

    /**
     * Function to create the view.
     *
     * @return array
     */
    public function createErrorView(): array
    {
        $view = '<form ' . $this->loadFormAttribute() . '>';
        $view .= csrf_field();
        $view .= $this->loadHeaderPage();
        $view .= $this->loadMessageBox();
        $view .= '</form>';
        $data = [];
        if ($this->EnableMenu === true) {
            $menu = new Menu($this->ActivePage);
            $data['content_menu'] = $menu->createMenu();
            $view .= $this->loadJavascript();
        }
        $data['content_body'] = $view;
        $data['page_title'] = $this->Title;
        $data['custom_style'] = '';
        $data['custom_script'] = '';

        return $data;
    }

    /**
     * Function to create the header view.
     *
     * @return string
     */
    private function loadContent(): string
    {
        $result = '';
        if (empty($this->ContentIds) === false) {
            foreach ($this->ContentIds as $contentId) {
                $result .= '<div class="row" ' . $this->loadContentAttribute($contentId) . '>';
                $result .= $this->Contents[$contentId];
                $result .= '</div>';
            }
        }

        return $result;
    }

    /**
     * Function to create the header view.
     *
     * @param string $contentId To store the content attribute.
     *
     * @return string
     */
    private function loadContentAttribute($contentId): string
    {
        $result = '';
        if (empty($this->ContentAttributes) === false && array_key_exists($contentId, $this->ContentAttributes) === true) {
            foreach ($this->ContentAttributes[$contentId] as $key => $value) {
                $result .= $key . '="' . $value . '"';
            }
        }

        return $result;
    }

    /**
     * Function to create the header view.
     *
     * @return string
     */
    private function loadHeaderPage(): string
    {
        $result = '';
        $result .= '<div class="row">';
        $result .= '<div class="col-md-6 col-sm-6 col-xs-12">';
        if (empty($this->Description) === false) {
            $result .= '<h4>' . $this->Description . '</h4>';
        } else {
            $result .= '<h4>Undefined</h4>';
        }
        $result .= '</div>';
        $result .= $this->loadButton();
        $result .= '</div>';
        $result .= '<hr class="col-12 title-divider">';
        $result .= '<div class="clearfix"></div>';

        return $result;
    }

    /**
     * Function to load button.
     *
     * @return string
     */
    private function loadButton(): string
    {
        $result = '';
        if (empty($this->ButtonIds) === false) {
            $singleButton = '';
            $amountOfButton = \count($this->ButtonIds);
            if ($amountOfButton < $this->NumberOfButton) {
                $this->NumberOfButton = $amountOfButton;
            } else if ($amountOfButton === ($this->NumberOfButton + 1)) {
                $this->NumberOfButton--;
            }
            for ($i = ($this->NumberOfButton - 1); $i >= 0; $i--) {
                $buttonId = $this->ButtonIds[$i];
                $singleButton .= $this->Buttons[$buttonId];
            }
            $groupButton = '';
            if ($amountOfButton > $this->NumberOfButton) {
                $groupButton .= '<div class="btn-group pull-right px-2">';
                $groupButton .= '<button type="button" class="btn btn-danger btn-sm">' . Trans::getWord('options') . '</button>';
                $groupButton .= '<button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">';
                $groupButton .= '<span class="caret"></span>';
                $groupButton .= '<span class="sr-only">Toggle Dropdown</span>';
                $groupButton .= '</button>';
                $groupButton .= '<ul class="dropdown-menu" role="menu">';
                for ($i = $this->NumberOfButton; $i < $amountOfButton; $i++) {
                    $buttonId = $this->ButtonIds[$i];
                    $btn = $this->Buttons[$buttonId];
                    if ($btn instanceof PdfButton) {
                        $singleButton .= $btn;
                    } else {
                        $groupButton .= '<li>';
                        $groupButton .= $this->changeButtonToHyperlink($btn);
                        $groupButton .= '</li>';
                    }
                }
                $groupButton .= '</ul>';
                $groupButton .= '</div>';
            }
            $result .= '<div class="col-md-6 col-sm-6 col-xs-12">';
            $result .= $groupButton;
            $result .= $singleButton;
            $result .= '</div>';
        }

        return $result;
    }

    /**
     * Function to load button.
     *
     * @param ButtonInterface $button TOo store the button object.
     *
     * @return string
     */
    private function changeButtonToHyperlink(ButtonInterface $button): string
    {
        $result = new HyperLink($button->getAttribute('id'), $button->getAttribute('value'), 'javascript:;');
        $result->setIcon($button->getIcon());
        if ($button instanceof SubmitButton) {
            $result .= $button->getJavascript();
        } else if ($button instanceof ModalButton) {
            $result .= $button->getJavascript();
        } else if ($button instanceof HyperLink) {
            $button->disableClassAttribute();
            $button->viewAsButton(false);
            $result = $button;
        } else {
            $result->addAttribute('onclick', $button->getAttribute('onclick'));
        }
        return $result;
    }

    /**
     * Function to create the error notification.
     *
     * @return string
     */
    private function loadMessageBox(): string
    {
        $result = '';
        if (empty($this->Errors) === false) {
            foreach ($this->Errors as $message) {
                $result .= $this->createHtmlMessage($message, 'alert-error');
            }
        }
        if (empty($this->Warnings) === false) {
            foreach ($this->Warnings as $message) {
                $result .= $this->createHtmlMessage($message, 'alert-warning');
            }
        }
        if (empty($this->Info) === false) {
            foreach ($this->Info as $message) {
                $result .= $this->createHtmlMessage($message, 'alert-success');
            }
        }

        return $result;
    }

    /**
     * Function to create the info notification.
     *
     * @param string $message To store the message.
     * @param string $type To store the type of the message to set the style of the box.
     *
     * @return string
     */
    private function createHtmlMessage(string $message, string $type = ''): string
    {
        $result = '';
        if (empty($message) === false) {
//            $result .= '<div class="row-fluid">';
            $result .= '<div class="alert ' . $type . ' alert-dismissible fade in" role="alert">';
            $result .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>';
            $result .= $message;
            $result .= '</div>';
//            $result .= '</div>';
        }

        return $result;
    }

    /**
     * Function to get the attribute of form.
     *
     * @return string
     */
    private function loadFormAttribute(): string
    {
        $result = '';
        if (empty($this->FormAttributes) === false) {
            foreach ($this->FormAttributes as $key => $value) {
                $result .= $key . '="' . $value . '"';
            }
        }

        return $result;
    }

    /**
     * Function to get the javascript code.
     *
     * @return string
     */
    private function loadJavascript(): string
    {
        $varJs = 'NotifVar';
        $javascript = '<script type="text/javascript">';
        $javascript .= 'var ' . $varJs . " = new App.SystemNotification('notification', 'loadUnreadNotification', 'loadListNotification', 'Notification/Notification');";
        $javascript .= $varJs . '.create();';
        $javascript .= 'setInterval(function () {';
        $javascript .= $varJs . '.reload();';
        $javascript .= '}, 20005);';
        $javascript .= '</script>';
        if ($this->ReloadTime > 0) {
            $javascript .= '<script type="text/javascript">';
            $javascript .= 'setInterval(function () {';
            $javascript .= 'window.location.reload();';
            $javascript .= '}, ' . $this->ReloadTime . ');';
            $javascript .= '</script>';

        }

        return $javascript;
    }


}
