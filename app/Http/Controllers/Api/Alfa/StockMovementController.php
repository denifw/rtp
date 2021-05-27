<?php

namespace App\Http\Controllers\Api\Alfa;

use App\Http\Controllers\Controller;
use App\Model\Api\Alfa\StockMovement;

class StockMovementController extends Controller
{
    
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJidByPn()
    {
        $model = new StockMovement('loadJidByPn', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJidByModel()
    {
        $model = new StockMovement('loadJidByModel', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function verifyScanSn()
    {
        $model = new StockMovement('verifyScanSn', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function verifyScanModel()
    {
        $model = new StockMovement('verifyScanModel', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function doDeleteDetail()
    {
        $model = new StockMovement('doDeleteDetail', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function doUpdateDetail()
    {
        $model = new StockMovement('doUpdateDetail', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJidStock()
    {
        $model = new StockMovement('loadJidStock', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJidBySn()
    {
        $model = new StockMovement('loadJidBySn', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadListJid()
    {
        $model = new StockMovement('loadListJid', request()->all());
        return $model->loadResponse();
    }
    
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function doComplete()
    {
        $model = new StockMovement('doComplete', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function doStart()
    {
        $model = new StockMovement('doStart', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJobData()
    {
        $model = new StockMovement('loadJobData', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control goods data
     *
     * @return mixed
     */
    public function loadGoodsData()
    {
        $model = new StockMovement('loadGoodsData', request()->all());
        return $model->loadResponse();
    }
}
