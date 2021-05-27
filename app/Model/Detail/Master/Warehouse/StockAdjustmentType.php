<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Master\Warehouse;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Master\Warehouse\StockAdjustmentTypeDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail StockAdjustmentType page
 *
 * @package    app
 * @subpackage Model\Detail\Master\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class StockAdjustmentType extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'stockAdjustmentType', 'sat_id');
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
            'sat_ss_id' => $this->User->getSsId(),
            'sat_code' => $this->getStringParameter('sat_code'),
            'sat_description' => $this->getStringParameter('sat_description'),
            'sat_active' => $this->getStringParameter('sat_active', 'Y'),
        ];
        $satDao = new StockAdjustmentTypeDao();
        $satDao->doInsertTransaction($colVal);

        return $satDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'sat_code' => $this->getStringParameter('sat_code'),
            'sat_description' => $this->getStringParameter('sat_description'),
            'sat_active' => $this->getStringParameter('sat_active', 'Y'),
        ];
        $satDao = new StockAdjustmentTypeDao();
        $satDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return StockAdjustmentTypeDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('sat_code', 3, 125);
        $this->Validation->checkRequire('sat_description', 3, 255);
        $this->Validation->checkUnique('sat_code', 'stock_adjustment_type', [
            'sat_id' => $this->getDetailReferenceValue()
        ], [
            'sat_ss_id' => $this->User->getSsId()
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create Fields.

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('sat_code', $this->getStringParameter('sat_code')), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('sat_description', $this->getStringParameter('sat_description')), true);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('sat_active', $this->getStringParameter('sat_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('SatGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }
}
