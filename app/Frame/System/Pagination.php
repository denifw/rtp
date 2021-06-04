<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2017 C-Book
 */

namespace App\Frame\System;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Icon;

/**
 * Class to manage  pagination.
 *
 * @package    app
 * @subpackage Util\System
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2017 C-Book
 */
class Pagination
{

    /**
     * Attribute to allow pagination.
     *
     * @var boolean $EnablePaging
     */
    private $EnablePaging = true;
    /**
     * Attribute to store the type of pagination.
     *
     * @var string $Type
     */
    private $Type;
    /**
     * Attribute to store the list of type that available for pagination.
     *
     * @var array $TypeList
     */
    private static $TypeList = ['link', 'submit'];

    /**
     * Attribute to store the active paging.
     *
     * @var integer $ActivePaging
     */
    private $ActivePage = 1;

    /**
     * Attribute to store the row per page.
     *
     * @var integer $RowsPerPage
     */
    private $RowsPerPage = 30;
    /**
     * Attribute to store number of page.
     *
     * @var integer $NumberOfPage
     */
    private $NumberOfPage = 0;

    /**
     * Attribute to store the total page per view.
     *
     * @var integer $TotalPagePerView
     */
    private $TotalPagePerView = 5;

    /**
     * Attribute to store the total of all the row.
     *
     * @var integer $TotalRows
     */
    private $TotalRows = 0;
    /**
     * Property to store the url.
     *
     * @var string $PageUrl
     */
    private $PageUrl = '';

    /**
     * Property to store the id of the hidden field.
     *
     * @var string $FieldId
     */
    private $FieldId = 'page_number';


    /**
     * Property to store the id of the form for the submit action
     *
     * @var string $FormId
     */
    private $FormId;


    /**
     * Pagination constructor that will be called when we initiate the class.
     *
     * @param string $type To store the type of pagination.
     * @param string $formId To store the id of the form
     *
     */
    public function __construct(string $type = 'submit', $formId = '')
    {
        if (\in_array($type, self::$TypeList, true) === false) {
            Message::throwMessage('Not allowed type for pagination.');
        } else {
            $this->Type = $type;
        }
        $this->FormId = $formId;
    }

    /**
     * Function to set disable pagination.
     *
     * @param boolean $disable To store boolean value.
     *
     * @return void
     */
    public function setDisablePaging(bool $disable = true): void
    {
        $this->EnablePaging = true;
        if ($disable === true) {
            $this->EnablePaging = false;
        }
    }

    /**
     * Function to get field id.
     *
     * @return string
     */
    public function getFieldId(): string
    {
        return $this->FieldId;
    }

    /**
     * Function to set page url.
     *
     * @param string $pageUrl To store the page url.
     *
     * @return void
     */
    public function setPageUrl(string $pageUrl): void
    {
        $this->PageUrl = $pageUrl;
    }

    /**
     * Function to check is paging allow or not.
     *
     * @return boolean
     */
    public function isPagingEnable(): bool
    {
        return $this->EnablePaging;
    }

    /**
     * Function to set the active page.
     *
     * @param string $pageId To store the active page.
     *
     * @return void
     */
    public function setActivePaging(string $pageId): void
    {
        if (empty($pageId) === false && is_numeric($pageId) === true) {
            $this->ActivePage = (int)$pageId;
        }
    }

    /**
     * Function to get the total rows per page
     *
     * @return integer
     */
    public function getRowsPerPage(): int
    {
        return $this->RowsPerPage;
    }

    /**
     * Function to set the total rows per page
     *
     * @param integer $numberOfRows To store the total rows per page.
     *
     * @return void
     */
    public function setRowsPerPage(int $numberOfRows): void
    {
        if ($numberOfRows > 0) {
            $this->RowsPerPage = $numberOfRows;
        }
    }

    /**
     * Function to set the total rows.
     *
     * @param string $totalRows To store the total rows.
     *
     * @return void
     */
    public function setTotalRows(string $totalRows): void
    {
        if (empty($totalRows) === false && is_numeric($totalRows) === true) {
            $this->TotalRows = (int)$totalRows;
        }
    }

    /**
     * Function to get limit start
     *
     * @return integer
     */
    public function getOffset(): int
    {
        return (($this->ActivePage - 1) * $this->RowsPerPage);
    }

    /**
     * Function to create the pagination
     *
     * @return string
     */
    public function createPaging(): string
    {
        $this->setNumberOfPage();
        $result = '<div class="portlet-paging">';
        # Calculate the total showing row.
        $numberOfCurrentRows = $this->TotalRows - ($this->RowsPerPage * $this->ActivePage);
        $showing = $this->RowsPerPage;
        if ($numberOfCurrentRows < 0) {
            $showing += $numberOfCurrentRows;
        }
        $result .= '<p class="pagination-inner pull-right">' . Trans::getWord('pagingInfo', 'message', '', ['dataPerRow' => $showing, 'totalData' => $this->TotalRows]) . '</p>';
        # Create pagination button.
        if ($this->EnablePaging === true && $this->NumberOfPage > 1) {
            if ($this->Type === 'submit') {
                if ($this->FormId === null || $this->FormId === '') {
                    Message::throwMessage('Invalid Form ID for pagination submit action.');
                }
                $result .= '<input type="hidden" id="' . $this->FieldId . '" name="' . $this->FieldId . '" value="' . $this->ActivePage . '" />';
            } else {
                if (empty($this->PageUrl) === true) {
                    Message::throwMessage('Invalid URL for pagination hyperlink, you need to set the page url for hyperlink pagination.');
                }
            }
            $result .= '<div class="paging-simple-number pull-right">';
            $result .= '<ul class="pagination ">';
            $result .= $this->getFirsButton();
            $result .= $this->getPreviousButton();
            $result .= $this->getButtons();
            $result .= $this->getNextButton();
            $result .= $this->getLastButton();
            $result .= '</ul>';
            $result .= '</div>';
        }
        $result .= '</div>';

        return $result;
    }

