<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Http\Controllers;


use App\Frame\Document\FileDownload;
use App\Frame\Exceptions\Message;
use Illuminate\Http\Request;

/**
 * Class to control the access for download document.
 *
 * @package    app
 * @subpackage Http\Controller
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class DownloadController extends Controller
{

    /**
     * Function to control listing screen
     *
     * @param \Illuminate\Http\Request $request To store all the request parameter.
     *
     * @return mixed
     */
    public function doControl(Request $request)
    {
        try {
            if (empty($request->get('doc_id')) === true) {
                Message::throwMessage('Invalid parameter for these page.', 'ERROR');
            }
            $download = new FileDownload($request->get('doc_id'));
            $download->loadFile();

            return response()->download($download->getPath(), $download->getFileName(), $download->getHeaders(), 'inline');
        } catch (\Exception $e) {
            return view('errors.general', ['error_message' => $e->getMessage(), 'back_url' => null]);
        }
    }
}
