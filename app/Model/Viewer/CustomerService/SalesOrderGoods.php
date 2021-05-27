<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Viewer\CustomerService;

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\Html\Buttons\Button;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelYesNo;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractViewerModel;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Model\Dao\CustomerService\SalesOrderDao;
use App\Model\Dao\CustomerService\SalesOrderGoodsDao;
use App\Model\Dao\CustomerService\SalesOrderQuotationDao;
use App\Model\Dao\CustomerService\SalesOrderServiceDao;
use App\Model\Dao\Finance\Purchase\JobDepositDao;
use App\Model\Dao\Job\JobActionEventDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\JobPurchaseDao;
use App\Model\Dao\Job\JobSalesDao;
use App\Model\Dao\Setting\Action\SystemActionEventDao;
use App\Model\Dao\System\Document\DocumentDao;

/**
 * Class to handle the creation of detail BaseJobOrder page
 *
 * @package    app
 * @subpackage Model\Viewer\Job
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SalesOrderGoods extends AbstractViewerModel
{

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'sog', 'sog_id');
        $this->setParameters($parameters);
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
        return SalesOrderGoodsDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getSalesOrderPortlet());
        $this->Tab->addPortlet('general', $this->getServicePortlet());
        $this->Tab->addPortlet('general', $this->getReferencePortlet());
        $this->Tab->addPortlet('jobOrder', $this->getJobPortlet());
        $this->overridePageDescription();

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
     * Function to get the Sales Order Portlet.
     *
     * @return Portlet
     */
    private function getSalesOrderPortlet(): Portlet
    {
        $data = [
            [
                'label' => Trans::getWord('soNumber'),
                'value' => $this->getStringParameter('so_number'),
            ],
            [
                'label' => Trans::getWord('customer'),
                'value' => $this->getStringParameter('so_customer'),
            ],
            [
                'label' => Trans::getWord('consolidate'),
                'value' => new LabelYesNo($this->getStringParameter('so_consolidate')),
            ],
            [
                'label' => Trans::getWord('container'),
                'value' => new LabelYesNo($this->getStringParameter('so_container')),
            ],
            [
                'label' => Trans::getWord('incoTerms'),
                'value' => $this->getStringParameter('so_inco_terms'),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('JoGeneralPtl', Trans::getWord('customer'));
        $portlet->addText($content);
        $portlet->setGridDimension(4);

        return $portlet;
    }


    /**
     * Function to get the Sales Order Portlet.
     *
     * @return Portlet
     */
    private function getServicePortlet(): Portlet
    {
        $data = [
            [
                'label' => Trans::getWord('inklaring'),
                'value' => new LabelYesNo($this->getStringParameter('so_inklaring')),
            ],
            [
                'label' => Trans::getWord('delivery'),
                'value' => new LabelYesNo($this->getStringParameter('so_delivery')),
            ],
            [
                'label' => Trans::getWord('multiPickUp'),
                'value' => new LabelYesNo($this->getStringParameter('so_multi_load')),
            ],
            [
                'label' => Trans::getWord('multiDrop'),
                'value' => new LabelYesNo($this->getStringParameter('so_multi_unload')),
            ],
            [
                'label' => Trans::getWord('warehouse'),
                'value' => new LabelYesNo($this->getStringParameter('so_warehouse')),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('SogSrvPtl', Trans::getWord('service'));
        $portlet->addText($content);
        $portlet->setGridDimension(4);

        return $portlet;
    }


    /**
     * Function to get the Reference Portlet.
     *
     * @return Portlet
     */
    private function getReferencePortlet(): Portlet
    {
        $data = [
            [
                'label' => Trans::getWord('customerRef'),
                'value' => $this->getStringParameter('so_customer_ref'),
            ],
            [
                'label' => Trans::getWord('blRef'),
                'value' => $this->getStringParameter('so_bl_ref'),
            ],
            [
                'label' => Trans::getWord('ajuRef'),
                'value' => $this->getStringParameter('so_aju_ref'),
            ],
            [
                'label' => Trans::getWord('sppbRef'),
                'value' => $this->getStringParameter('so_sppb_ref'),
            ],
            [
                'label' => Trans::getWord('packingListRef'),
                'value' => $this->getStringParameter('so_packing_ref'),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('JoGReferencePtl', Trans::getWord('reference'));
        $portlet->addText($content);
        $portlet->setGridDimension(4);

        return $portlet;
    }

    /**
     * Function to get the job Portlet.
     *
     * @return Portlet
     */
    private function getJobPortlet(): Portlet
    {
        # Create a portlet box.
        $portlet = new Portlet('SogJoPtl', Trans::getWord('jobOrder'));
        $portlet->addText('');
        if ($this->isInklaring() === true) {
            $btnInk = new HyperLink('SogInkBtn', Trans::getWord('inklaring'), url('/jik/detail?jik_so_id=' . $this->getIntParameter('sog_so_id')));
            $btnInk->viewAsButton()->setIcon(Icon::Plus)->pullRight()->btnSuccess();
            $portlet->addButton($btnInk);
        }
        return $portlet;
    }

    /**
     * Function to override page description.
     *
     * @return void
     */
    private function overridePageDescription(): void
    {
        $status = new LabelDanger(Trans::getWord('published'));
        if ($this->isValidParameter('jo_id') === true) {
            $joDao = new JobOrderDao();
            $status = $joDao->generateStatus([
                'is_deleted' => $this->isValidParameter('jo_deleted_on'),
                'is_hold' => $this->isValidParameter('joh_id'),
                'is_finish' => $this->isValidParameter('jo_finish_on'),
                'is_document' => $this->isValidParameter('jo_document_on'),
                'is_start' => $this->isValidParameter('jo_start_on'),
                'is_publish' => $this->isValidParameter('jo_publish_on'),
                'jac_id' => $this->getStringParameter('jo_action_id'),
                'jae_style' => $this->getStringParameter('jo_action_style'),
                'jac_action' => $this->getStringParameter('jo_action'),
                'jo_srt_id' => $this->getStringParameter('jo_srt_id'),
            ]);
        }
        $this->View->setDescription('#' . $this->getStringParameter('sog_number') . ' - ' . $status);
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        $btnSo = new HyperLink('SogSoBtn', $this->getStringParameter('so_number'), url('/so/detail?so_id=' . $this->getIntParameter('sog_so_id')));
        $btnSo->viewAsButton()->setIcon(Icon::Eye)->pullRight()->btnSuccess();
        $this->View->addButton($btnSo);
        parent::loadDefaultButton();
    }


    /**
     * Function to set hidden for job delivery detail fields.
     *
     * @return void
     */
    private function setHiddenField(): void
    {
        $content = '';
        $content .= $this->Field->getHidden('sog_sog_id', $this->getIntParameter('sog_sog_id'));
        $content .= $this->Field->getHidden('sog_so_id', $this->getIntParameter('sog_so_id'));
        $this->View->addContent('SogHdd', $content);

    }

    /**
     * Function to check is this inklaring active
     *
     * @return bool
     */
    private function isInklaring(): bool
    {
        return $this->getStringParameter('so_inklaring', 'N') === 'Y';
    }

}
