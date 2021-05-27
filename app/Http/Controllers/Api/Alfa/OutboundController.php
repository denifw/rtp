<?php

namespace App\Http\Controllers\Api\Alfa;

use App\Http\Controllers\Controller;
use App\Model\Api\Alfa\Outbound;

class OutboundController extends Controller
{
    
    
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function insertJodByPacking()
    {
        $model = new Outbound('insertJodByPacking', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function verifyPn()
    {
        $model = new Outbound('verifyPn', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadSuggestionPickPn()
    {
        $model = new Outbound('loadSuggestionPickPn', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadSuggestionPickSn()
    {
        $model = new Outbound('loadSuggestionPickSn', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function verifyScanStorage()
    {
        $model = new Outbound('verifyScanStorage', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function completeLoading()
    {
        $model = new Outbound('completeLoading', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function startLoading()
    {
        $model = new Outbound('startLoading', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function truckArrive()
    {
        $model = new Outbound('truckArrive', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function completePicking()
    {
        $model = new Outbound('completePicking', request()->all()); 
        return $model->loadResponse();
    }

    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function verifySnStorage()
    {
        $model = new Outbound('verifySnStorage', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function verifyStorage()
    {
        $model = new Outbound('verifyStorage', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function insertOutboundDetail()
    {
        $model = new Outbound('insertOutboundDetail', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function deleteOutboundDetail()
    {
        $model = new Outbound('deleteOutboundDetail', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function updateOutboundDetail()
    {
        $model = new Outbound('updateOutboundDetail', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJidStock()
    {
        $model = new Outbound('loadJidStock', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJobDetail()
    {
        $model = new Outbound('loadJobDetail', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function startPicking()
    {
        $model = new Outbound('startPicking', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJobGoods()
    {
        $model = new Outbound('loadJobGoods', request()->all());
        return $model->loadResponse(); 
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJobData()
    {
        $model = new Outbound('loadJobData', request()->all());
        return $model->loadResponse();
    }
}
