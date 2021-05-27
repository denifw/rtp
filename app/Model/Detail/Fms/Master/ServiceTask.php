<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Detail\Fms\Master;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Fms\Master\ServiceTaskDao;

/**
 * Class to handle the creation of detail ServiceTask page
 *
 * @package    app
 * @subpackage Model\Detail\Fms\Master
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class ServiceTask extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'serviceTask', 'svt_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $colVal = [
            'svt_ss_id' => $this->User->getSsId(),
            'svt_name' => $this->getStringParameter('svt_name'),
            'svt_active' => $this->getStringParameter('svt_active', 'Y')
        ];
        $svtDao = new ServiceTaskDao();
        $svtDao->doInsertTransaction($colVal);

        return $svtDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'svt_name' => $this->getStringParameter('svt_name'),
            'svt_active' => $this->getStringParameter('svt_active')
        ];
        $svtDao = new ServiceTaskDao();
        $svtDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return ServiceTaskDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('svt_name', 3, 255);
        $this->Validation->checkUnique('svt_name', 'service_task', [
            'svt_id' => $this->getDetailReferenceValue()
        ], [
            'svt_ss_id' => $this->User->getSsId()
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field to field set
        $fieldSet->addField(Trans::getFmsWord('name'), $this->Field->getText('svt_name', $this->getStringParameter('svt_name')), true);
        $fieldSet->addField(Trans::getFmsWord('active'), $this->Field->getYesNo('svt_active', $this->getStringParameter('svt_active')));
        # Create a portlet box.
        $portlet = new Portlet('gnrlPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


}