    /**
     * Function to set number of page
     *
     * @return void
     */
    private function setNumberOfPage(): void
    {
        if ($this->TotalRows > 0) {
            $pages = (int)($this->TotalRows / $this->RowsPerPage);
            if (($this->TotalRows % $this->RowsPerPage) > 0) {
                $pages++;
            }
            $this->NumberOfPage = $pages;
        }

    }

    /**
     * Function to get first button
     *
     * @return string
     */
    private function getFirsButton(): string
    {
        if ($this->ActivePage === 1) {
            $result = '<li class="disabled">';
            $result .= '<span>' . Trans::getWord('first') . '</span>';
            $result .= '</li>';

        } else {
            $result = '<li>';
            $result .= $this->createPaginationField('1', Trans::getWord('first'));
            $result .= '</li>';
        }

        return $result;
    }

    /**
     * Function to get page url
     *
     * @param string $pageId To store the id of the page.
     *
     * @return string
     */
    private function getPageUrl($pageId): string
    {
        return $this->PageUrl . '&' . $this->FieldId . '=' . $pageId;
    }

    /**
     * Function to get Previous button
     *
     * @return string
     */
    private function getPreviousButton(): string
    {
        if ($this->ActivePage === 1) {
            $result = '<li class="disabled">';
            $result .= '<span><i class="' . Icon::StepBackward . '"></i></span>';
            $result .= '</li>';
        } else {
            $result = '<li>';
            $page = $this->ActivePage - 1;
            $result .= $this->createPaginationField($page, '<i class="' . Icon::StepBackward . '"></i>');
            $result .= '</li>';
        }

        return $result;
    }

    /**
     * Function to get first button
     *
     * @return string
     */
    private function getButtons(): string
    {
        $result = '';
        # Get button before the active button.
        $totalBeforeAndAfter = (int)($this->TotalPagePerView / 2);
        if (($this->ActivePage - $totalBeforeAndAfter) < 1) {
            $startNumber = 1;
        } else {
            $startNumber = $this->ActivePage - $totalBeforeAndAfter;
        }

        $totalPageAfterActive = $this->NumberOfPage - $this->ActivePage;
        if ($totalPageAfterActive < $totalBeforeAndAfter && $startNumber > 1) {
            if (($startNumber - $totalPageAfterActive) < 1) {
                $startNumber = 1;
            } else {
                $startNumber -= ($totalBeforeAndAfter - $totalPageAfterActive);
            }
        }
        if ($startNumber === 0) {
            $startNumber = 1;
        }
        for ($i = 0; $i < $this->TotalPagePerView; $i++) {
            $pageNumber = $startNumber + $i;
            if ($pageNumber <= $this->NumberOfPage) {
                $result .= $this->getPageButton($pageNumber);
            }
        }

        return $result;
    }

    /**
     * Function to get Next button
     *
     * @param integer $pageNumber To store the number of the button.
     *
     * @return string
     */
    private function getPageButton($pageNumber): string
    {
        if ($this->ActivePage === $pageNumber) {
            $result = '<li class="active">';
            $result .= '<span>' . $pageNumber . '</span>';
            $result .= '</li>';
        } else {
            $result = '<li>';
            $result .= $this->createPaginationField($pageNumber, $pageNumber);
            $result .= '</li>';
        }

        return $result;
    }

    /**
     * Function to get Next button
     *
     * @return string
     */
    private function getNextButton(): string
    {
        if ($this->ActivePage === $this->NumberOfPage) {
            $result = '<li class="disabled">';
            $result .= '<span><i class="' . Icon::StepForward . '"></i></span>';
            $result .= '</li>';
        } else {
            $result = '<li>';
            $page = $this->ActivePage + 1;
            $result .= $this->createPaginationField($page, '<i class="' . Icon::StepForward . '"></i>');
            $result .= '</li>';
        }

        return $result;
    }

    /**
     * Function to get Next button
     *
     * @return string
     */
    private function getLastButton(): string
    {
        if ($this->ActivePage === $this->NumberOfPage) {
            $result = '<li class="disabled">';
            $result .= '<span>Last</span>';
            $result .= '</li>';
        } else {
            $result = '<li>';
            $result .= $this->createPaginationField($this->NumberOfPage, Trans::getWord('last'));
            $result .= '</li>';
        }

        return $result;
    }

    /**
     * Function to get Next button
     *
     * @param string $value To store the value of the paging.
     * @param string $label To store the label of the paging.
     *
     * @return string
     */
    private function createPaginationField(string $value, string $label): string
    {
        if ($this->Type === 'submit') {
            $result = '<a href="javascript:;" onclick="App.onClickPaging(\'' . $this->FormId . '\', \'' . $value . '\', \'' . $this->FieldId . '\')" href="#"> ' . $label . '</a>';
        } else {
            $result = '<a href="' . $this->getPageUrl($value) . '">' . $label . '</a>';
        }

        return $result;
    }


}
