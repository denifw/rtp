<?php

namespace App\Http\Controllers\Api\Alfa;

use App\Http\Controllers\Controller;
use App\Model\Api\Alfa\JobOrder;
use App\Model\Api\Alfa\StockCard;
use App\Model\Api\Alfa\StorageOverview;

class HomeController extends Controller
{
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadListJobOverview()
    {
        $model = new JobOrder('loadListJobOverview', request()->all());
        return $model->loadResponse();
    }


    /**
     * The function lo control overview storage
     *
     * @return mixed
     */
    public function loadJobOverviewByTime()
    {
        $model = new JobOrder('loadJobOverviewByTime', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJobOverview()
    {
        $model = new JobOrder('loadJobOverview', request()->all());
        return $model->loadResponse();
    }


    /**
     * The function lo control overview storage
     *
     * @return mixed
     */
    public function loadStorageOverview()
    {
        $model = new StorageOverview('loadStorageOverview', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function to conrtol goods storage
     *
     * @return mixed
     */
    public function loadGoodsStorageOverview()
    {
        $model = new StorageOverview('loadGoodsStorageOverview', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function loadStockCard()
    { 
        $model = new StockCard('loadStockCard', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function loadStorageStockCard()
    {
        $model = new StockCard('loadGoodsStorage', request()->all());
        return $model->loadResponse();
    }

}
