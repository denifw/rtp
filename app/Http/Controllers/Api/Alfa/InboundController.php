<?php

namespace App\Http\Controllers\Api\Alfa;

use App\Http\Controllers\Controller;
use App\Model\Api\Alfa\Inbound;

class InboundController extends Controller
{
    
    /**
     * The function lo control updateInboundDetailByPn
     *
     * @return mixed
     */
    public function insertJirByPn()
    {
        $model = new Inbound('insertJirByPn', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control updateInboundDetailByPn
     *
     * @return mixed
     */
    public function verifyInboundReceivePn()
    {
        $model = new Inbound('verifyInboundReceivePn', request()->all());
        return $model->loadResponse();
    }
/**
     * The function lo control updateInboundDetailByPn
     *
     * @return mixed
     */
    public function updateInboundDetailByPn()
    {
        $model = new Inbound('updateInboundDetailByPn', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control verifyInboundReceiveSn
     *
     * @return mixed
     */
    public function verifyInboundReceiveSn()
    {
        $model = new Inbound('verifyInboundReceiveSn', request()->all());
        return $model->loadResponse();
    }


    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function verifySnInbound()
    {
        $model = new Inbound('verifySnInbound', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJirStorage()
    {
        $model = new Inbound('loadJirStorage', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJirForPutAway()
    {
        $model = new Inbound('loadJirForPutAway', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJobInboundReceive()
    {
        $model = new Inbound('loadJobInboundReceive', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function completePutAway()
    {
        $model = new Inbound('completePutAway', request()->all());
        return $model->loadResponse();
    }


    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function verifyStorage()
    {
        $model = new Inbound('verifyStorage', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function deleteInboundDetail()
    {
        $model = new Inbound('deleteInboundDetail', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function updateInboundDetail()
    {
        $model = new Inbound('updateInboundDetail', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function startPutAway()
    {
        $model = new Inbound('startPutAway', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function doEndUnload()
    {
        $model = new Inbound('doEndUnload', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function deleteGoodDamageReceive()
    {
        $model = new Inbound('deleteGoodDamageReceive', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function registerGoodDamageReceive()
    {
        $model = new Inbound('registerGoodDamageReceive', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadGoodsDamageReceived()
    {
        $model = new Inbound('loadGoodsDamageReceived', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function updateInboundReceive()
    {
        $model = new Inbound('updateInboundReceive', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function deleteInboundReceive()
    {
        $model = new Inbound('deleteInboundReceive', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function startUnload()
    {
        $model = new Inbound('startUnload', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function updateTruckArrival()
    {
        $model = new Inbound('updateTruckArrival', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJobData()
    {
        $model = new Inbound('loadJobData', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function lo control job overview
     *
     * @return mixed
     */
    public function loadJobGoodsInbound()
    {
        $model = new Inbound('loadJobGoodsInbound', request()->all());
        return $model->loadResponse();
    }
}
