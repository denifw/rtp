<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Http\Controllers;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Mvc\AbstractListingModel;
use App\Frame\Mvc\AbstractViewerModel;
use Exception;

/**
 * Class to handle page controller
 *
 * @package    app
 * @subpackage Http\Controllers
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class PageController extends Controller
{

    /**
     * Property to store the static allowed page category inside the system.
     *
     * @var array $AllowedPageCategory
     * */
    private $AllowedPageCategory = [
        'listing' => 'Listing',
        'detail' => 'Detail',
        'view' => 'Viewer',
        'ajax' => 'Ajax',
    ];

    /**
     * Property to the object of model.
     *
     * @var mixed $Model
     * */
    private $Model;

    /**
     * Property to the url for fall back when there is an exception catch.
     *
     * @var string $FallBackUrl
     * */
    private $FallBackUrl = '';

    /**
     * Function to control page system.
     *
     * @param string $pageCategory To store the category of page
     *                             listing => for page listing
     *                             detail => for page detail
     *                             view => for page view
     *                             ajax => for ajax call
     * @param string $nameSpace    To store the Name Space of the page.
     *
     * @return mixed
     */
    public function doControl(string $pageCategory, string $nameSpace)
    {
        $pageCategory = mb_strtolower($pageCategory);
        try {
            # Check if page category is registered inside AllowedPageCategory
            if (array_key_exists($pageCategory, $this->AllowedPageCategory) === false) {
                Message::throwMessage(Trans::getWord('pageNotFound', 'message'), 'ERROR');
            }

            # Load Model Object
            $this->loadModel($this->AllowedPageCategory[$pageCategory], $nameSpace, request()->all());

            # do control page
            return $this->doControlPage($pageCategory);

        } catch (Exception $e) {
            if ($pageCategory === 'ajax' || request()->ajax() === true) {
                return response()->json($e->getMessage());
            }

            return view('errors.general', ['error_message' => $e->getMessage(), 'back_url' => $this->FallBackUrl]);
        }
    }

    /**
     * Function to control page system.
     *
     * @param string $pageCategory To store the category of page
     *
     * @return mixed
     */
    private function doControlPage(string $pageCategory)
    {
        if ($this->Model === null) {
            Message::throwMessage(Trans::getWord('pageNotFound', 'message'), 'ERROR');
        }
        if ($pageCategory === 'listing') {
            return $this->doControlListing($this->Model);
        }
        if ($pageCategory === 'detail') {
            return $this->doControlDetail($this->Model);
        }
        if ($pageCategory === 'view') {
            return $this->doControlViewer($this->Model);
        }
        if ($pageCategory === 'ajax') {
            return $this->doControlAjax($this->Model);
        }
        return view('errors.404');
    }

    /**
     * Function to load object model
     *
     * @param string $category   To store the page path.
     * @param string $nameSpace  To store the page path.
     * @param array  $parameters To store the page category.
     *
     * @return void
     */
    private function loadModel(string $category, string $nameSpace, array $parameters = []): void
    {
        if (empty($nameSpace) === true) {
            Message::throwMessage('Not allowed empty name_space for ' . $category . ' page.', 'ERROR');
        }
        $pagePath = $category . '\\' . str_replace('/', '\\', $nameSpace);
        # Check custom class
        $modelPath = $this->getCustomPath($pagePath);
        if ($this->validateModelClass($modelPath) === false) {
            $modelPath = 'App\\Model\\' . $pagePath;
            if ($this->validateModelClass($modelPath) === false) {
                Message::throwMessage('Not found name_space for ' . $category . ' page.', 'ERROR');
            }

        }
        # load Class
        $this->Model = new $modelPath($parameters);
    }

    /**
     * Function to control listing screen
     *
     * @param AbstractListingModel $model
     *
     * @return mixed
     */
    protected function doControlListing(AbstractListingModel $model)
    {
        if ($model->isPopupLayout() === false) {
            $this->FallBackUrl = url($model->getDefaultRoute());
        }

        # load page setting model.
        $model->initPageSetting();
        $model->loadPagination();
        $model->loadValidationRole();
        $model->loadSearchForm();
        $model->loadSortingOptions();

        # show result table if the validation is true.
        if ($model->isValidPostValues() === true) {
            $model->loadResultTable();
            # Check if form valid then call the action function, eq : function to export excel file.
            if ($this->isTokenFormValid() === true && $model->isExistParameter($this->getFormAction()) === true) {
                $actionFunction = $model->getStringParameter($this->getFormAction());
                if ($actionFunction !== null && method_exists($model, $actionFunction) === true) {
                    try {
                        $model->$actionFunction();
                    } catch (Exception $e) {
                        Message::throwMessage($e->getMessage(), 'ERROR');
                    }
                }
            }
        } else {
            $model->addErrorMessage(Trans::getWord('invalidCheckFound', 'message'));
        }
        $content = $model->createView();

        if ($model->isPopupLayout() === true) {
            return view('popup_listing', $content);
        }
        return view('listing', $content);
    }

    /**
     * Function to control detail screen
     *
     * @param AbstractFormModel $model
     *
     * @return mixed
     */
    protected function doControlDetail(AbstractFormModel $model)
    {
        # Set fallback url when the page is not a pop up layout.
        if ($model->isPopupLayout() === false) {
            $this->FallBackUrl = url('/' . $model->getDefaultRoute());
        }

        # load page setting model.
        $model->initPageSetting();

        # Check if it is a submit action.
        if ($this->isTokenFormValid() === true && $model->isExistParameter($this->getFormAction()) === true) {
            $model->doTransaction();
        }
        # Load existing data if it's an update page.
        if ($model->isUpdate() === true) {
            $detailData = $model->loadData();
            if (empty($detailData) === false) {
                $model->setParameters($detailData);
            } else {
                Message::throwMessage(Trans::getWord('noDataFound', 'message'), 'ERROR');
            }
        }
        # Load form and create view.
        $model->loadForm();
        $model->removeActiveModal();
        $content = $model->createView();

        # return view
        if ($model->isPopupLayout() === true) {
            return view('popup_detail', $content);
        }

        return view('detail', $content);
    }

    /**
     * Function to control view screen
     *
     * @param AbstractViewerModel $model
     *
     * @return mixed
     */
    protected function doControlViewer(AbstractViewerModel $model)
    {
        # Set fallback url when the page is not a pop up layout.
        if ($model->isPopupLayout() === false) {
            $this->FallBackUrl = url('/' . $model->getDefaultRoute());
        }

        # load page setting model.
        $model->initPageSetting();
        # check Modal.
        if ($model->isUpdate() === false) {
            Message::throwMessage(Trans::getWord('pageNotFound', 'message'), 'ERROR');
        }

        # Check if it is a submit action.
        if ($this->isTokenFormValid() === true && $model->isExistParameter($this->getFormAction()) === true) {
            $model->doTransaction();
        }

        # Load existing data.
        $detailData = $model->loadData();
        if (empty($detailData) === false) {
            $model->setParameters($detailData);
        } else {
            Message::throwMessage(Trans::getWord('noDataFound', 'message'), 'ERROR');
        }

        # Load form and create view.
        $model->loadForm();
        $model->removeActiveModal();
        $content = $model->createView();

        # Return view.
        if ($model->isPopupLayout() === true) {
            return view('popup_detail', $content);
        }

        return view('detail', $content);
    }

    /**
     * Function to control view screen
     *
     * @param AbstractBaseAjaxModel $model
     *
     * @return mixed
     */
    protected function doControlAjax(AbstractBaseAjaxModel $model)
    {
        # Request type must only from ajax call if the env debug status is false or parameter debug = 1
        if (env('APP_DEBUG', false) === false && $model->getIntParameter('debug', 0) !== 1 && request()->ajax() === false) {
            Message::throwMessage(Trans::getWord('pageNotFound', 'message'), 'ERROR');
        }

        if ($model->isValidParameter('callBackFunction') === false || method_exists($model, $model->getStringParameter('callBackFunction')) === false) {
            Message::throwMessage('Invalid CallBack Function.', 'ERROR');
        }

        $functionName = $model->getStringParameter('callBackFunction');
        $data = $model->$functionName();
        if (empty($data) === true) {
            Message::throwMessage(Trans::getWord('noDataFound', 'message'), 'ERROR');
        }
        return response()->json($data);

    }

}
