<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Http\Controllers;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractStatisticModel;
use Exception;

/**
 *
 *
 * @package    app
 * @subpackage Http\Controllers
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class StatisticController extends Controller
{
        /**
     * Property to store the base path of the page.
     *
     * @var string $PageCategory
     */
    private $PageCategory = 'Statistic';

    /**
     * Property to the object of model.
     *
     * @var AbstractStatisticModel $Model
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
     * @param string $nameSpace    To store the Name Space of the page.
     *
     * @return mixed
     */
    public function doControl(string $nameSpace)
    {
        try {
            # Load Model Object
            $this->loadModel($nameSpace, request()->all());

            if ($this->Model === null) {
                Message::throwMessage(Trans::getWord('pageNotFound', 'message'), 'ERROR');
            }

            # do control page
            return $this->doControlStatistic();

        } catch (Exception $e) {
            return view('errors.general', ['error_message' => $e->getMessage(), 'back_url' => $this->FallBackUrl]);
        }
    }


    /**
     * Function to control statistic screen
     *
     * @return mixed
     */
    private function doControlStatistic()
    {
        if ($this->Model->isPopupLayout() === false) {
            $this->FallBackUrl = url('/' . $this->Model->getDefaultRoute());
        }

        # load page setting model.
        $this->Model->initPageSetting();
        # check Modal.
        # show result table if the validation is true.
        $this->Model->loadValidationRole();
        $this->Model->loadSearchForm();
        if ($this->Model->isValidPostValues() === true) {
            $this->Model->loadViews();
            # Check if form valid then call the action function, eq : function to export excel file.
            if ($this->isTokenFormValid() === true && $this->Model->isExistParameter($this->getFormAction()) === true) {
                $actionFunction = $this->Model->getStringParameter($this->getFormAction());
                if ($actionFunction !== null && method_exists($this->Model, $actionFunction) === true) {
                    try {
                        $this->Model->$actionFunction();
                    } catch (Exception $e) {
                        Message::throwMessage($e->getMessage(), 'ERROR');
                    }
                }
            }
        } else {
            $this->Model->addErrorMessage(Trans::getWord('invalidCheckFound', 'message'));
        }
        $content = $this->Model->createView();
        if ($this->Model->isPopupLayout() === true) {
            return view('popup_statistic', $content);
        }

        return view('statistic', $content);
    }

    /**
     * Function to load object model
     *
     * @param string $nameSpace  To store the page path.
     * @param array  $parameters To store the page category.
     *
     * @return void
     */
    private function loadModel(string $nameSpace, array $parameters = []): void
    {
        if (empty($nameSpace) === true) {
            Message::throwMessage('Not allowed empty name_space for statistic page.', 'ERROR');
        }
        $pagePath = $this->PageCategory . '\\' . str_replace('/', '\\', $nameSpace);
        # Check custom class
        $modelPath = $this->getCustomPath($pagePath);
        if ($this->validateModelClass($modelPath) === false) {
            $modelPath = 'App\\Model\\' . $pagePath;
            if ($this->validateModelClass($modelPath) === false) {
                Message::throwMessage('Not found name_space for statistic page.', 'ERROR');
            }

        }
        # load Class
        $this->Model = new $modelPath($parameters);
    }
}
