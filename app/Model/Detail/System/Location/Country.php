<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\System\Location;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\Location\CountryDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail Country page
 *
 * @package    app
 * @subpackage Model\Detail\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Country extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'country', 'cnt_id');
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
            'cnt_name' => $this->getStringParameter('cnt_name'),
            'cnt_iso' => $this->getStringParameter('cnt_iso'),
            'cnt_active' => $this->getStringParameter('cnt_active', 'Y'),
        ];
        $cntDao = new CountryDao();
        $cntDao->doInsertTransaction($colVal);

        return $cntDao->getLastInsertId();

    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'cnt_name' => $this->getStringParameter('cnt_name'),
            'cnt_iso' => $this->getStringParameter('cnt_iso'),
            'cnt_active' => $this->getStringParameter('cnt_active', 'Y'),
        ];
        $cntDao = new CountryDao();
        $cntDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return CountryDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('cnt_name', 2, 125);
        $this->Validation->checkRequire('cnt_iso', 2, 10);
        $this->Validation->checkUnique('cnt_iso', 'country', [
            'cnt_id' => $this->getDetailReferenceValue()
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Add field into field set.
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('cnt_name', $this->getStringParameter('cnt_name')), true);
        $fieldSet->addField(Trans::getWord('isoCode'), $this->Field->getText('cnt_iso', $this->getStringParameter('cnt_iso')), true);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('cnt_active', $this->getStringParameter('cnt_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('CntGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }
}
