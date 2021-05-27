<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2017 C-Book
 */

namespace App\Frame\Gui;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;

/**
 * Class  to manage the creation of tab.
 *
 * @package    app
 * @subpackage Util\Gui
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2017 C-Book
 */
class Tabs
{
    /**
     * Attribute to store all the tab header.
     *
     * @var array $Tabs
     */
    private $Tabs = [];

    /**
     * Attribute to store all content for each tab.
     *
     * @var array $TabContents
     */
    private $TabContents = [];

    /**
     * Attribute to store all content id for the tab.
     *
     * @var array $ContentIds
     */
    private $ContentIds = [];

    /**
     * Attribute to store the value to lock the tab.
     *
     * @var bool $LockTab
     */
    private $LockTab = false;

    /**
     * Attribute to set the active tabs.
     *
     * @var string $ActiveTab
     */
    private $ActiveTab = '';

    /**
     * Attribute to set the hidden field id.
     *
     * @var string $FieldId
     */
    private $FieldId;
    /**
     * Attribute to set the tab id.
     *
     * @var string $TabId
     */
    private $TabId;


    /**
     * Tab constructor.
     *
     * @param string $tabId To store the id of the tabs.
     */
    public function __construct($tabId = 'myTab')
    {
        $this->TabId = $tabId;
        $this->FieldId = $tabId . 'Active';
    }

    /**
     * Function to register tab.
     *
     * @param string $tabId To store the id of the tab.
     *
     * @return void
     */
    private function registerTab(string $tabId): void
    {
        $tabId = trim($tabId);
        if (empty($tabId) === false && is_numeric($tabId) === false) {
            $tabId = str_replace([' ', '/', '\\', '-'], '_', $tabId);
            $this->Tabs[$tabId] = Trans::getWord($tabId);
            if (empty($this->ActiveTab) === true) {
                $this->ActiveTab = $tabId;
            }
        } else {
            Message::throwMessage('Invalid id for tab, the id must be not empty string and not a number.');
        }
    }

    /**
     * Function add content to the tab.
     *
     * @param string $tabId     To store the id of the tab.
     * @param string $content   To store the content of the tab.
     * @param string $spanClass To store the span class for the content.
     *
     * @return void
     */
    public function addContent($tabId, $content, $spanClass = 'span12'): void
    {
        if (empty($content) === false) {
            if (array_key_exists($tabId, $this->Tabs) === false) {
                $this->registerTab($tabId);
            }
            $oldContent = '';
            $contentId = 'undefined';
            if (array_key_exists($tabId, $this->ContentIds) === true) {
                $contentIds = $this->ContentIds[$tabId];
                if (\in_array($contentId, $contentIds, true) === false) {
                    $this->ContentIds[$tabId][] = $contentId;
                } else {
                    $oldContent = $this->TabContents[$tabId][$contentId];
                }
            } else {
                $this->ContentIds[$tabId][] = $contentId;
            }
            $this->TabContents[$tabId][$contentId] = $oldContent . '<div class="' . $spanClass . '">' . $content . '</div>';
        }
    }

    /**
     * Function add content to the tab.
     *
     * @param string                 $tabId   To store the id of the tab.
     * @param \App\Frame\Gui\Portlet $content To store the content of the tab.
     *
     * @return void
     */
    public function addPortlet(string $tabId, Portlet $content): void
    {
        if ($content !== null) {
            if (array_key_exists($tabId, $this->Tabs) === false) {
                $this->registerTab($tabId);
            }
            $portletId = $content->getPortletId();
            if (array_key_exists($tabId, $this->ContentIds) === true) {
                $contentIds = $this->ContentIds[$tabId];
                if (\in_array($portletId, $contentIds, true) === false) {
                    $this->ContentIds[$tabId][] = $portletId;
                } else {
                    Message::throwMessage('Content with id ' . $portletId . ' already exist.');
                }
            } else {
                $this->ContentIds[$tabId][] = $portletId;
            }
            $this->TabContents[$tabId][$portletId] = $content->createPortlet();

        }
    }

    /**
     * Function to set the id for the tab
     *
     * @param string $tabId To store the active tab id.
     *
     * @return void
     */
    public function setTabId(string $tabId): void
    {
        if ($tabId !== null) {
            $this->TabId = $tabId;
        }
    }

