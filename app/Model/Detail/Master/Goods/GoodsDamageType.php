<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Master\Goods;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Master\Goods\GoodsDamageTypeDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail GoodsDamageType page
 *
 * @package    app
 * @subpackage Model\Detail\Master\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class GoodsDamageType extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'goodsDamageType', 'gdt_id');
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
            'gdt_ss_id' => $this->User->getSsId(),
            'gdt_code' => $this->getStringParameter('gdt_code'),
            'gdt_description' => $this->getStringParameter('gdt_description'),
            'gdt_active' => $this->getStringParameter('gdt_active', 'Y'),
        ];
        $gdtDao = new GoodsDamageTypeDao();
        $gdtDao->doInsertTransaction($colVal);

        return $gdtDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'gdt_code' => $this->getStringParameter('gdt_code'),
            'gdt_description' => $this->getStringParameter('gdt_description'),
            'gdt_active' => $this->getStringParameter('gdt_active'),
        ];
        $gdtDao = new GoodsDamageTypeDao();
        $gdtDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return GoodsDamageTypeDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
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
        $this->Validation->checkRequire('gdt_code', 3, 125);
        $this->Validation->checkRequire('gdt_description', 3, 255);
        $this->Validation->checkUnique('gdt_code', 'goods_damage_type', [
            'gdt_id' => $this->getDetailReferenceValue()
        ], [
            'gdt_ss_id' => $this->User->getSsId()
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
        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('gdt_code', $this->getStringParameter('gdt_code')), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('gdt_description', $this->getStringParameter('gdt_description')), true);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('gdt_active', $this->getStringParameter('gdt_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('GdtGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }
}
