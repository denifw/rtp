<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Detail\System\Master;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\Master\ServiceDao;

/**
 * Class to handle the creation of detail Service page
 *
 * @package    app
 * @subpackage Model\Detail\System\Service
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Service extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'srv', 'srv_id');
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
            'srv_code' => mb_strtolower(StringFormatter::replaceSpecialCharacter($this->getStringParameter('srv_code'))),
            'srv_name' => $this->getStringParameter('srv_name'),
            'srv_active' => $this->getStringParameter('srv_active', 'Y'),
        ];
        $srvDao = new ServiceDao();
        $srvDao->doInsertTransaction($colVal);

        return $srvDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'srv_code' => mb_strtolower(StringFormatter::replaceSpecialCharacter($this->getStringParameter('srv_code'))),
            'srv_name' => $this->getStringParameter('srv_name'),
            'srv_active' => $this->getStringParameter('srv_active', 'Y'),
        ];
        $srvDao = new ServiceDao();
        $srvDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return ServiceDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('srv_name', 2, 125);
        $this->Validation->checkRequire('srv_code', 2, 125);
        $this->Validation->checkSpecialCharacter('srv_code');
        $this->Validation->checkUnique('srv_code', 'service', [
            'srv_id' => $this->getDetailReferenceValue(),
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Add field to field set
        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('srv_code', $this->getStringParameter('srv_code')), true);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('srv_name', $this->getStringParameter('srv_name')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('srv_active', $this->getStringParameter('srv_active')));
        # Create a portlet box.
        $portlet = new Portlet('srvGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }

}