    /**
     * Function to set the active tab.
     *
     * @param string $tabId    To store the active tab id.
     * @param bool   $override To override the active tab.
     *
     * @return void
     */
    public function setActiveTab(string $tabId, bool $override = false): void
    {
        if (array_key_exists($tabId, $this->Tabs) === true && ($override === true || $this->LockTab === false)) {
            $this->ActiveTab = $tabId;
        }
        $this->LockTab = $override;
    }

    /**
     * Function to get the hidden field id.
     *
     * @return string
     */
    public function getFieldId(): string
    {
        return $this->FieldId;
    }

    /**
     * Function to convert the portlet data to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->createTab();
    }


    /**
     * Function create the tab.
     *
     * @return string
     */
    public function createTab(): string
    {
        $result = '';
        if (empty($this->Tabs) === false) {
            $result .= $this->createHiddenActiveTabField();
            $result .= '<div class="tabs-container" role="tabpanel" data-example-id="togglable-tabs">';
            $result .= $this->createHeaderTab();
            $result .= $this->createContentTab();
            $result .= '</div>';
            $result .= $this->getJavascript();
        }

        return $result;
    }

    /**
     * Function create the header tab.
     *
     * @return string
     */
    private function createHeaderTab(): string
    {
        $result = '<ul id= "' . $this->TabId . '" class="nav nav-tabs bar_tabs" role="tablist">';
        foreach ($this->Tabs as $id => $label) {
            $tabContentId = '#' . $this->TabId . 'Content_' . $id;
            $linkId = $this->TabId . '_' . $id;
            if ($id === $this->ActiveTab) {
                $result .= '<li role="presentation" class="active">';
                $result .= '<a href="' . $tabContentId . '" id="' . $linkId . '" role="tab" data-toggle="tab" aria-expanded="true">' . $label . '</a>';
                $result .= '</li>';
            } else {
                $result .= '<li role="presentation" class="">';
                $result .= '<a href="' . $tabContentId . '" id="' . $linkId . '" role="tab" data-toggle="tab" aria-expanded="false">' . $label . '</a>';
                $result .= '</li>';
            }
        }
        $result .= '</ul>';

        return $result;
    }

    /**
     * Function create the content tab.
     *
     * @return string
     */
    private function createContentTab(): string
    {
        $result = '<div class="tab-content" id="' . $this->TabId . 'Content">';
        foreach ($this->Tabs as $tabId => $label) {
            $tabContentId = $this->TabId . 'Content_' . $tabId;
            $linkId = $this->TabId . '_' . $tabId;
            # Set active tab.
            if ($tabId === $this->ActiveTab) {
                $result .= '<div role="tabpanel" class="tab-pane fade active in" id="' . $tabContentId . '" aria-labelledby="' . $linkId . '">';
            } else {
                $result .= '<div role="tabpanel" class="tab-pane fade" id="' . $tabContentId . '" aria-labelledby="' . $linkId . '">';
            }
            # Generate content tab.
            $result .= '<div class="row">';
            if (empty($this->ContentIds[$tabId]) === false) {
                $countContent = \count($this->ContentIds[$tabId]);
                for ($i = 0; $i < $countContent; $i++) {
                    $contentId = $this->ContentIds[$tabId][$i];
                    $result .= $this->TabContents[$tabId][$contentId];
                }
            } else {
                $result .= 'There is no content for these tab.';
            }
            $result .= '</div>';
            $result .= '</div>';
        }
        $result .= '</div>';

        return $result;
    }

    /**
     * Function create the hidden field for the tab.
     *
     * @return string
     */
    private function createHiddenActiveTabField(): string
    {
        return '<input type="hidden" id="' . $this->FieldId . '" name="' . $this->FieldId . '" value="' . $this->ActiveTab . '"/>';
    }

    /**
     * Returns a string if the javascript must be loaded.
     *
     * @return string
     */
    private function getJavascript(): string
    {
        $javascript = '<script type="text/javascript">';
        $javascript .= 'var ' . $this->TabId . " = new App.Tabs('" . $this->TabId . "');";
        $javascript .= $this->TabId . ".setActiveField('" . $this->FieldId . "');";
        foreach ($this->Tabs as $key => $value) {
            $javascript .= $this->TabId . ".addContentId('" . $key . "');";
        }
        $javascript .= $this->TabId . '.createTab();';
        $javascript .= '</script>';

        return $javascript;
    }
}
