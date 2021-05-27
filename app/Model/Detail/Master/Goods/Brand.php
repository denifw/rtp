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
use App\Model\Dao\Master\Goods\BrandDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;

/**
 * Class to handle the creation of detail Brand page
 *
 * @package    app
 * @subpackage Model\Detail\Master\Goods
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Brand extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct($parameters)
    {
        parent::__construct(get_class($this), 'brand', 'br_id');
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
            'br_ss_id' => $this->User->getSsId(),
            'br_name' => $this->getStringParameter('br_name'),
            'br_active' => $this->getStringParameter('br_active', 'Y')
        ];
        $brDao = new BrandDao();
        $brDao->doInsertTransaction($colVal);

        return $brDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'br_name' => $this->getStringParameter('br_name'),
            'br_active' => $this->getStringParameter('br_active', 'Y')
        ];
        $brDao = new BrandDao();
        $brDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return BrandDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
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
        $this->Validation->checkRequire('br_name');
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
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('br_name', $this->getStringParameter('br_name')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('br_active', $this->getStringParameter('br_active')));
        # Create a portlet box.
        $portlet = new Portlet('brGnrlPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6, 6, 6);

        return $portlet;
    }


}
