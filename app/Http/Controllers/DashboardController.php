<?php
/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 10/04/2019
 * Time: 14:03
 */

namespace App\Http\Controllers;

use App\Frame\Formatter\Trans;
use App\Frame\System\Session\UserSession;
use App\Frame\System\SystemSettings;
use App\Model\Dao\Setting\DashboardDao;
use App\Model\Dashboard\Home;

class DashboardController extends AbstractBaseController
{

    /**
     * The function to load the default dashboard for every user.
     *
     * @return mixed
     */
    public function index()
    {
        $pages = SystemSettings::loadSettings('pages');
        $page = null;
        if ($pages !== null) {
            foreach ($pages AS $p) {
                if ($page === null && $p['pc_name'] === 'Dashboard') {
                    $page = $p;
                }
            }
            if ($page !== null) {
                $user = new UserSession();
                $wheres[] = '(dsh.dsh_ss_id = ' . $user->getSsId() . ')';
                $wheres[] = '(dsh.dsh_us_id = ' . $user->getId() . ')';
                $wheres[] = '(dsh.dsh_deleted_on IS NULL)';
                $orderList[] = 'dsh.dsh_order ASC';
                $dashboard = DashboardDao::loadData($wheres, $orderList, 1);
                if (empty($dashboard) === false) {
                    return redirect($page['pg_route'] . '?dsh_id=' . $dashboard[0]['dsh_id']);
                }

                return redirect($page['pg_route']);
            }
        }

        return view('errors.general', ['error_message' => Trans::getWord('doNotHavePermission', 'message'), 'back_url' => '']);
    }

    /**
     * The function to load the detail of menu.
     *
     * @return mixed
     */
    public function home()
    {

        $model = new Home(request()->all());

        return $this->doControlDashboard($model);
    }
}
