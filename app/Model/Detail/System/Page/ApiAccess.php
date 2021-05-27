<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\System\Page;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\Page\ApiAccessDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail ApiAccess page
 *
 * @package    app
 * @subpackage Model\Detail\System\Page
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class ApiAccess extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'apiAccess', 'aa_id');
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
            'aa_name' => $this->getStringParameter('aa_name'),
            'aa_description' => $this->getStringParameter('aa_description'),
            'aa_default' => $this->getStringParameter('aa_default', 'N'),
            'aa_active' => $this->getStringParameter('aa_active', 'Y'),
        ];
        $aaDao = new ApiAccessDao();
        $aaDao->doInsertTransaction($colVal);

        return $aaDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'aa_name' => $this->getStringParameter('aa_name'),
            'aa_description' => $this->getStringParameter('aa_description'),
            'aa_default' => $this->getStringParameter('aa_default', 'N'),
            'aa_active' => $this->getStringParameter('aa_active', 'Y'),
        ];
        $aaDao = new ApiAccessDao();
        $aaDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return ApiAccessDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('aa_name', 1, 125);
        $this->Validation->checkRequire('aa_description', 1, 255);
        $this->Validation->checkUnique('aa_name', 'api_access', [
            'aa_id' => $this->getDetailReferenceValue()
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
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('aa_name', $this->getStringParameter('aa_name')), true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('aa_description', $this->getStringParameter('aa_description')), true);
        $fieldSet->addField(Trans::getWord('default'), $this->Field->getYesNo('aa_default', $this->getStringParameter('aa_default')));
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('aa_active', $this->getStringParameter('aa_active')));

        # Create a portlet box.
        $portlet = new Portlet('AaGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }
}
