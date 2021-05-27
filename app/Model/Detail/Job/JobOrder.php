<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Job;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Templates\ServiceMenu;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\Service\ServiceDao;
use App\Model\Dao\System\Service\SystemServiceDao;

/**
 * Class to handle the creation of detail JobOrder page
 *
 * @package    app
 * @subpackage Model\Detail\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobOrder extends AbstractFormModel
{
    /**
     * Property to store list of service
     *
     * @var array $Service
     */
    private $Service = [];

    /**
     * Property to store list of service term
     *
     * @var array $Service
     */
    private $ServiceTerm = [];

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'jobOrder', 'jo_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        return 0;
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
    }


    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return [];
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->loadService();
        if (empty($this->Service) === true) {
            Message::throwMessage(Trans::getWord('pageNotFound', 'message'), 'ERROR');
        }
        $this->View->addContent('srv_menu', $this->getServiceContainer());
        $this->View->addContent('srv_sub_menu', $this->getServiceTermContainer());
        $this->View->addContentAttribute('srv_menu', 'style', 'text-align: center;');
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        $btnClose = new HyperLink('hplClose', Trans::getWord('cancel'), url('/'));
        $btnClose->viewAsButton();
        $btnClose->setIcon(Icon::MailReply)->btnDanger()->pullRight()->btnMedium();
        $this->View->addButton($btnClose);
    }

    /**
     * Function to get the general Field Set.
     *
     * @return void
     */
    private function loadService(): void
    {
        $wheres = [];
        $wheres[] = '(ssr.ssr_ss_id = ' . $this->User->getSsId() . ')';
        $wheres[] = '(ssr.ssr_deleted_on IS NULL)';
        $wheres[] = "(ssr.ssr_active = 'Y')";
        $wheres[] = '(srv.srv_deleted_on IS NULL)';
        $wheres[] = "(srv.srv_active = 'Y')";
        $wheres[] = '(srt.srt_deleted_on IS NULL)';
        $wheres[] = "(srt.srt_active = 'Y')";
        $results = SystemServiceDao::loadData($wheres);
        $srvIds = [];
        foreach ($results as $row) {
            if (in_array($row['ssr_srv_id'], $srvIds, true) === false) {
                $srvIds [] = $row['ssr_srv_id'];
                $this->Service[] = $row;
                $this->ServiceTerm[$row['ssr_srv_id']][] = $row;
            } else {
                $this->ServiceTerm[$row['ssr_srv_id']][] = $row;
            }
        }
        # Add stock transfer
        $srv = ServiceDao::getIdByCode('warehouse');
        $data = [
            'srv_name' => Trans::getWord('warehouse'),
            'srt_route' => 'joWhStockTransfer',
            'srt_image' => 'wh_stock_transfer.png',
            'srt_color' => 'tile-warehouse',
        ];
        $this->ServiceTerm[$srv][] = $data;
    }

    /**
     * Function to create service container.
     *
     * @return string
     */
    private function getServiceContainer(): string
    {
        # Add service widget
        $result = '<div id="service_menu">';
        foreach ($this->Service as $row) {
            $result .= $this->addWidgetService($row);
        }
        $result .= '</div>';

        return $result;
    }

    /**
     * Function to create service term container.
     *
     * @return string
     */
    private function getServiceTermContainer(): string
    {
        $result = '<div id="service_sub_menu">';
        $iconBack = asset('images/menus/back.png');
        $result .= '<div id="service-panel"><div id="service-home"><img style="width:30px" src="' . $iconBack . '" alt="back"/>
                   </div><div id="service-title"></div><div class="clear"></div></div>';
        foreach ($this->Service as $row) {
            $result .= '<div id="service_' . $row['srv_code'] . '" class="row">';
            # Add service term widget
            foreach ($this->ServiceTerm[$row['ssr_srv_id']] as $serviceTerm) {
                $result .= $this->addWidgetServiceTerm($serviceTerm);
            }
            $result .= '</div>';
        }
        $result .= '</div>' . $this->getJavascript();

        return $result;
    }


    /**
     * Function to get the general Field Set.
     *
     * @param array $service To store the service data.
     *
     * @return string
     */
    private function addWidgetService(array $service): string
    {
        $path = asset('images/image-not-found.jpg');
        if (empty($service['srt_image']) === false) {
            $path = asset('images/menus/' . strtolower($service['srv_name']) . '.png');
        }
        $data = [
            'image' => $path,
            'tile_style' => $service['srt_color'],
        ];
        $card = new ServiceMenu($service['srv_code']);
        $card->setData($data);
        $card->setGridDimension(3, 3, 3, 6);

        return $card->createView();
    }

    /**
     * Function to get the general Field Set.
     *
     * @param array $service To store the service data.
     *
     * @return string
     */
    private function addWidgetServiceTerm(array $service): string
    {
        $path = asset('images/image-not-found.jpg');
        if (empty($service['srt_image']) === false) {
            $path = asset('images/menus/' . $service['srt_image']);
        }
        $route = $service['srt_route'] . '/detail';
        $params = [];
        if (empty($service['ssr_srv_id']) === false) {
            $params[] = 'jo_srv_id=' . $service['ssr_srv_id'];
        }
        if (empty($service['ssr_srt_id']) === false) {
            $params[] .= 'jo_srt_id=' . $service['ssr_srt_id'];
        }
        if (empty($params) === false) {
            $route .= '?' . implode('&', $params);
        }
        $data = [
            'title' => $service['srv_name'],
            'label' => '',
            'route' => $route,
            'image' => $path,
            'tile_style' => $service['srt_color'],
        ];
        $card = new ServiceMenu('srt_' . $service['srt_route']);
        $card->setData($data);
        $card->setGridDimension(3, 3, 3, 6);

        return $card->createView();
    }

    /**
     * Returns a string if the javascript must be loaded.
     *
     * @return string
     */
    private function getJavascript(): string
    {
        $varJs = 'ServiceMenu';
        $javascript = '<script type="text/javascript">';
        $javascript .= 'var ' . $varJs . " = new Job.JobOrder('service_menu', 'service_sub_menu');";
        foreach ($this->Service as $key => $row) {
            $javascript .= $varJs . ".AddServiceId('" . $key . "', '" . $row['srv_code'] . "');";
        }
        $javascript .= $varJs . '.AddServiceEvent();';
        $javascript .= '</script>';

        return $javascript;
    }
}
