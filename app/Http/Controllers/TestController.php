<?php

namespace App\Http\Controllers;


use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\System\Page\MenuDao;
use App\Model\Dao\System\Page\PageDao;
use Ramsey\Uuid\Uuid;

class TestController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return mixed
     */
    public function test()
    {
        echo microtime();
//        var_dump(time());
        exit;
    }

}
