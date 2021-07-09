<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Detail\Master\Employee;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\Master\Employee\ItemSalaryDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail ItemSalary page
 *
 * @package    app
 * @subpackage Model\Detail\Master\Employee
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class ItemSalary extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'isl', 'isl_id');
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
            'isl_ss_id' => $this->User->getSsId(),
            'isl_name' => $this->getStringParameter('isl_name'),
            'isl_active' => $this->getStringParameter('isl_active', 'Y'),
        ];
        $islDao = new ItemSalaryDao();
        $islDao->doInsertTransaction($colVal);
        return $islDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === null) {
            $colVal = [
                'isl_name' => $this->getStringParameter('isl_name'),
                'isl_active' => $this->getStringParameter('isl_active'),
            ];
            $islDao = new ItemSalaryDao();
            $islDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->isDeleteAction() === true) {
            $islDao = new ItemSalaryDao();
            $islDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return ItemSalaryDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
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
            $this->Validation->checkRequire('isl_name', 2, 256);
            $this->Validation->checkUnique('isl_name', 'item_salary', [
                'isl_id' => $this->getDetailReferenceValue()
            ], [
                'isl_ss_id' => $this->User->getSsId()
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
        $portlet = new Portlet('IslPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(6, 6);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $fieldSet->addField(Trans::getWord('active'), $this->Field->getText('isl_name', $this->getStringParameter('isl_name')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('isl_active', $this->getStringParameter('isl_active')));

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }
}
