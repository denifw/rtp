<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\System;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\SystemTableDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail SystemTable page
 *
 * @package    app
 * @subpackage Model\Detail\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemTable extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'systemTable', 'st_id');
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
            'st_name' => $this->getStringParameter('st_name'),
            'st_prefix' => $this->getStringParameter('st_prefix'),
            'st_path' => $this->getStringParameter('st_path'),
            'st_active' => $this->getStringParameter('st_active', 'Y'),
        ];
        $stDao = new SystemTableDao();
        $stDao->doInsertTransaction($colVal);

        return $stDao->getLastInsertId();

    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'st_name' => $this->getStringParameter('st_name'),
            'st_prefix' => $this->getStringParameter('st_prefix'),
            'st_path' => $this->getStringParameter('st_path'),
            'st_active' => $this->getStringParameter('st_active', 'Y'),
        ];
        $stDao = new SystemTableDao();
        $stDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return SystemTableDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('st_name', 3, 255);
        $this->Validation->checkRequire('st_prefix', 2, 255);
        $this->Validation->checkRequire('st_path', 3, 255);
        $this->Validation->checkUnique('st_prefix', 'system_table', [
            'st_id' => $this->getDetailReferenceValue()
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
        $fieldSet->addField(Trans::getWord('tableName'), $this->Field->getText('st_name', $this->getStringParameter('st_name')), true);
        $fieldSet->addField(Trans::getWord('prefix'), $this->Field->getText('st_prefix', $this->getStringParameter('st_prefix')), true);
        $fieldSet->addField(Trans::getWord('path'), $this->Field->getText('st_path', $this->getStringParameter('st_path')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('st_active', $this->getStringParameter('st_active')));

        # Create a portlet box.
        $portlet = new Portlet('StGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }
}
