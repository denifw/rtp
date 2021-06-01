<?php
/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 10/04/2019
 * Time: 14:38
 */

namespace App\Http\Controllers;


use App\Frame\Exceptions\ErrorException;
use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Frame\Mvc\AbstractBaseDashboardItem;
use App\Frame\Mvc\AbstractDashboardModel;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Mvc\AbstractListingModel;
use App\Frame\Mvc\AbstractViewerModel;
use App\Frame\System\View;

class AbstractBaseController extends Controller
{

    /**
     * Function to control listing screen
     *
     * @param AbstractDashboardModel $model
     *
     * @return mixed
     */
    protected function doControlDashboard(AbstractDashboardModel $model)
    {
        try {
            # load page setting model.
            $model->initPageSetting();
//            $removeModal = false;
            if ($this->isTokenFormValid() === true && $model->isExistParameter($this->getFormAction()) === true) {
                $model->doTransaction();
                if ($model->isSuccessTransaction() === true) {
                    return redirect($model->getDefaultRoute() . '?'. $model->getReferenceCode() .'=' . $model->getReferenceValue());
                }
            }
            if ($model->isUpdate() === true) {
                $dashboardData = $model->loadData();
                if (empty($dashboardData) === false) {
                    $model->setParameters($dashboardData);
                } else {
                    throw new ErrorException(Trans::getWord('noDataFound', 'message'));
                }
            }
            $model->loadDashboardItem();
//            if ($removeModal === true) {
//                $model->removeActiveModal();
//            }
            $contents = $model->createView();
        } catch (\Exception $e) {
            $view = new View('error_form', 'Error Page', 'Error Message');
            $view->addErrorMessage($e->getMessage());
            $view->setErrorMessages($model->getView()->getErrorMessages());
            $view->setInfoMessages($model->getView()->getInfoMessages());
            $view->setWarningMessages($model->getView()->getWarningMessages());
            $contents = $view->createErrorView();
        }

        return view('detail', $contents);
    }
    /**
     * Function to control listing screen
     *
     * @param \App\Frame\Mvc\AbstractBaseDashboardItem $model
     *
     * @return mixed
     */
    protected function doControlDashboardItem(AbstractBaseDashboardItem $model)
    {
        if ($model === null) {
            return response()->json('Invalid dashboard item model.');
        }
        $model->setParameters(request()->all());

        return response()->json($model->getJsonResponse());
    }

