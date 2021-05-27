<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Detail\Master\Goods;

use App\Frame\Formatter\Trans;
use App\Model\Dao\Master\Goods\GoodsCategoryDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;

/**
 * Class to handle the creation of detail GoodsCategory page
 *
 * @package    app
 * @subpackage Model\Detail\Master\Goods
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class GoodsCategory extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'goodsCategory', 'gdc_id');
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
            'gdc_ss_id' => $this->User->getSsId(),
            'gdc_name' => $this->getStringParameter('gdc_name'),
            'gdc_active' => $this->getStringParameter('gdc_active', 'Y')
        ];
        $gdcDao = new GoodsCategoryDao();
        $gdcDao->doInsertTransaction($colVal);

        return $gdcDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'gdc_ss_id' => $this->User->getSsId(),
            'gdc_name' => $this->getStringParameter('gdc_name'),
            'gdc_active' => $this->getStringParameter('gdc_active', 'Y')
        ];
        $gdcDao = new GoodsCategoryDao();
        $gdcDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return GoodsCategoryDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
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
        $this->Validation->checkRequire('gdc_name');
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
        $fieldSet->setGridDimension(12, 12, 12, 12);
        # Add field to field set
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('gdc_name', $this->getStringParameter('gdc_name')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('gdc_active', $this->getStringParameter('gdc_active')));

        # Create a portlet box.
        $portlet = new Portlet('gdcGnrlPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6, 6, 6);

        return $portlet;
    }


}
