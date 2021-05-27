<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Detail\System\Location;

use App\Frame\Formatter\Trans;
use App\Model\Dao\System\Location\PortDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;

/**
 * Class to handle the creation of detail Port page
 *
 * @package    app
 * @subpackage Model\Detail\System\Location
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Port extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'port', 'po_id');
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
            'po_code' => $this->getStringParameter('po_code'),
            'po_name' => $this->getStringParameter('po_name'),
            'po_cnt_id' => $this->getIntParameter('po_cnt_id'),
            'po_cty_id' => $this->getIntParameter('po_cty_id'),
            'po_tm_id' => $this->getIntParameter('po_tm_id'),
            'po_active' => 'Y',
        ];
        $portDao = new PortDao();
        $portDao->doInsertTransaction($colVal);

        return $portDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'po_code' => $this->getStringParameter('po_code'),
            'po_name' => $this->getStringParameter('po_name'),
            'po_cnt_id' => $this->getIntParameter('po_cnt_id'),
            'po_cty_id' => $this->getIntParameter('po_cty_id'),
            'po_tm_id' => $this->getIntParameter('po_tm_id'),
            'po_active' => $this->getStringParameter('po_active'),
        ];
        $portDao = new PortDao();
        $portDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return PortDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('po_name');
        $this->Validation->checkRequire('po_code');
        $this->Validation->checkRequire('po_cnt_id');
        $this->Validation->checkRequire('po_tm_id');
        $this->Validation->checkUnique('po_code', 'port', [
            'po_id' => $this->getDetailReferenceValue()
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
        $countryField = $this->Field->getSingleSelect('country', 'po_country', $this->getStringParameter('po_country'));
        $countryField->setHiddenField('po_cnt_id', $this->getIntParameter('po_cnt_id'));
        $countryField->setEnableDetailButton(false);
        $countryField->setEnableNewButton(false);
        # City Field
        $cityField = $this->Field->getSingleSelect('city', 'po_city', $this->getStringParameter('po_city'));
        $cityField->setHiddenField('po_cty_id', $this->getIntParameter('po_cty_id'));
        $cityField->addParameterById('cty_cnt_id', 'po_cnt_id', Trans::getWord('country'));
        $cityField->setEnableDetailButton(false);
        $cityField->setEnableNewButton(false);
        # Module field
        $tmField = $this->Field->getSingleSelect('transportModule', 'po_module', $this->getStringParameter('po_module'));
        $tmField->setHiddenField('po_tm_id', $this->getIntParameter('po_tm_id'));
        $tmField->setEnableNewButton(false);
        $tmField->setEnableDetailButton(false);
        # Add field into fieldset
        $fieldSet->addField(Trans::getWord('portName'), $this->Field->getText('po_name', $this->getStringParameter('po_name')), true);
        $fieldSet->addField(Trans::getWord('portCode'), $this->Field->getText('po_code', $this->getStringParameter('po_code')), true);
        $fieldSet->addField(Trans::getWord('module'), $tmField, true);
        $fieldSet->addField(Trans::getWord('country'), $countryField, true);
        $fieldSet->addField(Trans::getWord('city'), $cityField);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('po_active', $this->getStringParameter('po_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('PoGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }


}
