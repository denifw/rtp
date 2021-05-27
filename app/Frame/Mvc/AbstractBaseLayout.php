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

use App\Model\Dao\Notification\NotificationReceiverDao;
use App\Frame\Formatter\DataParser;
use App\Frame\Gui\Html\Field;
use App\Frame\System\PageSetting;
use App\Frame\System\Validation;
use App\Frame\System\View;
use Illuminate\Support\Facades\DB;

/**
 * Class to manage the layout creation.
 *
 * @package    app
 * @subpackage Model
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
abstract class AbstractBaseLayout extends AbstractBaseModel
{

    /**
     * Property to store all the file name of the view.
     *
     * @var \App\Frame\System\View $View
     */
    protected $View;

    /**
     * Property to store the right of the page.
     *
     * @var \App\Frame\System\PageSetting $PageSetting
     */
    protected $PageSetting;

    /**
     * Property to store the validation handler.
     *
     * @var \App\Frame\System\Validation $Validation
     */
    protected $Validation;

    /**
     * Attribute for the field object.
     *
     * @var \App\Frame\Gui\Html\Field $Field
     */
    protected $Field;

    /**
     * Property to store popup trigger to hide some layout.
     *
     * @var bool $PopupLayout
     */
    protected $PopupLayout;

    /**
     * Base model constructor.
     *
     * @param string $pageCategory To store the page category of the model.
     * @param string $pagePath     To store the name path of the page.
     * @param string $pageRoute    To store the name space of the page.
     */
    public function __construct(string $pageCategory, string $pagePath, string $pageRoute)
    {
        parent::__construct();
        $this->loadNameSpaceModel($pageCategory, $pagePath);
        $this->PageSetting = new PageSetting($pageCategory, $pageRoute, $this->User);
        $this->View = new View('main_form', 'page', 'systemPage');
        $this->Validation = new Validation();
        $this->Field = new Field($this->Validation);
        $this->loadCustomScript($pageCategory);
        $this->loadCustomStyle($pageCategory);
        $this->PopupLayout = false;
    }

    /**
     * Function to init page setting
     *
     * @return void
     */
    public function initPageSetting(): void
    {
        $this->PageSetting->loadPageSetting();
        $this->View->setTitle($this->PageSetting->getPageTitle());
        $this->View->setDescription($this->PageSetting->getPageDescription());
        $this->View->setActivePage($this->PageSetting->getPageUrl());
    }


    /**
     * Function to get page setting
     *
     * @return \App\Frame\System\PageSetting
     */
    public function getPageSetting(): PageSetting
    {
        return $this->PageSetting;
    }

    /**
     * Function to get view object
     *
     * @return \App\Frame\System\View
     */
    public function getView(): View
    {
        return $this->View;
    }

    /**
     * Function to get validation object
     *
     * @return \App\Frame\System\Validation
     */
    public function getValidation(): Validation
    {
        return $this->Validation;
    }

    /**
     * Function to get field object
     *
     * @return \App\Frame\Gui\Html\Field
     */
    public function getField(): Field
    {
        return $this->Field;
    }

    /**
     * Function to get the action id parameter.
     *
     * @return string
     */
    public function getActionId(): string
    {
        return $this->getMainFormId() . '_action';
    }

    /**
     * Function to get the form action.
     *
     * @return null|string
     */
    public function getFormAction(): ?string
    {
        return $this->getStringParameter($this->getActionId());
    }

    /**
     * Function to add success message to the view.
     *
     * @param string $message To store the value of the data.
     *
     * @return void
     */
    public function addSuccessMessage($message): void
    {
        if (empty($message) === false) {
            $this->View->addInfoMessage($message);
        }
    }

    /**
     * Function to add error message to the view.
     *
     * @param string $message To store the value of the data.
     *
     * @return void
     */
    public function addErrorMessage($message): void
    {
        if (empty($message) === false) {
            $this->View->addErrorMessage($message);
        }
    }

    /**
     * Function to add warning message to the view.
     *
     * @param string $message To store the value of the data.
     *
     * @return void
     */
    public function addWarningMessage($message): void
    {
        if (empty($message) === false) {
            $this->View->addWarningMessage($message);
        }
    }

    /**
     * Function to get name space of the model.
     *
     * @return boolean
     */
    public function isValidPostValues(): bool
    {
        if ($this->Validation->isValidated() === false) {
            $this->Validation->setInputs($this->getAllParameters());
            $this->Validation->doValidation();
        }

        return $this->Validation->isValidInputs();
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
    }

    /**
     * Function to set the popup layout.
     *
     * @return void
     */
    private function checkPopupLayout(): void
    {
        if ($this->isValidParameter('pv') === true) {
            $this->PopupLayout = true;
            $this->View->setEnableMenu(false);
        }
    }

    /**
     * Function to check is the layout popup or not.
     *
     * @return bool
     */
    public function isPopupLayout(): bool
    {
        $this->checkPopupLayout();

        return $this->PopupLayout;
    }

    /**
     * Function to get main form id from view.
     *
     * @return string
     */
    public function getMainFormId(): string
    {
        return $this->View->getFormAttribute('id');
    }

    /**
     * Function to load all the view data and convert it to array.
     *
     * @return array
     */
    public function createView(): array
    {
        $this->doUpdateNotification();

        return $this->View->createView();
    }

    /**
     * Function to check is call action
     *
     * @return bool
     */
    public function isCallActionFunction(): bool
    {
        $result = false;
        $actionFunction = $this->getStringParameter($this->getActionId());
        if ($actionFunction !== null && $actionFunction !== '' && method_exists($this, $actionFunction) === true) {
            $result = true;
        }

        return $result;
    }

    /**
     * Function to load all the view data and convert it to array.
     *
     * @return void
     */
    private function doUpdateNotification(): void
    {
        $urlKey = md5($this->PageSetting->getPageFullUrl());
        $receivers = NotificationReceiverDao::getNotificationReceiverByUrlKeyAndUser($urlKey, $this->User->getId(), $this->User->getSsId());
        if (empty($receivers) === false) {
            $nfrDao = new NotificationReceiverDao();
            foreach ($receivers as $row) {
                $nfrDao->doUpdateTransaction($row['nfr_id'], [
                    'nfr_delivered' => 'Y',
                    'nfr_read_by' => $this->User->getId(),
                    'nfr_read_on' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    /**
     * Function to load data from database.
     *
     * @param string $query   to store the query selection.
     * @param array  $columns to store the query selection.
     *
     * @return array
     */
    protected function loadDatabaseRow(string $query, $columns = []): array
    {
        $results = [];
        $data = DB::select($query);
        if (empty($data) === false) {
            $results = DataParser::arrayObjectToArray($data, $columns);
        }

        # return the data.
        return $results;
    }

    /**
     * Function to get custom style path.
     *
     * @param string $pageCategory To store the category page.
     *
     * @return void
     */
    private function loadCustomStyle($pageCategory): void
    {
        $path = '/dist/css/' . mb_strtolower($pageCategory) . '/' . mb_strtolower($this->NameSpace) . '.css';
        $publicPath = public_path(str_replace('/', '\\', $path));
        if (file_exists($publicPath) === true) {
            $this->View->setPathCustomStyle($path);
        }
    }

    /**
     * Function to get custom script path.
     *
     * @param string $pageCategory To store the category page.
     *
     * @return void
     */
    private function loadCustomScript($pageCategory): void
    {
        $path = '/dist/js/' . mb_strtolower($pageCategory) . '/' . mb_strtolower($this->NameSpace) . '.js';

        $publicPath = public_path(str_replace('/', '\\', $path));
        if (file_exists($publicPath) === true) {
            $this->View->setPathCustomScript($path);
        }
    }

}
