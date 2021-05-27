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
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Master\Goods\GoodsCauseDamageDao;

/**
 * Class to handle the creation of detail GoodsCauseDamage page
 *
 * @package    app
 * @subpackage Model\Detail\Master\Goods
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class GoodsCauseDamage extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'goodsCauseDamage', 'gcd_id');
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
            'gcd_ss_id' => $this->User->getSsId(),
            'gcd_code' => $this->getStringParameter('gcd_code'),
            'gcd_description' => $this->getStringParameter('gcd_description'),
            'gcd_active' => $this->getStringParameter('gcd_active', 'Y'),
        ];
        $gcdDao = new GoodsCauseDamageDao();
        $gcdDao->doInsertTransaction($colVal);

        return $gcdDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'gcd_code' => $this->getStringParameter('gcd_code'),
            'gcd_description' => $this->getStringParameter('gcd_description'),
            'gcd_active' => $this->getStringParameter('gcd_active'),
        ];
        $gcdDao = new GoodsCauseDamageDao();
        $gcdDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return GoodsCauseDamageDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('gcd_code', 3, 125);
        $this->Validation->checkRequire('gcd_description', 3, 255);
        $this->Validation->checkUnique('gcd_code', 'goods_cause_damage', [
            'gcd_id' => $this->getDetailReferenceValue()
        ], [
            'gcd_ss_id' => $this->User->getSsId()
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
        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('gcd_code', $this->getStringParameter('gcd_code')), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('gcd_description', $this->getStringParameter('gcd_description')), true);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('gcd_active', $this->getStringParameter('gcd_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('GcdGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);


        return $portlet;
    }
}
