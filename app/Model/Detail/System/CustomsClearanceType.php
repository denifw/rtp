<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2020 spada-informatika.com
 */

namespace App\Model\Detail\System;

use App\Frame\Formatter\Trans;
use App\Model\Dao\System\CustomsClearanceTypeDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;

/**
 * Class to handle the creation of detail CustomsClearanceType page
 *
 * @package    app
 * @subpackage Model\Detail\System
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2020 spada-informatika.com
 */
class CustomsClearanceType extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'customsClearanceType', 'cct_id');
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
            'cct_code' => $this->getStringParameter('cct_code'),
            'cct_name' => $this->getStringParameter('cct_name'),
            'cct_active' => $this->getStringParameter('cct_active', 'Y'),
        ];
        $cctDao = new CustomsClearanceTypeDao();
        $cctDao->doInsertTransaction($colVal);

        return $cctDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'cct_code' => $this->getStringParameter('cct_code'),
            'cct_name' => $this->getStringParameter('cct_name'),
            'cct_active' => $this->getStringParameter('cct_active', 'Y'),
        ];
        $cctDao = new CustomsClearanceTypeDao();

        $cctDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return CustomsClearanceTypeDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('cct_code', 2, 128);
        $this->Validation->checkSpecialCharacter('cct_code');
        $this->Validation->checkUnique('cct_code', 'customs_clearance_type', [
            'cct_id' => $this->getDetailReferenceValue(),
        ]);
        $this->Validation->checkRequire('cct_name', 3, 125);
        $this->Validation->checkUnique('cct_name', 'customs_clearance_type', [
            'cct_id' => $this->getDetailReferenceValue(),
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
        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('cct_code', $this->getStringParameter('cct_code')), true);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('cct_name', $this->getStringParameter('cct_name')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('cct_active', $this->getStringParameter('cct_active')));
        # Create a portlet box.
        $portlet = new Portlet('GnrlPtl', Trans::getWord('general'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


}
