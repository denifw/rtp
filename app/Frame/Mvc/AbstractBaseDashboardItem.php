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
use App\Model\Dao\Setting\DashboardDetailDao;

/**
 * Class to manage the create dashboard item.
 *
 * @package    app
 * @subpackage Model
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
abstract class AbstractBaseDashboardItem extends AbstractBaseModel
{
    /**
     * Property to store the id.
     *
     * @var string $Id
     */
    protected $Id;

    /**
     * Property to store the title.
     *
     * @var string $Title
     */
    protected $Title;

    /**
     * Property to store the color of dashboard panel.
     *
     * @var string $Color
     */
    protected $Color;

    /**
     * Property to store the column grid class.
     *
     * @var string $ColumnGridClass
     */
    protected $ColumnGridClass;

    /**
     * Property to store the reference code.
     *
     * @var $ReferenceCode string
     */
    private $ReferenceCode = '';
    /**
     * Property to store the reference value.
     *
     * @var $ReferenceValue int
     */
    private $ReferenceValue = 0;

    /**
     * Property to store the content.
     *
     * @var string $Content
     */
    protected $Content = '';
    /**
     * Property to store the json response.
     *
     * @var array $JsonResponses
     */
    protected $JsonResponses = [];

    /**
     * Property to store the list parameters
     *
     * @var array $CallBackParameters ;
     */
    protected $CallBackParameters = [];

    /**
     * Property to store auto reload time.
     *
     * @var int $AutoReloadTime
     */
    protected $AutoReloadTime = 0;

    /**
     * Property to store main dashboard's page right
     *
     * @var array $PageRights ;
     */
    protected $DashboardPageRights = [];

    /**
     * Property to enable refresh button
     *
     * @var bool $EnableReload
     */
    protected $EnableReload = true;

    /**
     * Property to enable edit button
     *
     * @var bool $EnableEdit
     */
    protected $EnableEdit = true;

    /**
     * Property to enable delete button
     *
     * @var bool $EnableDelete
     */
    protected $EnableDelete = true;

    /**
     * Property to store modal delete dashboard item.
     *
     * @var  \app\Frame\Gui\Modal $ModalDelete
     */
    public $ModalDelete;


    /**
     * Function to load the chart data.
     *
     * @return void
     */
    abstract protected function loadContent(): void;

    /**
     * Function to load the chart data.
     *
     * @return void
     */
    abstract protected function loadJsonResponse(): void;

    /**
     * Function to load the java script data.
     *
     * @return string
     */
    abstract protected function loadJavaScript(): string;

    /**
     * Function to load addtional call back parameter.
     *
     * @return void
     */
    abstract protected function loadAddtionalCallBackParameter(): void;

    /**
     * Constructor to load when there is a new object created.
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setReferenceCode('dsd_id');
    }

    /**
     * Function to set the chart id
     *
     * @param string $id To store the id of the chart.
     *
     * @return void
     */
    public function setId(string $id): void
    {
        $this->Id = $id;
    }

    /**
     * Function to set the chart id
     *
     * @param string $title To store the title of the chart.
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->Title = $title;
    }

    /**
     * Function to set post value from the request.
     *
     * @param array $parameters To store the list input from request.
     *
     * @return void
     */
    public function addCallBackParameters(array $parameters): void
    {
        foreach ($parameters as $key => $value) {
            $this->addCallBackParameter($key, $value);
        }
    }

    /**
     * Function to add call back parameter.
     *
     * @param string $parId    The unique id used in html.
     * @param string $parValue The value that the field will contain.
     *
     * @return void
     */
    public function addCallBackParameter($parId, $parValue = ''): void
    {
        if (empty($parId) === false) {
            if (array_key_exists($parId, $this->CallBackParameters) === false) {
                $this->CallBackParameters[$parId] = $parValue;
            } else {
                Message::throwMessage('Duplicate parameter id for chart system with id = ' . $parId . '.');
            }
        } else {
            Message::throwMessage('Invalid empty parameter id for call back chart parameter.');
        }
    }

    /**
     * Function to set disable reload button.
     *
     * @param bool $disable To set disable value.
     *
     * @return void
     */
    public function setDisableReloadButton(bool $disable = true): void
    {
        $this->EnableReload = true;
        if ($disable === true) {
            $this->EnableReload = false;
        }
    }

    /**
     * Function to set disable edit button.
     *
     * @param bool $disable To set disable value.
     *
     * @return void
     */
    public function setDisableEditButton(bool $disable = true): void
    {
        $this->EnableEdit = true;
        if ($disable === true) {
            $this->EnableEdit = false;
        }
    }

    /**
     * Function to set disable delete button.
     *
     * @param bool $disable To set disable value.
     *
     * @return void
     */
    public function setDisableDeleteButton(bool $disable = true): void
    {
        $this->EnableDelete = true;
        if ($disable === true) {
            $this->EnableDelete = false;
        }
    }

    /**
     * Function to set enable button reload
     *
     * @param int $time To store the time to reload the table.
     *
     * @return void
     */
    public function setAutoReloadTime($time): void
    {
        $this->AutoReloadTime = $time;
    }

    /**
     * Function to load the chart.
     *
     * @return string
     */
    public function doCreate(): string
    {
        if (empty($this->Id) === true) {
            Message::throwMessage('Invalid id for the chart object.');
        }
        if ($this->isValidParameter('route') === false) {
            Message::throwMessage('Invalid route for the chart object.');
        }
        $this->addCallBackParameter($this->getReferenceCode(), $this->getReferenceValue());
        $this->loadAddtionalCallBackParameter();
        $this->loadContent();

        return $this->Content;
    }

    /**
     * Function to set page right.
     *
     * @param array $dashboardPageRights The main dashboard page right.
     *
     * @return void
     */
    public function setPageRight(array $dashboardPageRights): void
    {
        $this->DashboardPageRights = $dashboardPageRights;
    }

    /**
     * Function to get the page title.
     *
     * @param string $right To store the right name.
     *
     * @return boolean
     */
    public function checkPageRight(string $right): bool
    {
        if ($this->User->isUserSystem() === 'Y') {
            return true;
        }
        if (array_key_exists($right, $this->DashboardPageRights) === true) {
            $result = $this->DashboardPageRights[$right];
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Function to load the json response.
     *
     * @return array
     */
    public function getJsonResponse(): array
    {
        $this->setId($this->getStringParameter('id'));
        $this->loadDashboardItemSetting();
        $this->setTitle($this->Title);
        $this->JsonResponses['title'] = $this->Title;
        $this->JsonResponses['color'] = $this->Color;
        $this->JsonResponses['gridClass'] = $this->ColumnGridClass;
        $this->loadJsonResponse();

        return $this->JsonResponses;
    }

    /**
     * Function to set the route parameter.
     *
     * @param string $route To store the route name.
     *
     * @return void
     */
    public function setRoute(string $route): void
    {
        if ($this->isValidParameter('route') === false) {
            $this->setParameter('route', $route);
        }
    }

    /**
     * Function to set the reference code.
     *
     * @param string $referenceCode To store the reference code.
     *
     * @return void
     */
    public function setReferenceCode($referenceCode): void
    {
        $this->ReferenceCode = $referenceCode;
    }

    /**
     * Function to get the reference value.
     *
     * @return integer
     */
    public function getReferenceValue(): int
    {
        if (empty($this->ReferenceValue) === true && empty($this->getReferenceCode()) === false) {
            $this->ReferenceValue = (int)$this->getIntParameter($this->getReferenceCode());
        }

        return $this->ReferenceValue;
    }

    /**
     * Function to set the reference value.
     *
     * @param integer $referenceValue To store the last key value.
     *
     * @return void
     */
    public function setReferenceValue($referenceValue): void
    {
        $this->ReferenceValue = $referenceValue;
    }

    /**
     * Function to get the  reference value.
     *
     * @return string
     */
    public function getReferenceCode(): string
    {
        return $this->ReferenceCode;
    }

    /**
     * Function to set filters
     *
     * @return void
     */
    public function loadDashboardItemSetting(): void
    {
        $wheres[] = '(dsd.dsd_id = \'' . $this->getReferenceValue() . '\')';
        $data = DashboardDetailDao::loadData($wheres, [], 1);
        $this->Title = $data[0]['dsd_title'];
        $this->Color = $data[0]['dsd_color'];
        $this->ColumnGridClass = 'col-lg-' . $data[0]['dsd_grid_large'] . ' col-md-' . $data[0]['dsd_grid_medium'] . ' col-sm-' . $data[0]['dsd_grid_small'] . ' col-xs-' . $data[0]['dsd_grid_xsmall'];
    }

}
