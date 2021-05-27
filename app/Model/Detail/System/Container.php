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
use App\Model\Dao\System\ContainerDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail Container page
 *
 * @package    app
 * @subpackage Model\Detail\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Container extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'container', 'ct_id');
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
            'ct_code' => $this->getStringParameter('ct_code'),
            'ct_name' => $this->getStringParameter('ct_name'),
            'ct_length' => $this->getFloatParameter('ct_length'),
            'ct_height' => $this->getFloatParameter('ct_height'),
            'ct_width' => $this->getFloatParameter('ct_width'),
            'ct_volume' => $this->getFloatParameter('ct_volume'),
            'ct_max_weight' => $this->getFloatParameter('ct_max_weight'),
            'ct_active' => 'Y',
        ];
        $ctDao = new ContainerDao();
        $ctDao->doInsertTransaction($colVal);

        return $ctDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'ct_code' => $this->getStringParameter('ct_code'),
            'ct_name' => $this->getStringParameter('ct_name'),
            'ct_length' => $this->getFloatParameter('ct_length'),
            'ct_height' => $this->getFloatParameter('ct_height'),
            'ct_width' => $this->getFloatParameter('ct_width'),
            'ct_volume' => $this->getFloatParameter('ct_volume'),
            'ct_max_weight' => $this->getFloatParameter('ct_max_weight'),
            'ct_active' => $this->getStringParameter('ct_active'),
        ];
        $ctDao = new ContainerDao();
        $ctDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return ContainerDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('ct_code', 2, 128);
        $this->Validation->checkSpecialCharacter('ct_code');
        $this->Validation->checkUnique('ct_code', 'container', [
            'ct_id' => $this->getDetailReferenceValue(),
        ]);
        $this->Validation->checkRequire('ct_name', 2, 125);
        $this->Validation->checkUnique('ct_name', 'container', [
            'ct_id' => $this->getDetailReferenceValue(),
        ]);
        $this->Validation->checkRequire('ct_length');
        $this->Validation->checkRequire('ct_height');
        $this->Validation->checkRequire('ct_width');
        $this->Validation->checkRequire('ct_volume');
        $this->Validation->checkRequire('ct_max_weight');
        $this->Validation->checkFloat('ct_length');
        $this->Validation->checkFloat('ct_height');
        $this->Validation->checkFloat('ct_width');
        $this->Validation->checkFloat('ct_volume');
        $this->Validation->checkFloat('ct_max_weight');
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create Fields.

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('ct_code', $this->getStringParameter('ct_code')), true);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('ct_name', $this->getStringParameter('ct_name')), true);
        $fieldSet->addField(Trans::getWord('length') . ' / m', $this->Field->getNumber('ct_length', $this->getFloatParameter('ct_length')), true);
        $fieldSet->addField(Trans::getWord('width') . ' / m', $this->Field->getNumber('ct_width', $this->getFloatParameter('ct_width')), true);
        $fieldSet->addField(Trans::getWord('height') . ' / m', $this->Field->getNumber('ct_height', $this->getFloatParameter('ct_height')), true);
        $fieldSet->addField(Trans::getWord('volume') . ' / CBM', $this->Field->getNumber('ct_volume', $this->getFloatParameter('ct_volume')), true);
        $fieldSet->addField(Trans::getWord('maxWeight') . ' / Kg', $this->Field->getNumber('ct_max_weight', $this->getFloatParameter('ct_max_weight')), true);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('ct_active', $this->getStringParameter('ct_active')));
        }

        # Create a portlet box.
        $portlet = new Portlet('CtGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }
}
