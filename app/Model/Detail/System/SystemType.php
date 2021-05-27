<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\System;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\SystemTypeDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail SystemType page
 *
 * @package    app
 * @subpackage Model\Detail\System
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class SystemType extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'sty', 'sty_id');
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
            'sty_group' => mb_strtolower($this->getStringParameter('sty_group')),
            'sty_name' => $this->getStringParameter('sty_name'),
            'sty_order' => $this->getIntParameter('sty_order'),
            'sty_label_type' => $this->getStringParameter('sty_label_type'),
            'sty_active' => $this->getStringParameter('sty_active', 'Y'),
        ];

        $styDao = new SystemTypeDao();
        $styDao->doInsertTransaction($colVal);
        return $styDao->getLastInsertId();
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
                'sty_group' => mb_strtolower($this->getStringParameter('sty_group')),
                'sty_name' => $this->getStringParameter('sty_name'),
                'sty_order' => $this->getIntParameter('sty_order'),
                'sty_label_type' => $this->getStringParameter('sty_label_type'),
                'sty_active' => $this->getStringParameter('sty_active', 'Y'),
            ];

            $styDao = new SystemTypeDao();
            $styDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } elseif ($this->isDeleteAction()) {
            $styDao = new SystemTypeDao();
            $styDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return SystemTypeDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());

        if ($this->isUpdate()) {
            $this->setEnableDeleteButton();
            if ($this->isValidParameter('sty_deleted_reason')) {
                $this->setEnableDeleteButton(false);
                $this->setDisableUpdate();

                $massage = "Deleted by " . $this->getStringParameter('sty_us_name') . " With Reason : ";
                $massage .= $this->getStringParameter('sty_deleted_reason');
                $this->View->addErrorMessage($massage);
            }
        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {
            $this->Validation->checkRequire('sty_group', '2', '256');
            $this->Validation->checkRequire('sty_name', '2', '256');
            $this->Validation->checkRequire('sty_order');
            $this->Validation->checkInt('sty_order', 1);
            $this->Validation->checkUnique('sty_name', 'system_type',
                [
                    'sty_id' => $this->getDetailReferenceValue(),
                ],
                [
                    'sty_group' => $this->getStringParameter('sty_group'),
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
        $portlet = new Portlet('StyPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(6, 6);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6, 6);

        $labelType = $this->Field->getSelect('sty_label_type', $this->getStringParameter('sty_label_type'));
        $labelType->addOptions([
            [
                'text' => 'Primary',
                'value' => 'primary',
            ],
            [
                'text' => 'Aqua',
                'value' => 'aqua',
            ],
            [
                'text' => 'Success',
                'value' => 'success',
            ],
            [
                'text' => 'Danger',
                'value' => 'danger',
            ],
            [
                'text' => 'Dark',
                'value' => 'dark',
            ],
            [
                'text' => 'Grey',
                'value' => 'default',
            ],
            [
                'text' => 'Warning',
                'value' => 'warning',
            ],
        ]);

        $fieldSet->addField(Trans::getWord('group'), $this->Field->getText('sty_group', $this->getStringParameter('sty_group')), true);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('sty_name', $this->getStringParameter('sty_name')), true);
        $fieldSet->addField(Trans::getWord('orderNumber'), $this->Field->getNumber('sty_order', $this->getIntParameter('sty_order')), true);
        $fieldSet->addField(Trans::getWord('labelType'), $labelType);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('sty_active', $this->getStringParameter('sty_active')));

        # Add field to field set


        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }
}
