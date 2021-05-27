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

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\TransportModuleDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail TransportModule page
 *
 * @package    app
 * @subpackage Model\Detail\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class TransportModule extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'transportModule', 'tm_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $code = StringFormatter::replaceSpecialCharacter($this->getStringParameter('tm_code'));
        $colVal = [
            'tm_name' => $this->getStringParameter('tm_name'),
            'tm_code' => mb_strtolower($code),
            'tm_active' => $this->getStringParameter('tm_active', 'Y'),
        ];

        $tmDao = new TransportModuleDao();
        $tmDao->doInsertTransaction($colVal);

        return $tmDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $code = StringFormatter::replaceSpecialCharacter($this->getStringParameter('tm_code'));
        $colVal = [
            'tm_name' => $this->getStringParameter('tm_name'),
            'tm_code' => mb_strtolower($code),
            'tm_active' => $this->getStringParameter('tm_active'),
        ];
        $tmDao = new TransportModuleDao();
        $tmDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return TransportModuleDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('tm_name', 2, 100);
        $this->Validation->checkUnique('tm_name', 'transport_module', [
            'tm_id' => $this->getDetailReferenceValue()
        ]);
        $this->Validation->checkRequire('tm_code', 2, 125);
        $this->Validation->checkUnique('tm_code', 'transport_module', [
            'tm_id' => $this->getDetailReferenceValue()
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        $portlet = new Portlet('TmGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(6, 6);
        # Create Fields.
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('tm_code', $this->getStringParameter('tm_code')), true);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('tm_name', $this->getStringParameter('tm_name')), true);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('tm_active', $this->getStringParameter('tm_active')));
        }
        # Create a portlet box.
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }
}
