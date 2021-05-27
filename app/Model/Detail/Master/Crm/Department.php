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
use App\Model\Dao\Master\Crm\DepartmentDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail Department page
 *
 * @package    app
 * @subpackage Model\Detail\Master\Crm
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Department extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'dpt', 'dpt_id');
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
            'dpt_name' => $this->getStringParameter('dpt_name'),
            'dpt_ss_id' => $this->User->getSsId(),
            'dpt_active' => $this->getStringParameter('dpt_active', 'Y'),
        ];
        $dptDao = new DepartmentDao();
        $dptDao->doInsertTransaction($colVal);

        return $dptDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'dpt_name' => $this->getStringParameter('dpt_name'),
            'dpt_active' => $this->getStringParameter('dpt_active', 'Y'),
        ];
        $dptDao = new DepartmentDao();
        $dptDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return DepartmentDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
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
            $this->Validation->checkRequire('dpt_name', 3, 256);
            $this->Validation->checkUnique('dpt_name', 'department', [
                'dpt_id' => $this->getDetailReferenceValue()
            ], [
                'dpt_ss_id' => $this->User->getSsId()
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
        $portlet->setGridDimension(6, 6, 12);
        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getCrmWord('name'), $this->Field->getText('dpt_name', $this->getStringParameter('dpt_name')), true);
        $fieldSet->addField(Trans::getCrmWord('active'), $this->Field->getYesNo('dpt_active', $this->getStringParameter('dpt_active')));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }
}
