<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Custom\wlog\Detail\Job\Warehouse;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Modal;
use App\Model\Dao\Job\Warehouse\JobInboundDao;
use Illuminate\Support\Facades\DB;

/**
 * Class to handle the creation of detail JobInbound page
 *
 * @package    app
 * @subpackage Custom\wlog\Detail\Job\Warehouse
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class JobInbound extends \App\Model\Detail\Job\Warehouse\JobInbound
{

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        parent::doUpdate();
        if ($this->getFormAction() === null) {
            $data = $this->loadJobInboundData();
            $numbers = [];
            $numbers[] = $this->getStringParameter('jo_customer_ref');
            $numbers[] = $this->getStringParameter('jo_aju_ref');
            $lotNumber = implode(' - ', $numbers);
            $jidDao = new JobInboundDao();
            foreach ($data as $row) {
                $jidColVal = [
                    'jid_lot_number' => $lotNumber
                ];
                $jidDao->doUpdateTransaction($row['jid_id'], $jidColVal);
            }
        }
    }

    /**
     * Function to get the general Field Set.
     *
     * @return FieldSet
     */
    protected function getReferenceField(): FieldSet
    {
        # Create Fields.

        $ajuField = $this->Field->getText('jo_aju_ref', $this->getStringParameter('jo_aju_ref'));
        if ($this->isValidParameter('ji_start_store_on') === true) {
            $ajuField->setReadOnly();
        }
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getWord('customerRef'), $this->Field->getText('jo_customer_ref', $this->getStringParameter('jo_customer_ref')));
        $fieldSet->addField(Trans::getWord('blRef'), $this->Field->getText('jo_bl_ref', $this->getStringParameter('jo_bl_ref')));
        $fieldSet->addField(Trans::getWord('packingListRef'), $this->Field->getText('jo_packing_ref', $this->getStringParameter('jo_packing_ref')));
        $fieldSet->addField(Trans::getWord('ajuRef'), $ajuField, true);
        $fieldSet->addField(Trans::getWord('sppbRef'), $this->Field->getText('jo_sppb_ref', $this->getStringParameter('jo_sppb_ref')));

        return $fieldSet;
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {
            $this->Validation->checkRequire('jo_aju_ref');
        }
        parent::loadValidationRole();
    }
    /**
     * Function to load the validation role.
     *
     * @return array
     */
    private function loadJobInboundData(): array
    {
        $wheres = [];
        $wheres[] = '(ji.ji_jo_id = ' . $this->getDetailReferenceValue() . ')';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT jid.jid_id, jid.jid_lot_number
                    FROM job_inbound as ji INNER JOIN
                    job_inbound_detail as jid ON jid.jid_ji_id = ji.ji_id ' . $strWheres;
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }


    /**
     * Function to get publish confirmation modal.
     *
     * @return Modal
     */
    protected function getJoPublishModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JoPubMdl', Trans::getWord('publishConfirmation'));
        if (empty($this->Goods) === true || $this->isValidParameter('jo_manager_id') === false || $this->isValidParameter('jo_aju_ref') === false) {
            $modal->setTitle(Trans::getWord('warning'));
            $modal->addHeaderAttribute('class', 'modal-header alert-warning');
            $text = Trans::getWord('unablePublishJobOrderWlog', 'message');
            $modal->setDisableBtnOk();
        } else {
            $text = Trans::getWord('publishJobConfirmation', 'message');
            $modal->setFormSubmit($this->getMainFormId(), 'doPublishJob');
        }
        $modal->setBtnOkName(Trans::getWord('yesPublish'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }
}
