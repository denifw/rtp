<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Mvc;


use App\Frame\Exceptions\Message;
use App\Frame\Document\Excel;
use App\Frame\Document\ExcelTable;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\SubmitButton;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\System\ListingAction;
use App\Frame\System\Pagination;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle creation of listing model.
 *
 *
 * @package    app
 * @subpackage Frame\Mvc
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
abstract class AbstractListingModel extends AbstractBaseLayout
{
    /**
     * The listing table object, to store all the data.
     *
     * @var Table $ListingTable
     */
    protected $ListingTable;
    /**
     * Attribute to store the object of the listing form.
     *
     * @var FieldSet $ListingForm
     */
    protected $ListingForm;
    /**
     * Attribute to store the object of the listing sort field.
     *
     * @var ListingSort $ListingSort
     */
    protected $ListingSort;
    /**
     * Attribute to store the object of the listing action.
     *
     * @var ListingAction $ListingAction
     */
    protected $ListingAction;
    /**
     * Attribute to store the object of the pagination.
     *
     * @var Pagination $Pagination
     */
    protected $Pagination;
    /**
     * Property to store the reference code.
     *
     * @var string
     */
    private $DetailReferenceCode = '';

    /**
     * Property to store the trigger to show new button.
     *
     * @var boolean $EnableNewButton
     */
    private $EnableNewButton = true;

    /**
     * Property to store the trigger to show export xls button.
     *
     * @var boolean $EnableExportXls
     */
    private $EnableExportXls = true;

    /**
     * Property to store the trigger to show search button.
     *
     * @var boolean $EnableSearchButton
     */
    private $EnableSearchButton = false;

    /**
     * Base listing model constructor.
     *
     * @param string $nameSpace To store the name space of the page.
     * @param string $route To store the name space of the page.
     */
    public function __construct(string $nameSpace, string $route)
    {
        parent::__construct('Listing', $nameSpace, $route);
        $this->ListingForm = new FieldSet($this->Validation);
        $this->ListingSort = new ListingSort($this->Field);
        $this->Pagination = new Pagination('submit', $this->View->getFormAttribute('id'));
        $this->ListingTable = new Table('ListingTable');
        $this->ListingTable->addTableAttribute('class', 'table table-bordered jambo_table');
    }

    /**
     * Function to create the listing page.
     *
     * @return array
     */
    public function createView(): array
    {
        if ($this->ListingForm->isFieldsExist() === true) {
            $listingForm = '<div class="col-12">';
            $listingForm .= $this->ListingForm;
            $listingForm .= '</div>';
            $listingForm .= '<div class="clearfix"></div>';
            $this->View->addContent('listing_form', $listingForm);
            $this->EnableSearchButton = true;
        }
        # Create portlet
        $portlet = new Portlet('ListingTablePortlet', 'Listing');
        $portlet->setIcon(Icon::List);
        # Add pagination to portlet.
        $portlet->setPagination($this->Pagination);

        # Setup Listing Table.
        $this->ListingTable->setStartRowNumber($this->Pagination->getOffset());
        $portlet->addTable($this->ListingTable);
        $this->View->addContent('listing_table', $portlet);
        # Add default button to the view.
        $this->loadDefaultButton();

        return parent::createView();
    }

