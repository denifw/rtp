<?php

namespace App\Http\Controllers\Api\Alfa;

use App\Http\Controllers\Controller;
use App\Model\Api\Alfa\JobOrder;

class JobController extends Controller
{
    
    /**
     * The function to control job data
     *
     * @return mixed
     */
    public function loadJobGoods()
    {
        $model = new JobOrder('loadJobGoods', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function to control job data
     *
     * @return mixed
     */
    public function progressJobOverview()
    {
        $model = new JobOrder('progressJobOverview', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function to control job data
     *
     * @return mixed
     */
    public function planningJobOverview()
    {
        $model = new JobOrder('planningJobOverview', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function to control job data
     *
     * @return mixed
     */
    public function doUploadEventImage()
    {
        $model = new JobOrder('doUploadEventImage', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function to control job data
     *
     * @return mixed
     */
    public function insertJobEvent()
    {
        $model = new JobOrder('insertJobEvent', request()->all());
        return $model->loadResponse();
    }
    /**
     * The function to control job data
     *
     * @return mixed
     */
    public function loadJobWorkSheet()
    {
        $model = new JobOrder('loadJobWorkSheet', request()->all());
        return $model->loadResponse();
    }


    /**
     * The function to control job data
     *
     * @return mixed
     */
    public function loadMyJobs()
    {
        $model = new JobOrder('loadMyJobs', request()->all());
        return $model->loadResponse(); 
    }
}
