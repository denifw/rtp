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

use Illuminate\Http\Request;

/**
 *
 *
 * @package    app
 * @subpackage Http\Controllers
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class SeederController
{
    /**
     * Property to store the data that will be pass to the view.
     *
     * @var \App\Frame\Mvc\AbstractBaseDao $Model
     */
    protected $Model;

    /**
     * Show the application dashboard.
     *
     * @param \Illuminate\Http\Request $request To store the request parameter.
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $pagePath = $request->get('page');
        if (empty($pagePath) === false) {
            $pagePath = '\\App\\Model\\Dao\\' . str_replace('/', '\\', $pagePath);
            if (class_exists($pagePath) === true) {
                $this->Model = new $pagePath();
                $queries = $this->Model->loadSeeder();
                foreach ($queries AS $text) {
                    echo $text . '<br />';
                }
            } else {
                echo 'Page not found for seeder.';
            }
        } else {
            echo 'Invalid Seeder Parameter.';
        }
        exit;
    }

}
