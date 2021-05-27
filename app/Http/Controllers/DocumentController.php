<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Http\Controllers;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Model\Document\Pdf\AbstractBasePdf;
use Exception;
/**
 * Class to control document.
 *
 * @package    app
 * @subpackage Http\Controller
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DocumentController extends Controller
{
    /**
     * Property to store the data that will be pass to the view.
     *
     * @var AbstractBasePdf $Model
     */
    protected $Model;


    /**
     * Function to control detail screen
     *
     * @return mixed
     */
    public function doControlPdf()
    {
        try {
            # Load Page model.
            $this->loadModel(request()->all());
            if (request()->ajax() === true) {
                return response()->json($this->Model->loadHtmlContent());
            }
            $this->Model->loadContent();
            $this->Model->createPdf();
            exit;
        } catch (Exception $e) {
            return view('errors.general', ['error_message' => $e->getMessage(), 'back_url' => '']);
        }
    }

    /**
     * Function to load object model
     *
     * @param array $parameters To store the request parameters.
     *
     * @return void
     */
    private function loadModel(array $parameters = []): void
    {
        if(array_key_exists('path', $parameters) === false || empty($parameters['path']) === true) {
            Message::throwMessage(Trans::getWord('pageNotFound', 'message'), 'ERROR');
        }
        $pagePath = $parameters['path'];
        $pagePath = str_replace('/', '\\', $pagePath);
        # Check custom class
        $pagePath = 'Document\\Pdf\\' . str_replace('/', '\\', $pagePath);
        # Check custom class
        $modelPath = $this->getCustomPath($pagePath);
        if ($this->validateModelClass($modelPath) === false) {
            $modelPath = 'App\\Model\\' . $pagePath;
            if ($this->validateModelClass($modelPath) === false) {
                Message::throwMessage('Not found name_space for document page.', 'ERROR');
            }
        }
        # load Class
        $this->Model = new $modelPath();
        $this->Model->setParameters($parameters);
    }
}