    /**
     * Function to control listing screen
     *
     * @param \App\Frame\Mvc\AbstractListingModel $model
     *
     * @return mixed
     *
     * @deprecated Use Page Controller instead.
     */
    protected function doControlListing(AbstractListingModel $model)
    {
        try {
            # load page setting model.
            $model->initPageSetting();
            # check Modal.
            # show result table if the validation is true.
            $model->loadPagination();
            $model->loadValidationRole();
            $model->loadSearchForm();
            $model->loadSortingOptions();
            if ($model->isValidPostValues() === true) {
                $model->loadResultTable();
                # Check if form valid then call the action function, eq : function to export excel file.
                if ($this->isTokenFormValid() === true && $model->isExistParameter($this->getFormAction()) === true) {
                    $actionFunction = $model->getStringParameter($this->getFormAction());
                    if ($actionFunction !== null && method_exists($model, $actionFunction) === true) {
                        try {
                            $model->$actionFunction();
                        } catch (\Exception $e) {
                            throw new ErrorException($e->getMessage());
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
        } catch (\Exception $e) {
            $url = '';
            if ($model->isPopupLayout() === false) {
                $url = url('/' . $model->getDefaultRoute());
            }

            return view('errors.general', ['error_message' => $e->getMessage(), 'back_url' => $url]);
        }
    }

    /**
     * Function to control detail screen
     *
     * @param \App\Frame\Mvc\AbstractFormModel $model
     *
     * @return mixed
     *
     * @deprecated Use Page Controller instead.
     */
    protected function doControlDetail($model)
    {
        if (($model instanceof AbstractFormModel) === false) {
            return view('errors.general', ['error_message' => 'Invalid page model for detail page.', 'back_url' => '']);
        }
        try {
            # load page setting model.
            $model->initPageSetting();
            # check Modal.
            $removeModal = false;
            # Check if the form_action not empty
            if ($this->isTokenFormValid() === true && $model->isExistParameter($this->getFormAction()) === true) {
                $model->doTransaction();
            }
            # Load existing data if it's an update page.
            if ($model->isUpdate() === true) {
                $detailData = $model->loadData();
                if (empty($detailData) === false) {
                    $model->setParameters($detailData);
                } else {
                    throw new ErrorException(Trans::getWord('noDataFound', 'message'));
                }
            }
            # Load additional message from the status.
            $model->loadForm();

            if ($removeModal === true) {
                $model->removeActiveModal();
            }

            $content = $model->createView();
            if ($model->isPopupLayout() === true) {
                return view('popup_detail', $content);
            }

            return view('detail', $content);
        } catch (\Exception $e) {
            $url = '';
            if ($model->isPopupLayout() === false) {
                $url = url('/' . $model->getDefaultRoute());
            }

            return view('errors.general', ['error_message' => $e->getMessage(), 'back_url' => $url]);
        }

    }

    /**
     * Function to control view screen
     *
     * @param \App\Frame\Mvc\AbstractViewerModel $model
     *
     * @return mixed
     *
     * @deprecated Use Page Controller instead.
     */
    protected function doControlViewer(AbstractViewerModel $model)
    {
        try {
            # load page setting model.
            $model->initPageSetting();
            # check Modal.
            if ($model->isUpdate() === false) {
                throw new ErrorException(Trans::getWord('pageNotFound', 'message'));
            }
            $removeModal = false;
            # Check if the form_action not empty
            if ($this->isTokenFormValid() === true && $model->isExistParameter($this->getFormAction()) === true) {
                $model->doTransaction();
            }
            # Load existing data if it's an update page.
            $detailData = $model->loadData();
            if (empty($detailData) === false) {
                $model->setParameters($detailData);
            } else {
                throw new ErrorException(Trans::getWord('noDataFound', 'message'));
            }
            # Load additional message from the status.
            $model->loadForm();

            if ($removeModal === true) {
                $model->removeActiveModal();
            }

            $content = $model->createView();
            if ($model->isPopupLayout() === true) {
                return view('popup_detail', $content);
            }

            return view('detail', $content);
        } catch (\Exception $e) {
            $url = '';
            if ($model->isPopupLayout() === false) {
                $url = url('/' . $model->getDefaultRoute());
            }

            return view('errors.general', ['error_message' => $e->getMessage(), 'back_url' => $url]);
        }
    }

    /**
     * Function to control view screen
     *
     * @param \App\Frame\Mvc\AbstractBaseAjaxModel $model
     *
     * @return mixed
     *
     * @deprecated Use Page Controller instead.
     */
    protected function doControlAjax(AbstractBaseAjaxModel $model)
    {
        // if (request()->ajax() === false) {
        //     return response()->json('Invalid request type.');
        // }
        $message = null;
        if ($model->isValidParameter('callBackFunction') === true && method_exists($model, $model->getStringParameter('callBackFunction')) === true) {
            $functionName = $model->getStringParameter('callBackFunction');
            $data = $model->$functionName();
            if (empty($data) === false) {
                return response()->json($data);
            }

            return response()->json('No data found.');
        }

        return response()->json('Invalid ajax callback function.');
    }


    /**
     * Function to load object model
     *
     * @param string $category   To store the page path.
     * @param string $path       To store the page path.
     * @param array  $parameters To store the page category.
     *
     * @return mixed
     */
    protected function loadModel(string $category, string $path, array $parameters)
    {
        //@var AbstractFormModel $model To store the model.
        $model = null;
        if (empty($path) === false) {
            $pagePath = $category . '\\' . str_replace('/', '\\', $path);
            # Check custom class
            $modelPath = $this->getCustomPath($pagePath);
            if ($this->validateModelClass($modelPath) === false) {
                $modelPath = 'App\\Model\\' . $pagePath;
                if ($this->validateModelClass($modelPath) === false) {
                    Message::throwMessage('Invalid page path for model ' . $category . ' with path : ' . $path);
                }

            }
            # load Class
            $model = new $modelPath($parameters);
        } else {
            Message::throwMessage(Trans::getWord('pageNotFound', 'message'), 'ERROR');
        }

        return $model;
    }

}
