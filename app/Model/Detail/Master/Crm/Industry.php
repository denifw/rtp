<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\Master\Crm;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Master\Crm\IndustryDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail Industry page
 *
 * @package    app
 * @subpackage Model\Detail\Master\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Industry extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ids', 'ids_id');
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
            'ids_name' => $this->getStringParameter('ids_name'),
            'ids_ss_id' => $this->User->getSsId(),
            'ids_active' => $this->getStringParameter('ids_active', 'Y'),
        ];
        $idsDao = new IndustryDao();
        $idsDao->doInsertTransaction($colVal);

        return $idsDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'ids_name' => $this->getStringParameter('ids_name'),
            'ids_active' => $this->getStringParameter('ids_active', 'Y'),
        ];
        $idsDao = new IndustryDao();
        $idsDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return IndustryDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {
            $this->Validation->checkRequire('ids_name', 3, 256);
            $this->Validation->checkUnique('ids_name', 'industry', [
                'ids_id' => $this->getDetailReferenceValue()
            ], [
                'ids_ss_id' => $this->User->getSsId()
            ]);
        } else {
            parent::loadValidationRole();
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('gnrlPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(6,6, 12);
        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12,12);
        $fieldSet->addField(Trans::getCrmWord('name'), $this->Field->getText('ids_name', $this->getStringParameter('ids_name')), true);
        $fieldSet->addField(Trans::getCrmWord('active'), $this->Field->getYesNo('ids_active', $this->getStringParameter('ids_active')));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }
}
