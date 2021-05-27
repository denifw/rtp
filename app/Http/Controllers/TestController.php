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
        $wheres = [];
        $wheres[] = SqlHelper::generateNullCondition('ss_deleted_on');
        $wheres[] = SqlHelper::generateStringCondition('ss_active', 'Y');
        $wheres[] = SqlHelper::generateNumericCondition('ss_id', 2, '<>');
        $data = SystemSettingDao::loadAllData($wheres);
        $snDao = new SerialNumberDao();
        foreach ($data as $row) {
            $snColVal = [
                'sn_ss_id' => $row['ss_id'],
                'sn_sc_id' => 8,
                'sn_relation' => 'N',
                'sn_prefix' => 'BT',
                'sn_separator' => '-',
                'sn_yearly' => 'Y',
                'sn_monthly' => 'Y',
                'sn_length' => 3,
                'sn_increment' => 1,
                'sn_format' => 'A',
                'sn_active' => 'Y',
            ];
            $snDao->doInsertTransaction($snColVal);
        }
        exit;
    }

}
