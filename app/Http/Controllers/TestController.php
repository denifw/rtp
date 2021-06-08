<?php

namespace App\Http\Controllers;


use App\Model\Dao\System\Page\MenuDao;
use App\Model\Dao\System\Page\PageDao;

class TestController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return mixed
     */
    public function test()
    {
        $data = PageDao::loadData();
        $temp = [];
        foreach ($data as $row) {
            if (in_array($row['pg_route'], $temp, true) === false) {
                echo "'" . $row['pg_route'] . "' => [<br/>'title' => '" . $row['pg_title'] . "', <br/>'description' => '" . $row['pg_description'] . "'<br/>],";
                echo "<br/>";
                $temp[] = $row['pg_route'];
            }
        }
        exit;
    }

}
