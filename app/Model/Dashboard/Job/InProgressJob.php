<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Dashboard\Job;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractDashboardModel;
use App\Model\DashboardItem\Table\AutoReloadProgressJob;

/**
 * Class to create the view for PlanningJob dashboard.
 *
 * @package    app
 * @subpackage Model\Dashboard
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class InProgressJob extends AbstractDashboardModel
{


    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'inProgressJob');
        $this->setParameters($parameters);
    }


    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadView(): void
    {
        # Arrive soon
        $arriveJob = new AutoReloadProgressJob('ProgressJobTbl');
        $arriveJob->setTitlePortlet(Trans::getWord('inProgressJob' ));
        $arriveJob->addCallBackParameter('jo_ss_id', $this->User->getSsId());
        if ($this->PageSetting->checkPageRight('AllowSeeAllJob') === false) {
            $arriveJob->addCallBackParameter('us_id', $this->User->getId());
            $arriveJob->addCallBackParameter('us_rel_id', $this->User->getRelId());
        }
        $this->addContent($arriveJob->doCreate());
    }

}