    /**
     * Function to get listing table.
     *
     * @return Table
     */
    public function getListingTable(): Table
    {
        return $this->ListingTable;
    }


    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        $this->View->setNumberOfButton(3);
        if ($this->EnableSearchButton === true) {
            $btnSearch = new SubmitButton('btnSearch', Trans::getWord('search'), 'doSearch', $this->getMainFormId());
            $btnSearch->setEnableLoading(false)
                ->setIcon(Icon::Search)
                ->btnInfo()
                ->pullRight();
            $this->View->addButton($btnSearch);
        }
        if ($this->EnableNewButton === true && empty($this->getUpdateRoute()) === false && $this->isAllowInsert() === true) {
            $btnNew = new HyperLink('btnNew', Trans::getWord('new'), url($this->getUpdateRoute()));
            $btnNew->viewAsButton();
            $btnNew->setIcon(Icon::Plus)->pullRight()->viewAsButton()->btnSuccess();
            $this->View->addButton($btnNew);
        }
        if ($this->EnableExportXls === true && $this->isAllowExportExcel() === true) {
            $btnXls = new SubmitButton('btnExportXls', Trans::getWord('exportXls'), 'doExportXls', $this->getMainFormId());
            $btnXls->setIcon(Icon::Download)->setEnableLoading(false)->btnPrimary()->pullRight();
            $this->View->addButton($btnXls);
        }
        $btnReload = new Button('btnReload', Trans::getWord('reload'), 'button');
        $btnReload->setIcon(Icon::Refresh)->pullRight()->btnWarning();
        $btnReload->addAttribute('onclick', 'App.reloadWindow()');
        $this->View->addButton($btnReload);
        if ($this->PopupLayout === true) {
            $btnClose = new Button('btnClose', Trans::getWord('close'), 'button');
            $btnClose->setIcon(Icon::Close)->btnDanger()->pullRight();
            $btnClose->addAttribute('onclick', 'App.closeWindow()');
            $this->View->addButton($btnClose);
        }
    }

    /**
     * Function to load pagination.
     *
     * @return void
     */
    public function loadPagination(): void
    {
        if ($this->isValidParameter($this->Pagination->getFieldId()) === true) {
            $this->Pagination->setActivePaging($this->getStringParameter($this->Pagination->getFieldId()));
        }
        $this->Pagination->setTotalRows($this->getTotalRows());

    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    abstract protected function getTotalRows(): int;

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    abstract public function loadSearchForm(): void;


    /**
     * Abstract function to load sorting field.
     *
     * @return void
     */
    public function loadSortingOptions(): void
    {
        if ($this->isValidParameter($this->ListingSort->getSortId()) === true) {
            $this->ListingSort->setSelectedField($this->getStringParameter($this->ListingSort->getSortId()));
        }
        if ($this->ListingSort->isExist() === true) {
            $this->ListingForm->addField(Trans::getWord('sortBy'), $this->ListingSort->getSortingField());
        }
    }

    /**
     * Function to export data into excel file.
     *
     * @return void
     */
    public function doExportXls(): void
    {
        if (empty($this->ListingTable->getRows()) === false) {
            $excel = new Excel();
            $excel->addSheet('listing', $this->PageSetting->getPageTitle());
            $excel->setFileName($this->PageSetting->getPageDescription() . '.xlsx');
            $sheet = $excel->getSheet('listing', true);
            $excelTable = new ExcelTable($excel, $sheet);
            $excelTable->setTable($this->ListingTable);
            $excelTable->writeTable();
            $excel->setActiveSheet('listing');
            $excel->createExcel();
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    abstract public function loadResultTable(): void;

    /**
     * Function to check is the validation rule exist or not.
     *
     * @return boolean
     */
    public function isValidationExist(): bool
    {
        return $this->Validation->isValidationExist();
    }


    /**
     * Function to load data from database.
     *
     * @param string $query to store the query selection.
     * @param array $columns to store the query selection.
     *
     * @return array
     */
    protected function loadDatabaseRow(string $query, $columns = []): array
    {
        # Set Limit query
        if ($this->Pagination->isPagingEnable() === true && ($this->isValidParameter($this->getActionId()) === false || mb_strtolower($this->getStringParameter($this->getActionId())) === 'dosearch')) {

            $query .= ' LIMIT ' . $this->Pagination->getRowsPerPage() . ' OFFSET ' . $this->Pagination->getOffset();
        }

        # return the data.
        return parent::loadDatabaseRow($query, $columns);
    }


    /**
     * Function to get the limit per page from pagination.
     * @return int
     */
    protected function getLimitTable(): int
    {
        if ($this->Pagination->isPagingEnable() === true && ($this->isValidParameter($this->getActionId()) === false || mb_strtolower($this->getStringParameter($this->getActionId())) === 'dosearch')) {
            return $this->Pagination->getRowsPerPage();
        }

        # return the data.
        return 0;
    }

    /**
     * Function to get the limit per page from pagination.
     * @return int
     */
    protected function getLimitOffsetTable(): int
    {
        if ($this->Pagination->isPagingEnable() === true && ($this->isValidParameter($this->getActionId()) === false || mb_strtolower($this->getStringParameter($this->getActionId())) === 'dosearch')) {
            return $this->Pagination->getOffset();
        }

        # return the data.
        return 0;
    }

    /**
     * Function to get the detail reference code.
     *
     * @return string
     */
    protected function getDetailReferenceCode(): string
    {
        return $this->DetailReferenceCode;
    }

    /**
     * Function to set the detail reference code.
     *
     * @param string $detailReferenceCode To store the reference code.
     *
     * @return void
     */
    protected function setDetailReferenceCode(string $detailReferenceCode): void
    {
        $this->DetailReferenceCode = $detailReferenceCode;
    }


    /**
     * Get Contact Person data
     *
     * @param string $query to store the query selection.
     * @param string $field_id to store the field id of the data.
     *
     * @return int
     */
    protected function loadTotalListingRows(string $query, string $field_id = 'total_rows'): int
    {
        $results = 0;
        $data = DB::select($query);
        if (count($data) === 1) {
            $results = DataParser::objectToArray($data[0], [$field_id])[$field_id];
        } else {
            Message::throwMessage('Invalid query to get the total of the rows');
        }

        # return the data.
        return $results;
    }

    /**
     * Function to enable new button.
     *
     * @param bool $disable To store the option value.
     *
     * @return void
     */
    protected function disableNewButton(bool $disable = true): void
    {
        $this->EnableNewButton = !$disable;
    }

    /**
     * Function to enable new button.
     *
     * @param bool $disable To store the option value.
     *
     * @return void
     */
    protected function disableExportButton(bool $disable = true): void
    {
        $this->EnableExportXls = !$disable;
    }

    /**
     * Function to get the the detail route.
     *
     * @return string
     */
    protected function getUpdateRoute(): string
    {
        return $this->PageSetting->getPageRoute() . '/detail';
    }

    /**
     * Function to get the the view route.
     *
     * @return string
     */
    protected function getViewRoute(): string
    {
        return $this->PageSetting->getPageRoute() . '/view';
    }

    /**
     * Function to get the the view route.
     *
     * @return string
     */
    protected function getRoute(): string
    {
        return $this->PageSetting->getPageRoute();
    }

    /**
     * Function to get the the default route.
     *
     * @return string
     */
    public function getDefaultRoute(): string
    {
        return '/';
    }

    /**
     * Function to check update access.
     *
     * @return bool
     */
    protected function isAllowUpdate(): bool
    {
        return $this->PageSetting->checkPageRight('AllowUpdate');
    }

    /**
     * Function to check insert access.
     *
     * @return bool
     */
    protected function isAllowInsert(): bool
    {
        return $this->PageSetting->checkPageRight('AllowInsert');
    }

    /**
     * Function to check export access.
     *
     * @return bool
     */
    protected function isAllowExportExcel(): bool
    {
        return $this->PageSetting->checkPageRight('AllowExportXls');
    }


}
