<?php

namespace App\Http\Controllers\Api\Alfa;

use App\Http\Controllers\Controller;
use App\Model\Api\Alfa\Master;

class MasterController extends Controller
{

    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function loadGoodUnit()
    {
        $model = new Master('loadGoodUnit', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function loadWarehouseStorage()
    {
        $model = new Master('loadWarehouseStorage', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function loadGoodDamageType()
    {
        $model = new Master('loadGoodDamageType', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function loadGoodCauseDamage()
    {
        $model = new Master('loadGoodCauseDamage', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function loadRelationGoods()
    {
        $model = new Master('loadRelationGoods', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function loadGoods()
    {
        $model = new Master('loadGoods', request()->all());
        return $model->loadResponse();
    }

    
    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function loadWarehouse()
    {
        $model = new Master('loadWarehouse', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function loadRelation()
    {
        $model = new Master('loadRelation', request()->all());
        return $model->loadResponse();
    }

    /**
     * The function to process the login.
     *
     * @return mixed
     */
    public function loadActionEvents()
    {
        $model = new Master('loadActionEvents', request()->all());
        return $model->loadResponse();
    }

}
