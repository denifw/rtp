<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Detail\System\Master;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\Master\CurrencyDao;

/**
 * Class to handle the creation of detail Currency page
 *
 * @package    app
 * @subpackage Model\Detail\System
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Currency extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'cur', 'cur_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $colVal = [
            'cur_cnt_id' => $this->getStringParameter('cur_cnt_id'),
            'cur_name' => $this->getStringParameter('cur_name'),
            'cur_iso' => mb_strtoupper($this->getStringParameter('cur_iso')),
            'cur_active' => $this->getStringParameter('cur_active', 'Y'),
        ];
        $curDao = new CurrencyDao();
        $curDao->doInsertTransaction($colVal);

        return $curDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'cur_cnt_id' => $this->getStringParameter('cur_cnt_id'),
            'cur_name' => $this->getStringParameter('cur_name'),
            'cur_iso' => mb_strtoupper($this->getStringParameter('cur_iso')),
            'cur_active' => $this->getStringParameter('cur_active', 'Y'),
        ];
        $curDao = new CurrencyDao();
        $curDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return CurrencyDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('cur_name', 2, 125);
        $this->Validation->checkRequire('cur_iso', 2, 5);
        $this->Validation->checkRequire('cur_cnt_id');
        $this->Validation->checkUnique('cur_iso', 'currency', [
            'cur_id' => $this->getDetailReferenceValue()
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add field to field set
        $countryField = $this->Field->getSingleSelect('cnt', 'cnt_name', $this->getStringParameter('cnt_name'));
        $countryField->setHiddenField('cur_cnt_id', $this->getStringParameter('cur_cnt_id'));
        $countryField->setEnableNewButton(false);
        $fieldSet->addField(Trans::getWord('currency'), $this->Field->getText('cur_name', $this->getStringParameter('cur_name')), true);
        $fieldSet->addField(Trans::getWord('isoCode'), $this->Field->getText('cur_iso', $this->getStringParameter('cur_iso')), true);
        $fieldSet->addField(Trans::getWord('country'), $countryField, true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('cur_active', $this->getStringParameter('cur_active')));
        # Create a portlet box.
        $portlet = new Portlet('curGnrlPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12);

        return $portlet;
    }


}
