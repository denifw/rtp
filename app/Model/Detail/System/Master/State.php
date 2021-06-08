<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\System\Master;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\System\Master\CountryDao;
use App\Model\Dao\System\Master\StateDao;

/**
 * Class to handle the creation of detail State page
 *
 * @package    app
 * @subpackage Model\Detail\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class State extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'stt', 'stt_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $colVal = [
            'stt_cnt_id' => $this->getStringParameter('stt_cnt_id'),
            'stt_name' => $this->getStringParameter('stt_name'),
            'stt_iso' => $this->getStringParameter('stt_iso'),
            'stt_active' => $this->getStringParameter('stt_active', 'Y')
        ];
        $sttDao = new StateDao();
        $sttDao->doInsertTransaction($colVal);

        return $sttDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'stt_cnt_id' => $this->getStringParameter('stt_cnt_id'),
            'stt_name' => $this->getStringParameter('stt_name'),
            'stt_iso' => $this->getStringParameter('stt_iso'),
            'stt_active' => $this->getStringParameter('stt_active', 'Y')
        ];
        $sttDao = new StateDao();
        $sttDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return StateDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() === true && $this->isValidParameter('stt_cnt_id') === true) {
            $cnt = CountryDao::getByReference($this->getStringParameter('stt_cnt_id'));
            if (empty($cnt) === false) {
                $this->setParameter('stt_country', $cnt['cnt_name']);
            } else {
                $this->setParameter('stt_cnt_id', '');
            }
        }
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('stt_cnt_id');
        $this->Validation->checkRequire('stt_name', 3, 125);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create Fields.
        $countryField = $this->Field->getSingleSelect('cnt', 'stt_country', $this->getStringParameter('stt_country'));
        $countryField->setHiddenField('stt_cnt_id', $this->getStringParameter('stt_cnt_id'));
        $countryField->setDetailReferenceCode('cnt_id');

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getWord('country'), $countryField, true);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('stt_name', $this->getStringParameter('stt_name')), true);
        $fieldSet->addField(Trans::getWord('isoCode'), $this->Field->getText('stt_iso', $this->getStringParameter('stt_iso')));
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('stt_active', $this->getStringParameter('stt_active')));
        }
        # Create a portlet box.
        $portlet = new Portlet('SttGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }
}
