<?php

namespace App\Http\Controllers;


use App\Frame\Bin\Code\Routes\CheckPageRoute;
use App\Frame\Bin\Code\UnregisteredTable;
use App\Frame\Bin\SqlQuery\Uuid\AddingUuid;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\SqlHelper;
use App\Model\Dao\Setting\SerialNumberDao;
use App\Model\Dao\System\Master\IncoTermsDao;
use App\Model\Dao\System\Service\ActionDao;
use App\Model\Dao\System\Service\ServiceTermDao;
use App\Model\Dao\System\SystemSettingDao;
use App\Model\Dao\System\SystemTableDao;
use App\Model\Dao\User\UsersDao;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Route;

class TestController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return mixed
     */
    public function test()
    {
        session()->flush();
        session()->regenerate();
        exit;
        $usDao = new UsersDao();
        echo implode('<br>', $usDao->loadSeeder());
        exit;
    }

}
