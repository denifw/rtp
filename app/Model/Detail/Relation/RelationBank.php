<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Detail\Relation;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Relation\RelationBankDao;
use App\Model\Dao\Relation\RelationDao;

/**
 * Class to handle the creation of detail RelationBank page
 *
 * @package    app
 * @subpackage Model\Detail\Crm\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class RelationBank extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'relationBank', 'rb_id');
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
            'rb_rel_id' => $this->getIntParameter('rb_rel_id'),
            'rb_bn_id' => $this->getIntParameter('rb_bn_id'),
            'rb_number' => $this->getStringParameter('rb_number'),
            'rb_branch' => $this->getStringParameter('rb_branch'),
            'rb_name' => $this->getStringParameter('rb_name'),
            'rb_active' => $this->getStringParameter('rb_active', 'Y'),
        ];
        $rbDao = new RelationBankDao();
            $rbDao->doInsertTransaction($colVal);
        return $rbDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'rb_rel_id' => $this->getIntParameter('rb_rel_id'),
            'rb_bn_id' => $this->getIntParameter('rb_bn_id'),
            'rb_number' => $this->getStringParameter('rb_number'),
            'rb_branch' => $this->getStringParameter('rb_branch'),
            'rb_name' => $this->getStringParameter('rb_name'),
            'rb_active' => $this->getStringParameter('rb_active', 'Y'),
        ];
        $rbDao = new RelationBankDao();
        $rbDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return RelationBankDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() && $this->isValidParameter('rb_rel_id') === true) {
            $relation = RelationDao::getByReference($this->getIntParameter('rb_rel_id'));
            if (empty($relation) === false) {
                $this->setParameter('rb_relation', $relation['rel_name']);
            } else {
                $this->setParameter('rb_rel_id', '');
            }
        }
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('rb_rel_id');
        $this->Validation->checkRequire('rb_bn_id');
        $this->Validation->checkRequire('rb_number');
        $this->Validation->checkRequire('rb_name');
        $this->Validation->checkRequire('rb_branch');
        $this->Validation->checkUnique('rb_number', 'relation_bank', [
            'rb_id' => $this->getDetailReferenceValue(),
        ], [
            'rb_rel_id' => $this->getIntParameter('rb_rel_id'),
            'rb_bn_id' => $this->getIntParameter('rb_bn_id'),
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Create Fields.
        # Create Relation Field
        $relField = $this->Field->getSingleSelect('relation', 'rb_relation', $this->getStringParameter('rb_relation'));
        $relField->setHiddenField('rb_rel_id', $this->getIntParameter('rb_rel_id'));
        $relField->setDetailReferenceCode('rel_id');
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        if ($this->isInsert() === true && $this->isValidParameter('rb_rel_id') === true) {
            $relField->setReadOnly();
        }
        # Create Office Field
        $bankField = $this->Field->getSingleSelect('bank', 'rb_bank', $this->getStringParameter('rb_bank'));
        $bankField->setHiddenField('rb_bn_id', $this->getIntParameter('rb_bn_id'));
        $bankField->setEnableNewButton(false);
        $bankField->setEnableDetailButton(false);

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $fieldSet->addField(Trans::getWord('relation'), $relField, true);
        $fieldSet->addField(Trans::getWord('bank'), $bankField, true);
        $fieldSet->addField(Trans::getWord('accountNumber'), $this->Field->getText('rb_number', $this->getStringParameter('rb_number')), true);
        $fieldSet->addField(Trans::getWord('accountName'), $this->Field->getText('rb_name', $this->getStringParameter('rb_name')), true);
        $fieldSet->addField(Trans::getWord('branch'), $this->Field->getText('rb_branch', $this->getStringParameter('rb_branch')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('rb_active', $this->getStringParameter('rb_active')));

        # Create a portlet box.
        $portlet = new Portlet('GnFormPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }
}
