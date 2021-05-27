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
use App\Model\Dao\Fms\Master\RenewalTypeDao;

/**
 * Class to handle the creation of detail RenewalType page
 *
 * @package    app
 * @subpackage Model\Detail\Fms\Master
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class RenewalType extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'renewalType', 'rnt_id');
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
            'rnt_ss_id' => $this->User->getSsId(),
            'rnt_name' => $this->getStringParameter('rnt_name'),
            'rnt_active' => $this->getStringParameter('rnt_active', 'Y'),
        ];
        $rntDao = new RenewalTypeDao();
        $rntDao->doInsertTransaction($colVal);

        return $rntDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'rnt_name' => $this->getStringParameter('rnt_name'),
            'rnt_active' => $this->getStringParameter('rnt_active', 'Y'),
        ];
        $rntDao = new RenewalTypeDao();
        $rntDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return RenewalTypeDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
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
        $this->Validation->checkRequire('rnt_name', 3, 255);
        $this->Validation->checkUnique('rnt_name', 'renewal_type', [
            'rnt_id' => $this->getDetailReferenceValue()
        ], [
            'rnt_ss_id' => $this->User->getSsId()
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
        $fieldSet->addField(Trans::getFmsWord('name'), $this->Field->getText('rnt_name', $this->getStringParameter('rnt_name')), true);
        $fieldSet->addField(Trans::getFmsWord('active'), $this->Field->getYesNo('rntt_active', $this->getStringParameter('rnt_active')));
        # Create a portlet box.
        $portlet = new Portlet('gnrlPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


}
