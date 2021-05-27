<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Detail\Master;

use App\Frame\Formatter\Trans;
use App\Model\Dao\Master\UnitDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;

/**
 * Class to handle the creation of detail UnitOfMeasure page
 *
 * @package    app
 * @subpackage Model\Detail\Master
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Unit extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'unit', 'uom_id');
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
            'uom_code' => $this->getStringParameter('uom_code'),
            'uom_name' => $this->getStringParameter('uom_name'),
            'uom_active' => $this->getStringParameter('uom_active', 'Y'),
        ];
        $unitDao = new UnitDao();
        $unitDao->doInsertTransaction($colVal);

        return $unitDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'uom_code' => $this->getStringParameter('uom_code'),
            'uom_name' => $this->getStringParameter('uom_name'),
            'uom_active' => $this->getStringParameter('uom_active', 'Y'),
        ];
        $unitDao = new UnitDao();
        $unitDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);

    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return UnitDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('uom_name', 2, 125);
        $this->Validation->checkRequire('uom_code', 1, 50);
        $this->Validation->checkUnique('uom_code', 'unit', [
            'uom_id' => $this->getDetailReferenceValue()
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
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('uom_name', $this->getStringParameter('uom_name')), true);
        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('uom_code', $this->getStringParameter('uom_code')), true);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('uom_active', $this->getStringParameter('uom_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('UomGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12);

        return $portlet;
    }


}
