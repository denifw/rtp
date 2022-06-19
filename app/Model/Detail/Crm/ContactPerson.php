<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Crm;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Crm\ContactPersonDao;
use App\Model\Dao\Crm\OfficeDao;
use App\Model\Dao\Crm\RelationDao;

/**
 * Class to handle the creation of detail ContactPerson page
 *
 * @package    app
 * @subpackage Model\Detail\Crm\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class ContactPerson extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'cp', 'cp_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $sn = new SerialNumber($this->User->getSsId());
        $cpNumber = $sn->loadNumber('CP', $this->getStringParameter('cp_of_id', ''), $this->getStringParameter('cp_rel_id', ''));
        $colVal = [
            'cp_number' => $cpNumber,
            'cp_of_id' => $this->getStringParameter('cp_of_id'),
            'cp_name' => $this->getStringParameter('cp_name'),
            'cp_email' => $this->getStringParameter('cp_email'),
            'cp_phone' => $this->getStringParameter('cp_phone'),
            'cp_active' => $this->getStringParameter('cp_active', 'Y'),
        ];
        $cpDao = new ContactPersonDao();
        $cpDao->doInsertTransaction($colVal);
        if ($this->getStringParameter('cp_of_manager', 'N') === 'Y') {
            $ofDao = new OfficeDao();
            $ofDao->doUpdateTransaction($this->getStringParameter('cp_of_id'), [
                'of_cp_id' => $cpDao->getLastInsertId()
            ]);
        }
        return $cpDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'cp_of_id' => $this->getStringParameter('cp_of_id'),
            'cp_name' => $this->getStringParameter('cp_name'),
            'cp_email' => $this->getStringParameter('cp_email'),
            'cp_phone' => $this->getStringParameter('cp_phone'),
            'cp_active' => $this->getStringParameter('cp_active', 'Y'),
        ];
        $cpDao = new ContactPersonDao();
        $cpDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        if ($this->getStringParameter('cp_of_manager', 'N') === 'Y') {
            $ofDao = new OfficeDao();
            $ofDao->doUpdateTransaction($this->getStringParameter('cp_of_id'), [
                'of_cp_id' => $this->getDetailReferenceValue()
            ]);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return ContactPersonDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() === true) {
            if ($this->isValidParameter('cp_of_id') === true) {
                $office = OfficeDao::getByReference($this->getStringParameter('cp_of_id'));
                if (empty($office) === false) {
                    $this->setParameter('cp_office', $office['of_name']);
                    $this->setParameter('cp_rel_id', $office['of_rel_id']);
                    $this->setParameter('cp_relation', $office['of_relation']);
                } else {
                    $this->setParameter('cp_of_id', '');
                }
            } elseif ($this->isValidParameter('cp_rel_id') === true) {
                $relation = RelationDao::getByReference($this->getStringParameter('cp_rel_id'));
                if (empty($relation) === false) {
                    $this->setParameter('cp_relation', $relation['rel_name']);
                } else {
                    $this->setParameter('cp_rel_id', '');
                }
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
        $this->Validation->checkRequire('cp_rel_id');
        $this->Validation->checkRequire('cp_of_id');
        $this->Validation->checkRequire('cp_name', 2, 256);
        if ($this->isValidParameter('cp_email') === true) {
            $this->Validation->checkMaxLength('cp_email', 128);
            $this->Validation->checkEmail('cp_email');
        }
        $this->Validation->checkMaxLength('cp_phone', 25);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create Fields.
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('rel', 'cp_relation', $this->getStringParameter('cp_relation'));
        $relField->setHiddenField('cp_rel_id', $this->getStringParameter('cp_rel_id'));
        $relField->setDetailReferenceCode('rel_id');
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        if ($this->isInsert() === true && $this->isValidParameter('cp_rel_id') === true) {
            $relField->setReadOnly();
        }
        # Create Office Field
        $officeField = $this->Field->getSingleSelect('of', 'cp_office', $this->getStringParameter('cp_office'));
        $officeField->setHiddenField('cp_of_id', $this->getStringParameter('cp_of_id'));
        $officeField->setDetailReferenceCode('of_id');
        $officeField->addParameterById('of_rel_id', 'cp_rel_id', Trans::getWord('relation'));
        if ($this->isInsert() === true && $this->isValidParameter('cp_of_id') === true) {
            $relField->setReadOnly();
            $officeField->setReadOnly();
        }
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $fieldSet->addField(Trans::getWord('relation'), $relField, true);
        $fieldSet->addField(Trans::getWord('office'), $officeField, true);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('cp_name', $this->getStringParameter('cp_name')), true);
        $fieldSet->addField(Trans::getWord('email'), $this->Field->getText('cp_email', $this->getStringParameter('cp_email')));
        $fieldSet->addField(Trans::getWord('mainPic'), $this->Field->getYesNo('cp_of_manager', $this->getStringParameter('cp_of_manager')));
        $fieldSet->addField(Trans::getWord('phone'), $this->Field->getText('cp_phone', $this->getStringParameter('cp_phone')));
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('cp_active', $this->getStringParameter('cp_active')));
        }

        # Create a portlet box.
        $portlet = new Portlet('CpGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }
}
