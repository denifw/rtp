<?php

namespace App\Http\Controllers\Api\Alfa;

use App\Http\Controllers\Controller;
use App\Model\Api\Alfa\StockOpname;

class StockOpnameController extends Controller
{
    
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function doDeleteOpnameDetail()
    {
        $model = new StockOpname('doDeleteOpnameDetail', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function verifyScanModel()
    {
        $model = new StockOpname('verifyScanModel', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function verifyScanSn()
    {
        $model = new StockOpname('verifyScanSn', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function verifyScanStorage()
    {
        $model = new StockOpname('verifyScanStorage', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function doUpdateOpnameDetail()
    {
        $model = new StockOpname('doUpdateOpnameDetail', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadStockOpnameDetail()
    {
        $model = new StockOpname('loadStockOpnameDetail', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function doEnd()
    {
        $model = new StockOpname('doEnd', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function doStart()
    {
        $model = new StockOpname('doStart', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJobData()
    {
        $model = new StockOpname('loadJobData', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control goods data
     *
     * @return mixed
     */
    public function loadGoodsData()
    {
        $model = new StockOpname('loadGoodsData', request()->all());
        return $model->loadResponse();
    }


    // /**
    //  * The function lo control overview storage
    //  *
    //  * @return mixed
    //  */
    // public function loadJobOverviewByTime()
    // {
    //     $model = new JobOverview('loadJobOverviewByTime', request()->all());
    //     return $model->loadResponse();
    // }

    // /**
    //  * The function lo control job overview
    //  *
    //  * @return mixed
    //  */
    // public function loadJobOverview()
    // {
    //     $model = new JobOverview('loadJobOverview', request()->all());
    //     return $model->loadResponse();
    // }


    // /**
    //  * The function lo control overview storage
    //  *
    //  * @return mixed
    //  */
    // public function loadStorageOverview()
    // {
    //     $model = new StorageOverview('loadStorageOverview', request()->all());
    //     return $model->loadResponse();
    // }

    // /**
    //  * The function to conrtol goods storage
    //  *
    //  * @return mixed
    //  */
    // public function loadGoodsStorageOverview()
    // {
    //     $model = new StorageOverview('loadGoodsStorageOverview', request()->all());
    //     return $model->loadResponse();
    // }

    // /**
    //  * The function to process the login.
    //  *
    //  * @return mixed
    //  */
    // public function loadStockCard()
    // {
    //     $model = new StockCard('loadStockCard', request()->all());
    //     return $model->loadResponse();
    // }

    // /**
    //  * The function to process the login.
    //  *
    //  * @return mixed
    //  */
    // public function loadStorageStockCard()
    // {
    //     $model = new StockCard('loadGoodsStorage', request()->all());
    //     return $model->loadResponse();
    // }

}
