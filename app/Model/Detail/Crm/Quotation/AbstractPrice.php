<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\Crm\Quotation;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Crm\Quotation\PriceDao;
use App\Model\Dao\Crm\Quotation\PriceDetailDao;
use App\Model\Dao\Crm\Quotation\QuotationDao;
use App\Model\Dao\System\Location\DistrictCodeDao;

/**
 * Class to handle the creation of detail Price page
 *
 * @package    app
 * @subpackage Model\Detail\Crm
 * @author     Daniar Dwi Hartomo <daniar@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
abstract class AbstractPrice extends AbstractFormModel
{
    /**
     * Function to do insert of trucking price;
     *
     * @param bool $isCopy To trigger is it a copy action or not.
     *
     * @return int
     */
    protected function doInsertTrucking(bool $isCopy = false): int
    {
        $postFix = '';
        if ($isCopy === true) {
            $postFix = '_cp';
        }
        # UpSer Origin district code
        $this->doUpSerOriginDistrictCode($isCopy);

        # UpSer Destination District Code
        $this->doUpSerDestinationDistrictCode($isCopy);

        $colVal = [
            'prc_ss_id' => $this->User->getSsId(),
            'prc_type' => $this->getStringParameter('prc_type', 'S'),
            'prc_rel_id' => $this->getIntParameter('prc_rel_id'),
            'prc_qt_id' => $this->getIntParameter('prc_qt_id'),
            'prc_srv_id' => $this->getIntParameter('prc_srv_id'),
            'prc_srt_id' => $this->getIntParameter('prc_srt_id'),
            'prc_lead_time' => $this->getFloatParameter('prc_lead_time' . $postFix),
            'prc_code' => $this->getTruckingCodeParameter($isCopy),
            'prc_eg_id' => $this->getIntParameter('prc_eg_id' . $postFix),
            'prc_origin_address' => $this->getStringParameter('prc_origin_address' . $postFix),
            'prc_dtc_origin' => $this->getIntParameter('prc_dtc_origin' . $postFix),
            'prc_destination_address' => $this->getStringParameter('prc_destination_address' . $postFix),
            'prc_dtc_destination' => $this->getIntParameter('prc_dtc_destination' . $postFix),
            'prc_tm_id' => $this->getIntParameter('prc_tm_id' . $postFix),
            'prc_pol_id' => $this->getIntParameter('prc_pol_id' . $postFix),
            'prc_pod_id' => $this->getIntParameter('prc_pod_id' . $postFix),
        ];
        if ($this->getStringParameter('prc_srt_container', 'N') === 'Y') {
            $colVal['prc_ct_id'] = $this->getIntParameter('prc_ct_id' . $postFix);
        }
        $prcDao = new PriceDao();
        $prcDao->doInsertTransaction($colVal);

        return $prcDao->getLastInsertId();
    }

    /**
     * Function to do insert of inklaring price;
     *
     * @param bool $isCopy To trigger is it a copy action or not.
     *
     * @return int
     */
    protected function doInsertInklaring(bool $isCopy = false): int
    {
        $postFix = '';
        if ($isCopy === true) {
            $postFix = '_cp';
        }
        $colVal = [
            'prc_ss_id' => $this->User->getSsId(),
            'prc_type' => $this->getStringParameter('prc_type', 'S'),
            'prc_rel_id' => $this->getIntParameter('prc_rel_id'),
            'prc_qt_id' => $this->getIntParameter('prc_qt_id'),
            'prc_srv_id' => $this->getIntParameter('prc_srv_id'),
            'prc_srt_id' => $this->getIntParameter('prc_srt_id'),
            'prc_lead_time' => $this->getFloatParameter('prc_lead_time' . $postFix),
            'prc_code' => $this->getInklaringCodeParameter($isCopy),
            'prc_cct_id' => $this->getIntParameter('prc_cct_id' . $postFix),
            'prc_tm_id' => $this->getIntParameter('prc_tm_id' . $postFix),
            'prc_ct_id' => null,
        ];
        if ($this->isSrtPod() === true) {
            $colVal['prc_pod_id'] = $this->getIntParameter('prc_po_id' . $postFix);
        } else {
            $colVal['prc_pol_id'] = $this->getIntParameter('prc_po_id' . $postFix);
        }
        if ($this->getStringParameter('prc_srt_container', 'N') === 'Y') {
            $colVal['prc_ct_id'] = $this->getIntParameter('prc_ct_id' . $postFix);
        }
        $prcDao = new PriceDao();
        $prcDao->doInsertTransaction($colVal);

        return $prcDao->getLastInsertId();
    }

    /**
     * Function to do insert of warehouse price;
     *
     * @param bool $isCopy To trigger is it a copy action or not.
     *
     * @return int
     */
    protected function doInsertWarehouse(bool $isCopy = false): int
    {
        $postFix = '';
        if ($isCopy === true) {
            $postFix = '_cp';
        }
        $colVal = [
            'prc_ss_id' => $this->User->getSsId(),
            'prc_type' => $this->getStringParameter('prc_type', 'S'),
            'prc_rel_id' => $this->getIntParameter('prc_rel_id'),
            'prc_qt_id' => $this->getIntParameter('prc_qt_id'),
            'prc_srv_id' => $this->getIntParameter('prc_srv_id'),
            'prc_code' => $this->getWarehouseCodeParameter($isCopy),
            'prc_wh_id' => $this->getIntParameter('prc_wh_id' . $postFix),
        ];
        $prcDao = new PriceDao();
        $prcDao->doInsertTransaction($colVal);

        return $prcDao->getLastInsertId();
    }

    /**
     * Function to get trucking code from parameter.;
     *
     * @param bool $isCopy To trigger copy parameter.
     *
     * @return string
     */
    private function getTruckingCodeParameter(bool $isCopy = false): string
    {
        if ($this->isInsert() === true) {
            return '';
        }
        $postFix = '';
        if ($isCopy === true) {
            $postFix = '_cp';
        }
        if ($this->isDoorToDoor() === true) {
            return $this->getStringParameter('prc_dtc_or_code' . $postFix) . '-' . $this->getStringParameter('prc_dtc_des_code' . $postFix) . '-' . $this->getStringParameter('prc_eg_code' . $postFix);
        }
        if ($this->isDoorToPort() === true) {
            return $this->getStringParameter('prc_dtc_or_code' . $postFix) . '-' . $this->getStringParameter('prc_pod_code' . $postFix) . '-' . $this->getStringParameter('prc_eg_code' . $postFix);
        }
        if ($this->isPortToDoor() === true) {
            return $this->getStringParameter('prc_pol_code' . $postFix) . '-' . $this->getStringParameter('prc_dtc_des_code' . $postFix) . '-' . $this->getStringParameter('prc_eg_code' . $postFix);
        }
        return $this->getStringParameter('prc_pol_code' . $postFix) . '-' . $this->getStringParameter('prc_pod_code' . $postFix) . '-' . $this->getStringParameter('prc_eg_code' . $postFix);
    }

    /**
     * Function to get inklaring code from parameter.;
     *
     * @param bool $isCopy To trigger is it a copy action or not.
     *
     * @return string
     */
    private function getInklaringCodeParameter(bool $isCopy = false): string
    {
        $postFix = '';
        if ($isCopy === true) {
            $postFix = '_cp';
        }

        $code = $this->getStringParameter('prc_po_code' . $postFix) . '-' . $this->getStringParameter('prc_cct_code' . $postFix);
        if ($this->getStringParameter('prc_srt_container', 'N') === 'Y' && $this->isValidParameter('prc_ct_code' . $postFix) === true) {
            $code .= '-' . $this->getStringParameter('prc_ct_code' . $postFix);
        }
        return $code;
    }

    /**
     * Function to get warehouse code from parameter.;
     *
     * @param bool $isCopy To trigger is it a copy action or not.
     *
     * @return string
     */
    private function getWarehouseCodeParameter(bool $isCopy = false): string
    {
        $postFix = '';
        if ($isCopy === true) {
            $postFix = '_cp';
        }
        return 'WH-' . $this->getStringParameter('prc_warehouse' . $postFix);
    }

    /**
     * Function to update origin district code;
     *
     * @param bool $isCopy To trigger copy parameter.
     *
     * @return void
     */
    private function doUpSerOriginDistrictCode(bool $isCopy = false): void
    {
        $postFix = '';
        if ($isCopy === true) {
            $postFix = '_cp';
        }

        if ($this->getStringParameter('prc_dtc_or_code' . $postFix) !== $this->getStringParameter('prc_or_dtcc_code' . $postFix)) {
            $dtccColVal = [
                'dtcc_ss_id' => $this->User->getSsId(),
                'dtcc_dtc_id' => $this->getIntParameter('prc_dtc_origin' . $postFix),
                'dtcc_code' => $this->getStringParameter('prc_dtc_or_code' . $postFix),
            ];

            $dtccDao = new DistrictCodeDao();
            if ($this->isValidParameter('prc_or_dtcc_id' . $postFix) === true) {
                $dtccDao->doUpdateTransaction($this->getIntParameter('prc_or_dtcc_id' . $postFix), $dtccColVal);
            } else {
                $dtccDao->doInsertTransaction($dtccColVal);
            }
        }
    }

    /**
     * Function to update origin district code;
     *
     * @param bool $isCopy To trigger copy parameter.
     *
     * @return void
     */
    private function doUpSerDestinationDistrictCode(bool $isCopy = false): void
    {
        $postFix = '';
        if ($isCopy === true) {
            $postFix = '_cp';
        }
        if ($this->getStringParameter('prc_dtc_des_code' . $postFix) !== $this->getStringParameter('prc_des_dtcc_code' . $postFix)) {
            $dtccColVal = [
                'dtcc_ss_id' => $this->User->getSsId(),
                'dtcc_dtc_id' => $this->getIntParameter('prc_dtc_destination' . $postFix),
                'dtcc_code' => $this->getStringParameter('prc_dtc_des_code' . $postFix),
            ];

            $dtccDao = new DistrictCodeDao();
            if ($this->isValidParameter('prc_des_dtcc_id' . $postFix) === true) {
                $dtccDao->doUpdateTransaction($this->getIntParameter('prc_des_dtcc_id' . $postFix), $dtccColVal);
            } else {
                $dtccDao->doInsertTransaction($dtccColVal);
            }
        }
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doUpdatePriceDetail') {
            $exchangeRate = $this->getFloatParameter('prd_exchange_rate');
            if ($this->getIntParameter('prd_cur_id') === $this->User->Settings->getCurrencyId()) {
                $exchangeRate = 1;
            }

            $taxAmount = 0.0;
            $rate = $this->getFloatParameter('prd_rate') * $this->getFloatParameter('prd_quantity');
            if ($this->isValidParameter('prd_exchange_rate') === true) {
                $rate *= $exchangeRate;
            }
            if ($this->isValidParameter('prd_tax_id') === true && $this->isValidParameter('prd_tax_percent')) {
                $taxPercent = $this->getFloatParameter('prd_tax_percent');
                $taxAmount = ($rate * $taxPercent) / 100;
            }
            $total = $rate + $taxAmount;
            $colPrdVal = [
                'prd_prc_id' => $this->getDetailReferenceValue(),
                'prd_cc_id' => $this->getIntParameter('prd_cc_id'),
                'prd_description' => $this->getStringParameter('prd_description'),
                'prd_quantity' => $this->getFloatParameter('prd_quantity'),
                'prd_rate' => $this->getFloatParameter('prd_rate'),
                'prd_minimum_rate' => $this->getFloatParameter('prd_minimum_rate'),
                'prd_cur_id' => $this->getIntParameter('prd_cur_id'),
                'prd_exchange_rate' => $exchangeRate,
                'prd_uom_id' => $this->getIntParameter('prd_uom_id'),
                'prd_tax_id' => $this->getIntParameter('prd_tax_id'),
                'prd_total' => $total,
                'prd_remark' => $this->getStringParameter('prd_remark'),
            ];
            $prdDao = new PriceDetailDao();
            if ($this->isValidParameter('prd_id') === false) {
                $prdDao->doInsertTransaction($colPrdVal);
            } else {
                $prdDao->doUpdateTransaction($this->getIntParameter('prd_id'), $colPrdVal);
            }
        } elseif ($this->getFormAction() === 'doDeletePriceDetail') {
            $prdDao = new PriceDetailDao();
            $prdDao->doDeleteTransaction($this->getIntParameter('prd_id_del'));
        } elseif ($this->isDeleteAction()) {
            $delDao = new PriceDao();
            $delDao->doDeleteTransaction($this->getDetailReferenceValue(), $this->getReasonDeleteAction());
        }
    }

    /**
     * Function to do the update trucking price.;
     *
     * @return void
     */
    protected function doUpdateTrucking(): void
    {
        # UpSer Origin district code
        $this->doUpSerOriginDistrictCode();

        # UpSer Destination District Code
        $this->doUpSerDestinationDistrictCode();
        $colVal = [
            'prc_type' => $this->getStringParameter('prc_type'),
            'prc_qt_id' => $this->getIntParameter('prc_qt_id'),
            'prc_rel_id' => $this->getIntParameter('prc_rel_id'),
            'prc_srv_id' => $this->getIntParameter('prc_srv_id'),
            'prc_srt_id' => $this->getIntParameter('prc_srt_id'),
            'prc_lead_time' => $this->getFloatParameter('prc_lead_time'),
            'prc_code' => $this->getTruckingCodeParameter(),
            'prc_eg_id' => $this->getIntParameter('prc_eg_id'),
            'prc_origin_address' => $this->getStringParameter('prc_origin_address'),
            'prc_dtc_origin' => $this->getIntParameter('prc_dtc_origin'),
            'prc_destination_address' => $this->getStringParameter('prc_destination_address'),
            'prc_dtc_destination' => $this->getIntParameter('prc_dtc_destination'),
            'prc_tm_id' => $this->getIntParameter('prc_tm_id'),
            'prc_pol_id' => $this->getIntParameter('prc_pol_id'),
            'prc_pod_id' => $this->getIntParameter('prc_pod_id'),
        ];
        if ($this->getStringParameter('prc_srt_container', 'N') === 'Y') {
            $colVal['prc_ct_id'] = $this->getIntParameter('prc_ct_id');
        }
        $prcDao = new PriceDao();
        $prcDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Function to do the update inklaring price.;
     *
     * @return void
     */
    protected function doUpdateInklaring(): void
    {
        $colVal = [
            'prc_type' => $this->getStringParameter('prc_type'),
            'prc_qt_id' => $this->getIntParameter('prc_qt_id'),
            'prc_rel_id' => $this->getIntParameter('prc_rel_id'),
            'prc_srv_id' => $this->getIntParameter('prc_srv_id'),
            'prc_srt_id' => $this->getIntParameter('prc_srt_id'),
            'prc_lead_time' => $this->getFloatParameter('prc_lead_time'),
            'prc_code' => $this->getInklaringCodeParameter(),
            'prc_cct_id' => $this->getIntParameter('prc_cct_id'),
            'prc_tm_id' => $this->getIntParameter('prc_tm_id'),
            'prc_ct_id' => null
        ];
        if ($this->isSrtPod() === true) {
            $colVal['prc_pod_id'] = $this->getIntParameter('prc_po_id');
        } else {
            $colVal['prc_pol_id'] = $this->getIntParameter('prc_po_id');
        }
        if ($this->getStringParameter('prc_srt_container', 'N') === 'Y') {
            $colVal['prc_ct_id'] = $this->getIntParameter('prc_ct_id');
        }
        $prcDao = new PriceDao();
        $prcDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Function to do the update warehouse price.;
     *
     * @return void
     */
    protected function doUpdateWarehouse(): void
    {
        $colVal = [
            'prc_type' => $this->getStringParameter('prc_type'),
            'prc_qt_id' => $this->getIntParameter('prc_qt_id'),
            'prc_rel_id' => $this->getIntParameter('prc_rel_id'),
            'prc_srv_id' => $this->getIntParameter('prc_srv_id'),
            'prc_code' => $this->getWarehouseCodeParameter(),
            'prc_wh_id' => $this->getIntParameter('prc_wh_id'),
        ];
        $prcDao = new PriceDao();
        $prcDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Function to do the update inklaring price.;
     *
     * @param int $newPrcId To store the id of price.
     *
     * @return void
     */
    protected function doCopyPriceDetail($newPrcId): void
    {
        $data = PriceDetailDao::getByPriceId($this->getDetailReferenceValue());
        $prdDao = new PriceDetailDao();
        foreach ($data as $row) {
            $colPrdVal = [
                'prd_prc_id' => $newPrcId,
                'prd_cc_id' => $row['prd_cc_id'],
                'prd_description' => $row['prd_description'],
                'prd_quantity' => $row['prd_quantity'],
                'prd_rate' => $row['prd_rate'],
                'prd_minimum_rate' => $row['prd_minimum_rate'],
                'prd_cur_id' => $row['prd_cur_id'],
                'prd_exchange_rate' => $row['prd_exchange_rate'],
                'prd_uom_id' => $row['prd_uom_id'],
                'prd_tax_id' => $row['prd_tax_id'],
                'prd_total' => $row['prd_total'],
                'prd_remark' => $row['prd_remark'],
            ];
            $prdDao->doInsertTransaction($colPrdVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return PriceDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() === true && $this->isValidParameter('prc_qt_id') === true) {
            $quotation = QuotationDao::getByReferenceAndSystem($this->getIntParameter('prc_qt_id'), $this->User->getSsId());
            if (empty($quotation) === false) {
                $this->setParameter('prc_qt_id', $quotation['qt_id']);
                $this->setParameter('prc_qt_number', $quotation['qt_number']);
                $this->setParameter('prc_rel_id', $quotation['qt_rel_id']);
                $this->setParameter('prc_relation', $quotation['qt_relation']);
            }
        }
        # Override Title
        $this->overrideTitlePage();
        if ($this->isDeleted() === true) {
            $this->View->addErrorMessage(Trans::getMessageWord('deletedData', '', [
                'user' => $this->getStringParameter('prc_deleted_by'),
                'time' => DateTimeParser::format($this->getStringParameter('prc_deleted_on')),
                'reason' => $this->getStringParameter('prc_deleted_reason'),
            ]));
            $this->setEnableDeleteButton(false);
        }

    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === 'doUpdatePriceDetail') {
            $this->Validation->checkRequire('prd_cc_id');
            $this->Validation->checkRequire('prd_description', 3, 256);
            $this->Validation->checkRequire('prd_uom_id');
            $this->Validation->checkRequire('prd_quantity');
            $this->Validation->checkFloat('prd_quantity');
            $this->Validation->checkRequire('prd_rate');
            $this->Validation->checkFloat('prd_rate');
            $this->Validation->checkMaxLength('prd_remark', 256);
            $this->Validation->checkRequire('prd_cur_id');
            if ($this->isValidParameter('prd_cur_id') === true && $this->User->Settings->getCurrencyId() !== $this->getIntParameter('prd_cur_id')) {
                $this->Validation->checkFloat('prd_exchange_rate');
            }
            if ($this->isValidParameter('prd_cc_id') === true) {
                $this->Validation->checkUnique('prd_cc_id', 'price_detail', [
                    'prd_id' => $this->getIntParameter('prd_id'),
                ], [
                    'prd_prc_id' => $this->getDetailReferenceValue(),
                    'prd_uom_id' => $this->getIntParameter('prd_uom_id'),
                    'prd_deleted_on' => null,
                ]);
            }
        } elseif ($this->getFormAction() === 'doDeletePriceDetail') {
            $this->Validation->checkRequire('prd_id_del');
        } else {
            parent::loadValidationRole();
        }
    }

    /**
     * Function to get query for checking unique trucking data.
     *
     * @param bool $isCopy To trigger is it copy action or not.
     *
     * @return void
     */
    protected function loadValidationTrucking(bool $isCopy = false): void
    {
        $postfix = '';
        if ($isCopy === true) {
            $postfix = '_cp';
        }
        $this->Validation->checkRequire('prc_type');
        $this->Validation->checkRequire('prc_srv_code');
        $this->Validation->checkRequire('prc_qt_id');
        $this->Validation->checkRequire('prc_rel_id');
        $this->Validation->checkRequire('prc_srt_id');
        $this->Validation->checkRequire('prc_lead_time' . $postfix);
        $this->Validation->checkFloat('prc_lead_time' . $postfix, 0.1);
        $this->Validation->checkRequire('prc_eg_id' . $postfix);
        $this->Validation->checkRequire('prc_eg_code' . $postfix);
        if ($this->getStringParameter('prc_srt_container', 'N') === 'Y') {
            $this->Validation->checkRequire('prc_ct_id' . $postfix);
        }
        if ($this->isUpdate() === true) {
            if ($this->isSrtPol() === true) {
                $this->Validation->checkRequire('prc_pol_id' . $postfix);
                $this->Validation->checkRequire('prc_pol_code' . $postfix);
            }
            if ($this->isSrtPod() === true) {
                $this->Validation->checkRequire('prc_pod_id' . $postfix);
                $this->Validation->checkRequire('prc_pod_code' . $postfix);
            }
            if ($this->isSrtPol() === true && $this->isSrtPod() === true) {
                $this->Validation->checkRequire('prc_tm_id' . $postfix);
                $this->Validation->checkRequire('prc_tm_code' . $postfix);
            }
            if ($this->isSrtLoad() === true) {
                $this->Validation->checkRequire('prc_dtc_origin' . $postfix);
                $this->Validation->checkRequire('prc_dtc_or_code' . $postfix);
                # Check unique origin district code
                $this->Validation->checkAdvanceUnique('prc_dtc_or_code' . $postfix, 'district_code', 'dtcc_code', [
                    'dtcc_id' => $this->getIntParameter('prc_or_dtcc_id' . $postfix),
                ], [
                    'dtcc_ss_id' => $this->User->getSsId(),
                    'dtcc_deleted_on' => null,
                ]);
            }
            if ($this->isSrtUnload() === true) {
                $this->Validation->checkRequire('prc_dtc_destination' . $postfix);
                $this->Validation->checkRequire('prc_dtc_des_code' . $postfix);
                # Check unique destination district code
                $this->Validation->checkAdvanceUnique('prc_dtc_des_code' . $postfix, 'district_code', 'dtcc_code', [
                    'dtcc_id' => $this->getIntParameter('prc_des_dtcc_id' . $postfix),
                ], [
                    'dtcc_ss_id' => $this->User->getSsId(),
                    'dtcc_deleted_on' => null,
                ]);
            }
            # Check unique price
            $uniqueMessage = Trans::getMessageWord('uniquePriceTrucking');
            $this->Validation->checkEmptyQueryResult('prc_eg_id' . $postfix, $this->getQueryCheckUniqueTrucking($isCopy), $uniqueMessage);
        }
    }

    /**
     * Function to get query for checking unique trucking data.
     *
     * @param bool $isCopy To trigger is it copy action or not.
     *
     * @return void
     */
    protected function loadValidationInklaring(bool $isCopy = false): void
    {
        $postfix = '';
        if ($isCopy === true) {
            $postfix = '_cp';
        }
        $this->Validation->checkRequire('prc_type');
        $this->Validation->checkRequire('prc_srv_code');
        $this->Validation->checkRequire('prc_rel_id');
        $this->Validation->checkRequire('prc_qt_id');
        $this->Validation->checkRequire('prc_srt_id');
        $this->Validation->checkRequire('prc_lead_time' . $postfix);
        $this->Validation->checkFloat('prc_lead_time' . $postfix, 0.1);
        $this->Validation->checkRequire('prc_tm_id' . $postfix);
        $this->Validation->checkRequire('prc_po_id' . $postfix);
        $this->Validation->checkRequire('prc_po_code' . $postfix);
        if ($this->getStringParameter('prc_srt_container', 'N') === 'Y') {
            $this->Validation->checkRequire('prc_ct_id' . $postfix);
            $this->Validation->checkRequire('prc_ct_code' . $postfix);
        }
        $uniqueMessage = Trans::getMessageWord('uniquePriceInklaring');
        $this->Validation->checkEmptyQueryResult('prc_cct_id' . $postfix, $this->getQueryCheckUniqueInklaring($isCopy), $uniqueMessage);
    }

    /**
     * Function to get query for checking unique warehouse data.
     *
     * @param bool $isCopy To trigger is it copy action or not.
     *
     * @return void
     */
    protected function loadValidationWarehouse(bool $isCopy = false): void
    {
        $postfix = '';
        if ($isCopy === true) {
            $postfix = '_cp';
        }
        $this->Validation->checkRequire('prc_type');
        $this->Validation->checkRequire('prc_srv_code');
        $this->Validation->checkRequire('prc_rel_id');
        $this->Validation->checkRequire('prc_qt_id');
        $this->Validation->checkRequire('prc_wh_id' . $postfix);
        $uniqueMessage = Trans::getMessageWord('uniquePriceWarehouse');
        $this->Validation->checkEmptyQueryResult('prc_wh_id' . $postfix, $this->getQueryCheckUniqueWarehouse($isCopy), $uniqueMessage);
    }

    /**
     * Function to get query for checking unique trucking data.
     *
     * @param bool $isCopy To trigger is it copy action or not.
     *
     * @return string
     */
    private function getQueryCheckUniqueTrucking(bool $isCopy = false): string
    {
        $wheres = [];
        $postfix = '';
        if ($isCopy === true) {
            $postfix = '_cp';
        } else {
            $wheres[] = '(prc_id <> ' . $this->getDetailReferenceValue() . ')';
        }
        $wheres[] = SqlHelper::generateNumericCondition('prc_rel_id', $this->getIntParameter('prc_rel_id', 0));
        $wheres[] = SqlHelper::generateNumericCondition('prc_srt_id', $this->getIntParameter('prc_srt_id', 0));
        $wheres[] = SqlHelper::generateStringCondition('prc_type', $this->getStringParameter('prc_type', ''));
        $wheres[] = SqlHelper::generateNumericCondition('prc_qt_id', $this->getIntParameter('prc_qt_id', 0));
        $wheres[] = SqlHelper::generateNumericCondition('prc_dtc_origin', $this->getIntParameter('prc_dtc_origin' . $postfix, 0));
        $wheres[] = SqlHelper::generateNumericCondition('prc_dtc_destination', $this->getIntParameter('prc_dtc_destination' . $postfix, 0));
        $wheres[] = SqlHelper::generateNumericCondition('prc_eg_id', $this->getIntParameter('prc_eg_id' . $postfix, 0));
        $wheres[] = SqlHelper::generateNumericCondition('prc_ct_id', $this->getIntParameter('prc_ct_id' . $postfix, 0));
        $wheres[] = SqlHelper::generateNumericCondition('prc_tm_id', $this->getIntParameter('prc_tm_id' . $postfix, 0));
        $wheres[] = SqlHelper::generateNumericCondition('prc_pol_id', $this->getIntParameter('prc_pol_id' . $postfix, 0));
        $wheres[] = SqlHelper::generateNumericCondition('prc_pod_id', $this->getIntParameter('prc_pod_id' . $postfix, 0));
        $wheres[] = '(prc_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT prc_id FROM price ' . $strWhere;
    }

    /**
     * Function to get query for checking unique inklaring data.
     *
     * @param bool $isCopy To trigger is it copy action or not.
     *
     * @return string
     */
    private function getQueryCheckUniqueInklaring(bool $isCopy = false): string
    {
        $wheres = [];
        $postfix = '';
        if ($isCopy === true) {
            $postfix = '_cp';
        } else {
            $wheres[] = '(prc_id <> ' . $this->getDetailReferenceValue() . ')';
        }
        $wheres[] = SqlHelper::generateNumericCondition('prc_rel_id', $this->getIntParameter('prc_rel_id', 0));
        $wheres[] = SqlHelper::generateNumericCondition('prc_srt_id', $this->getIntParameter('prc_srt_id', 0));
        $wheres[] = SqlHelper::generateStringCondition('prc_type', $this->getStringParameter('prc_type', ''));
        $wheres[] = SqlHelper::generateNumericCondition('prc_qt_id', $this->getIntParameter('prc_qt_id', 0));
        if ($this->isSrtPod() === true) {
            $wheres[] = SqlHelper::generateNumericCondition('prc_pod_id', $this->getIntParameter('prc_po_id' . $postfix, 0));
        } else {
            $wheres[] = SqlHelper::generateNumericCondition('prc_pol_id', $this->getIntParameter('prc_po_id' . $postfix, 0));
        }
        $wheres[] = SqlHelper::generateNumericCondition('prc_cct_id', $this->getIntParameter('prc_cct_id' . $postfix, 0));
        $wheres[] = SqlHelper::generateNumericCondition('prc_ct_id', $this->getIntParameter('prc_ct_id' . $postfix, 0));
        $wheres[] = '(prc_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT prc_id FROM price ' . $strWhere;
    }

    /**
     * Function to get query for checking unique warehouse data.
     *
     * @param bool $isCopy To trigger is it copy action or not.
     *
     * @return string
     */
    private function getQueryCheckUniqueWarehouse(bool $isCopy = false): string
    {
        $wheres = [];
        $postfix = '';
        if ($isCopy === true) {
            $postfix = '_cp';
        } else {
            $wheres[] = '(prc_id <> ' . $this->getDetailReferenceValue() . ')';
        }
        $wheres[] = SqlHelper::generateNumericCondition('prc_rel_id', $this->getIntParameter('prc_rel_id'));
        $wheres[] = SqlHelper::generateStringCondition('prc_type', $this->getStringParameter('prc_type'));
        $wheres[] = SqlHelper::generateNumericCondition('prc_qt_id', $this->getIntParameter('prc_qt_id'));
        $wheres[] = SqlHelper::generateNumericCondition('prc_wh_id', $this->getIntParameter('prc_wh_id' . $postfix));
        $wheres[] = '(prc_deleted_on IS NULL)';
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        return 'SELECT prc_id FROM price ' . $strWhere;
    }

    /**
     * Function to override title page
     *
     * @return void
     */
    private function overrideTitlePage(): void
    {
        $title = Trans::getFinanceWord('price');
        if ($this->getStringParameter('prc_type') === 'P') {
            $title = Trans::getFinanceWord('cogs');
        }

        $title .= ' ' . $this->getStringParameter('prc_srv_name');
        if ($this->isUpdate() === true) {
            $prcDao = new PriceDao();
            $title .= ' | ' . $this->getStringParameter('prc_code');
            $title .= ' | ' . $prcDao->getStatus($this->getAllParameters());
        }
        $this->View->setDescription($title);
    }

    /**
     * Function to get the general Field Set.
     *
     * @return FieldSet
     */
    private function getGeneralFieldSet(): FieldSet
    {
        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(4);
        $relationName = Trans::getFinanceWord('customer');
        if ($this->getStringParameter('prc_type') === 'S') {
            $quotationRoute = 'slsQt';
        } elseif ($this->getStringParameter('prc_type') === 'P') {
            $relationName = Trans::getFinanceWord('vendor');
            $quotationRoute = 'prcQt';
        } else {
            $quotationRoute = 'qt';
        }

        # Add Service Term
        $srtField = $this->Field->getSingleSelect('serviceTerm', 'prc_srt_name', $this->getStringParameter('prc_srt_name'), 'loadSingleSelectAutoComplete');
        $srtField->setHiddenField('prc_srt_id', $this->getIntParameter('prc_srt_id'));
        $srtField->addParameter('srt_srv_id', $this->getIntParameter('prc_srv_id'));
        $srtField->addOptionalParameterById('srt_container', 'prc_container');
        $srtField->setAutoCompleteFields([
            'prc_srt_container' => 'srt_container',
            'prc_srt_load' => 'srt_load',
            'prc_srt_unload' => 'srt_unload',
            'prc_srt_pol' => 'srt_pol',
            'prc_srt_pod' => 'srt_pod',
        ]);
        $srtField->setEnableNewButton(false);
        if ($this->isUpdate() === true) {
            $srtField->setReadOnly();
        }
        # Add Customer
        $customerField = $this->Field->getSingleSelect('relation', 'prc_relation', $this->getStringParameter('prc_relation'));
        $customerField->setHiddenField('prc_rel_id', $this->getIntParameter('prc_rel_id'));
        $customerField->setDetailReferenceCode('rel_id');
        $customerField->addParameter('rel_ss_id', $this->User->getSsId());
        $customerField->addClearField('prc_qt_number');
        $customerField->addClearField('prc_qt_id');
        $customerField->setReadOnly();

        # Add Quotation
        $quotationField = $this->Field->getSingleSelect($quotationRoute, 'prc_qt_number', $this->getStringParameter('prc_qt_number'), 'loadUnSubmitData');
        $quotationField->setHiddenField('prc_qt_id', $this->getIntParameter('prc_qt_id'));
        $quotationField->addParameter('qt_ss_id', $this->User->getSsId());
        $quotationField->addParameterById('qt_rel_id', 'prc_rel_id', Trans::getFinanceWord('relation'));
        $quotationField->setEnableNewButton(false);
        $quotationField->setReadOnly();


        # Add Field
        $fieldSet->addField(Trans::getFinanceWord('quotation'), $quotationField, true);
        $fieldSet->addField($relationName, $customerField, true);
        $fieldSet->addField(Trans::getFinanceWord('serviceTerm'), $srtField, true);
        $fieldSet->addHiddenField($this->Field->getHidden('prc_type', $this->getStringParameter('prc_type')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_srv_id', $this->getIntParameter('prc_srv_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_srv_code', $this->getStringParameter('prc_srv_code')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_srt_container', $this->getStringParameter('prc_srt_container')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_srt_load', $this->getStringParameter('prc_srt_load')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_srt_unload', $this->getStringParameter('prc_srt_unload')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_srt_pol', $this->getStringParameter('prc_srt_pol')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_srt_pod', $this->getStringParameter('prc_srt_pod')));

        return $fieldSet;
    }


    /**
     * Function to generate trucking portlet.
     *
     * @return Portlet
     */
    protected function getTruckingPortlet(): Portlet
    {
        # Instantiate FieldSet Object
        $fieldSet = $this->getGeneralFieldSet();
        # Add Trucking Type
        $egField = $this->Field->getSingleSelect('eg', 'prc_eg_name', $this->getStringParameter('prc_eg_name'));
        $egField->setHiddenField('prc_eg_id', $this->getIntParameter('prc_eg_id'));
        $egField->addParameterById('eq_srt_id', 'prc_srt_id', Trans::getFinanceWord('serviceTerm'));
        $egField->addOptionalParameterById('eg_container', 'prc_srt_container');
        $egField->setAutoCompleteFields([
            'prc_eg_code' => 'eg_code',
        ]);
        $egField->setEnableNewButton(false);
        # Container Type
        $conTypeField = $this->Field->getSingleSelect('container', 'prc_container_type', $this->getStringParameter('prc_container_type'));
        $conTypeField->setHiddenField('prc_ct_id', $this->getIntParameter('prc_ct_id'));
        $conTypeField->setEnableNewButton(false);
        $conTypeField->setAutoCompleteFields([
            'prc_ct_code' => 'ct_code'
        ]);

        $leadTimeTruck = $this->Field->getNumber('prc_lead_time', $this->getFloatParameter('prc_lead_time'));

        # add Fieldset
        $fieldSet->addField(Trans::getFinanceWord('transportType'), $egField, true);
        $fieldSet->addField(Trans::getFinanceWord('containerType'), $conTypeField);
        $fieldSet->addField(Trans::getFinanceWord('leadTime') . ' (' . Trans::getFinanceWord('days') . ')', $leadTimeTruck, true);
        $fieldSet->addHiddenField($this->Field->getHidden('prc_eg_code', $this->getStringParameter('prc_eg_code')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_ct_code', $this->getStringParameter('prc_ct_code')));
        # Instantiate Portlet Object
        $portlet = new Portlet('GnrTruckPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(12, 12, 12);
        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function to generate trucking portlet.
     *
     * @return Portlet
     */
    protected function getTruckingDetailPortlet(): Portlet
    {
        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        # Add Trucking Type
        # district Origin
        $setOrigin = $this->Field->getSingleSelect('district', 'prc_origin_district', $this->getStringParameter('prc_origin_district'), 'loadSingleSelectAutoComplete');
        $setOrigin->setHiddenField('prc_dtc_origin', $this->getIntParameter('prc_dtc_origin'));
        $setOrigin->setEnableNewButton(false);
        $setOrigin->setAutoCompleteFields([
            'prc_dtc_or_code' => 'dtc_dtcc_code',
            'prc_or_dtcc_code' => 'dtc_dtcc_code',
            'prc_or_dtcc_id' => 'dtc_dtcc_id',
            'prc_origin_city' => 'dtc_city',
            'prc_origin_state' => 'dtc_state',
        ]);

        # district Destination
        $setDestination = $this->Field->getSingleSelect('district', 'prc_destination_district', $this->getStringParameter('prc_destination_district'), 'loadSingleSelectAutoComplete');
        $setDestination->setHiddenField('prc_dtc_destination', $this->getIntParameter('prc_dtc_destination'));
        $setDestination->setEnableNewButton(false);
        $setDestination->setAutoCompleteFields([
            'prc_dtc_des_code' => 'dtc_dtcc_code',
            'prc_des_dtcc_code' => 'dtc_dtcc_code',
            'prc_des_dtcc_id' => 'dtc_dtcc_id',
            'prc_destination_city' => 'dtc_city',
            'prc_destination_state' => 'dtc_state',
        ]);

        $equipmentCode = $this->Field->getText('prc_eg_code', $this->getStringParameter('prc_eg_code'));
        $equipmentCode->setReadOnly();
        $originCity = $this->Field->getText('prc_origin_city', $this->getStringParameter('prc_origin_city'));
        $originCity->setReadOnly();
        $originState = $this->Field->getText('prc_origin_state', $this->getStringParameter('prc_origin_state'));
        $originState->setReadOnly();
        $destinationCity = $this->Field->getText('prc_destination_city', $this->getStringParameter('prc_destination_city'));
        $destinationCity->setReadOnly();
        $destinationState = $this->Field->getText('prc_destination_state', $this->getStringParameter('prc_destination_state'));
        $destinationState->setReadOnly();

        # Set Transport Module
        $tmField = $this->Field->getSingleSelect('transportModule', 'prc_transport_module', $this->getStringParameter('prc_transport_module'), 'loadNonRoadData');
        $tmField->setHiddenField('prc_tm_id', $this->getIntParameter('prc_tm_id'));
        $tmField->setEnableNewButton(false);
        $tmField->setAutoCompleteFields([
            'prc_tm_code' => 'tm_code'
        ]);
        $tmField->addClearField('prc_pol_id');
        $tmField->addClearField('prc_pol_name');
        $tmField->addClearField('prc_pol_code');
        $tmField->addClearField('prc_pol_country');
        $tmField->addClearField('prc_pod_id');
        $tmField->addClearField('prc_pod_name');
        $tmField->addClearField('prc_pod_code');
        $tmField->addClearField('prc_pod_country');

        # Set Port origin single select table
        $polField = $this->Field->getSingleSelect('port', 'prc_pol_name', $this->getStringParameter('prc_pol_name'));
        $polField->setHiddenField('prc_pol_id', $this->getIntParameter('prc_pol_id'));
        $polField->setEnableNewButton(false);
        $polField->setAutoCompleteFields([
            'prc_pol_code' => 'po_code',
            'prc_pol_country' => 'po_country',
        ]);

        $podField = $this->Field->getSingleSelect('port', 'prc_pod_name', $this->getStringParameter('prc_pod_name'));
        $podField->setHiddenField('prc_pod_id', $this->getIntParameter('prc_pod_id'));
        $podField->setEnableNewButton(false);
        $podField->setAutoCompleteFields([
            'prc_pod_code' => 'po_code',
            'prc_pod_country' => 'po_country',
        ]);

        $polCountry = $this->Field->getText('prc_pol_country', $this->getStringParameter('prc_pol_country'));
        $polCountry->setReadOnly();
        $podCountry = $this->Field->getText('prc_pod_country', $this->getStringParameter('prc_pod_country'));
        $podCountry->setReadOnly();
        $originAddress = $this->Field->getText('prc_origin_address', $this->getStringParameter('prc_origin_address'));
        $destinationAddress = $this->Field->getText('prc_destination_address', $this->getStringParameter('prc_destination_address'));


        # add Fieldset
        if ($this->isSrtPol() === true && $this->isSrtPod() === true) {
            # Port to Port
            $polField->addParameterById('po_tm_id', 'prc_tm_id', Trans::getFinanceWord('transportModule'));
            $podField->addParameterById('po_tm_id', 'prc_tm_id', Trans::getFinanceWord('transportModule'));

            $fieldSet->addField(Trans::getFinanceWord('transportModule'), $tmField, true);
            $fieldSet->addField(Trans::getFinanceWord('pol'), $polField, true);
            $fieldSet->addField(Trans::getFinanceWord('polCountry'), $polCountry);
            $fieldSet->addField(Trans::getFinanceWord('pod'), $podField, true);
            $fieldSet->addField(Trans::getFinanceWord('podCountry'), $podCountry);
        } elseif ($this->isSrtPol() === true && $this->isSrtUnload() === true) {
            # Port to door
            $fieldSet->addField(Trans::getFinanceWord('pol'), $polField, true);
            $fieldSet->addField(Trans::getFinanceWord('polCountry'), $polCountry);
            $fieldSet->addField(Trans::getFinanceWord('destinationAddress'), $destinationAddress);
            $fieldSet->addField(Trans::getFinanceWord('destinationDistrict'), $setDestination, true);
            $fieldSet->addField(Trans::getFinanceWord('destinationCode'), $this->Field->getText('prc_dtc_des_code', $this->getStringParameter('prc_dtc_des_code')), true);
            $fieldSet->addField(Trans::getFinanceWord('destinationCity'), $destinationCity);
        } elseif ($this->isSrtLoad() === true && $this->isSrtPod() === true) {
            $fieldSet->addField(Trans::getFinanceWord('originAddress'), $originAddress);
            $fieldSet->addField(Trans::getFinanceWord('originDistrict'), $setOrigin, true);
            $fieldSet->addField(Trans::getFinanceWord('originCode'), $this->Field->getText('prc_dtc_or_code', $this->getStringParameter('prc_dtc_or_code')), true);
            $fieldSet->addField(Trans::getFinanceWord('pod'), $podField, true);
            $fieldSet->addField(Trans::getFinanceWord('originCity'), $originCity);
            $fieldSet->addField(Trans::getFinanceWord('podCountry'), $podCountry);
        } else {
            $fieldSet->addField(Trans::getFinanceWord('originAddress'), $originAddress);
            $fieldSet->addField(Trans::getFinanceWord('originDistrict'), $setOrigin, true);
            $fieldSet->addField(Trans::getFinanceWord('destinationAddress'), $destinationAddress);
            $fieldSet->addField(Trans::getFinanceWord('destinationDistrict'), $setDestination, true);
            $fieldSet->addField(Trans::getFinanceWord('originCode'), $this->Field->getText('prc_dtc_or_code', $this->getStringParameter('prc_dtc_or_code')), true);
            $fieldSet->addField(Trans::getFinanceWord('originCity'), $originCity);
            $fieldSet->addField(Trans::getFinanceWord('destinationCode'), $this->Field->getText('prc_dtc_des_code', $this->getStringParameter('prc_dtc_des_code')), true);
            $fieldSet->addField(Trans::getFinanceWord('destinationCity'), $destinationCity);
        }
        $fieldSet->addHiddenField($this->Field->getHidden('prc_tm_code', $this->getStringParameter('prc_tm_code')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_pol_code', $this->getStringParameter('prc_pol_code')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_pod_code', $this->getStringParameter('prc_pod_code')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_or_dtcc_code', $this->getStringParameter('prc_or_dtcc_code')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_or_dtcc_id', $this->getIntParameter('prc_or_dtcc_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_des_dtcc_id', $this->getIntParameter('prc_des_dtcc_id')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_des_dtcc_code', $this->getStringParameter('prc_des_dtcc_code')));
        # Instantiate Portlet Object
        $portlet = new Portlet('GnrDlDtlPtl', Trans::getFinanceWord('details'));
        $portlet->setGridDimension(12, 12, 12);
        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function for Inklaring Portlet
     *
     * @return Portlet
     */
    protected function getInklaringPortlet(): Portlet
    {

        # Instantiate FieldSet Object
        $fieldSet = $this->getGeneralFieldSet();

        # Set Transport Module
        $tmField = $this->Field->getSingleSelect('transportModule', 'prc_transport_module', $this->getStringParameter('prc_transport_module'));
        $tmField->setHiddenField('prc_tm_id', $this->getIntParameter('prc_tm_id'));
        $tmField->setEnableNewButton(false);
        $tmField->addClearField('prc_po_id');
        $tmField->addClearField('prc_port');

        # Set Container Type
        $containerTypeField = $this->Field->getSingleSelect('container', 'prc_container_type', $this->getStringParameter('prc_container_type'));
        $containerTypeField->setHiddenField('prc_ct_id', $this->getIntParameter('prc_ct_id'));
        $containerTypeField->setEnableNewButton(false);
        $containerTypeField->setAutoCompleteFields([
            'prc_ct_code' => 'ct_code',
        ]);

        # Set Port origin single select table
        $orPortField = $this->Field->getSingleSelect('port', 'prc_port', $this->getStringParameter('prc_port'), 'loadSingleSelectAutoComplete');
        $orPortField->setHiddenField('prc_po_id', $this->getIntParameter('prc_po_id'));
        $orPortField->addParameterById('po_tm_id', 'prc_tm_id', Trans::getFinanceWord('transportModule'));
        $orPortField->setEnableNewButton(false);
        $orPortField->setAutoCompleteFields([
            'prc_po_country' => 'po_country',
            'prc_po_code' => 'po_code',
        ]);

        # Set Customs Clear Type
        $customClearType = $this->Field->getSingleSelect('customsClearanceType', 'prc_custom_clearance_type', $this->getStringParameter('prc_custom_clearance_type'));
        $customClearType->setHiddenField('prc_cct_id', $this->getIntParameter('prc_cct_id'));
        $customClearType->setEnableNewButton(false);
        $customClearType->setAutoCompleteFields([
            'prc_cct_code' => 'cct_code',
        ]);

        $portCountry = $this->Field->getText('prc_po_country', $this->getStringParameter('prc_po_country'));
        $portCountry->setReadOnly();

        # Add FieldSet
        $fieldSet->addField(Trans::getFinanceWord('transportModule'), $tmField, true);
        $fieldSet->addField(Trans::getFinanceWord('port'), $orPortField, true);
        $fieldSet->addField(Trans::getFinanceWord('containerType'), $containerTypeField);
        $fieldSet->addField(Trans::getFinanceWord('lineStatus'), $customClearType);
        $fieldSet->addField(Trans::getFinanceWord('countryPort'), $portCountry, true);
        $fieldSet->addField(Trans::getFinanceWord('leadTime') . ' (' . Trans::getFinanceWord('days') . ')', $this->Field->getNumber('prc_lead_time', $this->getFloatParameter('prc_lead_time')), true);
        $fieldSet->addHiddenField($this->Field->getHidden('prc_po_code', $this->getStringParameter('prc_po_code')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_ct_code', $this->getStringParameter('prc_ct_code')));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_cct_code', $this->getStringParameter('prc_cct_code')));
        # Instantiate Portlet Object
        $portlet = new Portlet('GnrIncPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(12, 12, 12);
        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function warehouse portlet service
     *
     * @return Portlet
     */
    protected function getWarehousePortlet(): Portlet
    {
        # Instantiate FieldSet Object
        $fieldSet = $this->getGeneralFieldSet();
        $fieldSet->removeField('prc_srt_name');

        # Warehouse
        $whField = $this->Field->getSingleSelect('warehouse', 'prc_warehouse', $this->getStringParameter('prc_warehouse'), 'loadSingleSelectPrice');
        $whField->setHiddenField('prc_wh_id', $this->getIntParameter('prc_wh_id'));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableNewButton(false);

        # Add FieldSet
        $fieldSet->addField(Trans::getFinanceWord('warehouse'), $whField, true);
        # Create Portlet Warehouse
        $portlet = new Portlet('GnrWhPtl', $this->getDefaultPortletTitle());
        $portlet->setTitle($this->getStringParameter('prc_srv_name'));
        $portlet->setGridDimension(12, 12, 12);
        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }

    /**
     * Function Price Detail FieldSet
     *
     * @return Portlet
     */
    protected function getPriceDetailFieldSet(): Portlet
    {
        # Create Object portlet.
        $portlet = new Portlet('PrcDetPtl', Trans::getFinanceWord('priceDetail'));
        # Create a table.
        $priceTable = new Table('PrcDtTbl');
        $priceTable->setHeaderRow([
            'prd_description' => Trans::getFinanceWord('description'),
            'prd_quantity' => Trans::getFinanceWord('quantity'),
            'prd_rate' => Trans::getFinanceWord('rate'),
            'prd_minimum_rate' => Trans::getFinanceWord('minRate'),
            'prd_exchange_rate' => Trans::getFinanceWord('exchangeRate'),
            'prd_tax' => Trans::getFinanceWord('tax'),
            'prd_total' => Trans::getFinanceWord('subTotal'),
            'prd_remark' => Trans::getFinanceWord('remark'),
        ]);
        $data = PriceDetailDao::getByPriceId($this->getDetailReferenceValue());
        $rows = [];
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $row['prd_description'] = $row['prd_cost_code'] . ' - ' . $row['prd_description'];
            $row['prd_quantity'] = $number->doFormatFloat($row['prd_quantity']) . ' ' . $row['prd_unit'];
            $row['prd_rate'] = $row['prd_currency'] . ' ' . $number->doFormatFloat($row['prd_rate']);
            if (empty($row['prd_minimum_rate']) === false) {
                $row['prd_minimum_rate'] = $row['prd_currency'] . ' ' . $number->doFormatFloat($row['prd_minimum_rate']);
            }
            $total = $row['prd_currency'] . ' ' . $number->doFormatFloat($row['prd_total']);
            if (empty($row['prd_exchange_rate']) === false) {
                $total = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['prd_total']);
                $row['prd_exchange_rate'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['prd_exchange_rate']);
            }
            $row['prd_total'] = $total;
            $rows[] = $row;
        }
        $priceTable->addRows($rows);
        # Set table
        $priceTable->addColumnAttribute('prd_rate', 'style', 'text-align: right');
        $priceTable->addColumnAttribute('prd_minimum_rate', 'style', 'text-align: right');
        $priceTable->addColumnAttribute('prd_exchange_rate', 'style', 'text-align: right');
        $priceTable->addColumnAttribute('prd_total', 'style', 'text-align: right');
        $priceTable->addColumnAttribute('prd_tax', 'style', 'text-align: center');
        # Add Modal for form Add.
        $modal = $this->getPriceDetailModal();
        $this->View->addModal($modal);
        if (($this->isValidParameter('prc_deleted_on') === false) && ($this->isValidParameter('prc_qt_approve_on') === false)) {
            $priceTable->setUpdateActionByModal($modal, 'prd', 'getPriceDetailById', ['prd_id']);
        }
        if ($this->isAllowUpdate() === true) {
            # Add Modal for form delete.
            $modalDelete = $this->getPriceDetailDeleteModal();
            $this->View->addModal($modalDelete);
            $priceTable->setDeleteActionByModal($modalDelete, 'prd', 'getPriceDetailByIdForDelete', ['prd_id']);

            # Add new button
            $btnAddDetail = new ModalButton('btnAddDetails', Trans::getFinanceWord('addPrice'), $modal->getModalId());
            $btnAddDetail->btnPrimary();
            $btnAddDetail->pullRight();
            $btnAddDetail->setIcon(Icon::Plus);
            $portlet->addButton($btnAddDetail);
        }
        # Add table to portlet.
        $portlet->addTable($priceTable);

        return $portlet;
    }

    /**
     * Default button approval condition
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isValidParameter('prc_qt_id') === true) {
            $route = '/slsQt/detail';
            if ($this->getStringParameter('prc_type') === 'P') {
                $route = '/prcQt/detail';
            }
            $route .= '?qt_id=' . $this->getIntParameter('prc_qt_id');
            $btnQt = new HyperLink('btnQtPrc', $this->getStringParameter('prc_qt_number', Trans::getFinanceWord('quotation')), url($route));
            $btnQt->viewAsButton();
            $btnQt->setIcon(Icon::ExternalLink)->btnPrimary()->pullRight();
            $this->View->addButton($btnQt);
        }
        if ($this->isUpdate() === true) {
            if ($this->isAllowUpdate() === true) {
                $this->setEnableDeleteButton();
                $cpModal = $this->getCopyModal();
                $this->View->addModal($cpModal);
                $btnCopy = new ModalButton('btnCpPrc', Trans::getFinanceWord('copy'), $cpModal->getModalId());
                $btnCopy->setIcon(Icon::Copy)->btnDark()->pullRight();
                $this->View->addButton($btnCopy);
            } else {
                $this->setDisableUpdate();
                $this->setEnableDeleteButton(false);
            }
        }
        parent::loadDefaultButton();
    }

    /**
     * Function check if data is deleted or not.
     *
     * @return bool
     */
    private function isDeleted(): bool
    {
        return $this->isValidParameter('prc_deleted_on');
    }

    /**
     * Function check if data is approved or not.
     *
     * @return bool
     */
    private function isAllowUpdate(): bool
    {
        return ($this->isValidParameter('prc_deleted_on') === false)
            && ($this->isValidParameter('prc_qt_approve_on') === false)
            && ($this->isValidParameter('prc_qt_qts_id') === false || ($this->isValidParameter('prc_qt_qts_id') === true && $this->isValidParameter('prc_qt_qts_deleted_on') === true));
    }

    /**
     * Function check if data is submitted or not.
     *
     * @return bool
     */
    private function isSubmitted(): bool
    {
        return ($this->isValidParameter('prc_deleted_on') === false)
            && ($this->isValidParameter('prc_qt_approve_on') === false)
            && ($this->isValidParameter('prc_qt_qts_id') === true && $this->isValidParameter('prc_qt_qts_deleted_on') === false);
    }

    /**
     * Function get Price Detail modal for delete by prd_id
     *
     * @return Modal
     */
    private function getPriceDetailDeleteModal(): Modal
    {
        # Create Modal for Price Detail Delete
        $modal = new Modal('UpDGdMdl', Trans::getFinanceWord('deleteConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeletePriceDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doDeletePriceDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }

        # Delete Confirmation
        $modal->setTitle(Trans::getFinanceWord('deleteConfirmation'));

        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->setBtnOkName(Trans::getFinanceWord('yesDelete'));

        $modal->addFieldSet($this->getPriceDetailDeleteFieldSet($showModal));
        return $modal;
    }

    /**
     * Function to get Delete data
     *
     * @param $showModal
     *
     * @return FieldSet
     */
    private function getPriceDetailDeleteFieldSet($showModal): FieldSet
    {
        # Custom Field Set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Add Fieldset
        $fieldSet->addField(Trans::getFinanceWord('costCode'), $this->Field->getText('prd_cost_code_del', $this->getParameterForModal('prd_cost_code_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('uom'), $this->Field->getText('prd_unit_del', $this->getParameterForModal('prd_unit_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('description'), $this->Field->getText('prd_description_del', $this->getParameterForModal('prd_description_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('quantity'), $this->Field->getNumber('prd_quantity_del', $this->getParameterForModal('prd_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('rate'), $this->Field->getNumber('prd_rate_del', $this->getParameterForModal('prd_rate_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('minRate'), $this->Field->getNumber('prd_minimum_rate_del', $this->getParameterForModal('prd_minimum_rate_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('tax'), $this->Field->getText('prd_tax_del', $this->getParameterForModal('prc_tax_del', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('remark'), $this->Field->getTextArea('prd_remark_del', $this->getParameterForModal('prd_remark_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('prd_id_del', $this->getParameterForModal('prd_id_del', $showModal)));

        return $fieldSet;
    }

    /**
     * Function Get Price Detail modal for Update
     *
     * @return Modal
     */
    private function getPriceDetailModal(): Modal
    {
        $modal = new Modal('PrcGnMdl', Trans::getFinanceWord('priceDetail'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdatePriceDetail');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdatePriceDetail' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $modal->addFieldSet($this->getPriceDetailModalField($showModal));

        # Create Custom Field
        return $modal;
    }

    /**
     * Function to get Price Detail data modal
     *
     * @param $showModal
     *
     * @return FieldSet
     */
    private function getPriceDetailModalField($showModal): FieldSet
    {
        # Instantiate FieldSet
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Cost Code Field
        $ccField = $this->Field->getSingleSelect('costCode', 'prd_cost_code', $this->getParameterForModal('prd_cost_code', $showModal));
        $ccField->setHiddenField('prd_cc_id', $this->getParameterForModal('prd_cc_id', $showModal));
        $ccField->setEnableNewButton(false);
        $ccField->addParameterById('ccg_srv_id', 'prc_srv_id', Trans::getFinanceWord('service'));
        $ccField->addParameterById('ccg_type', 'prc_type', Trans::getFinanceWord('type'));
        $ccField->addParameter('cc_ss_id', $this->User->getSsId());
        $ccField->setAutoCompleteFields([
            'prd_description' => 'cc_name',
        ]);

        # Uom Field
        $uomField = $this->Field->getSingleSelect('unit', 'prd_unit', $this->getParameterForModal('prd_unit', $showModal));
        $uomField->setHiddenField('prd_uom_id', $this->getParameterForModal('prd_uom_id', $showModal));
        $uomField->setEnableNewButton(false);

        # Tax Field
        $taxField = $this->Field->getSingleSelect('tax', 'prd_tax', $this->getParameterForModal('prd_tax', $showModal));
        $taxField->setHiddenField('prd_tax_id', $this->getParameterForModal('prd_tax_id', $showModal));
        $taxField->addParameter('tax_ss_id', $this->User->getSsId());
        $taxField->setEnableNewButton(false);
        $taxField->setAutoCompleteFields([
            'prd_tax_percent' => 'tax_percent',
        ]);

        $qtyField = $this->Field->getNumber('prd_quantity', $this->getParameterForModal('prd_quantity', $showModal));
        $rateField = $this->Field->getNumber('prd_rate', $this->getParameterForModal('prd_rate', $showModal));
        $minRateField = $this->Field->getNumber('prd_minimum_rate', $this->getParameterForModal('prd_minimum_rate', $showModal));
        if ($this->isSubmitted() === true) {
            $qtyField->setReadOnly();
            $rateField->setReadOnly();
            $minRateField->setReadOnly();
        }
        $curField = $this->Field->getSingleSelect('currency', 'prd_currency', $this->getParameterForModal('prd_currency', $showModal));
        $curField->setHiddenField('prd_cur_id', $this->getParameterForModal('prd_cur_id', $showModal));
        $curField->setEnableDetailButton(false);
        $curField->setEnableNewButton(false);

        # Add field into fieldSet
        $fieldSet->addField(Trans::getFinanceWord('costCode'), $ccField, true);
        $fieldSet->addField(Trans::getFinanceWord('uom'), $uomField, true);
        $fieldSet->addField(Trans::getFinanceWord('description'), $this->Field->getText('prd_description', $this->getParameterForModal('prd_description', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('quantity'), $qtyField, true);
        $fieldSet->addField(Trans::getFinanceWord('rate'), $rateField, true);
        $fieldSet->addField(Trans::getFinanceWord('minRate'), $minRateField);
        $fieldSet->addField(Trans::getFinanceWord('currency'), $curField, true);
        $fieldSet->addField(Trans::getFinanceWord('tax'), $taxField);
        $fieldSet->addField(Trans::getFinanceWord('exchangeRate'), $this->Field->getNumber('prd_exchange_rate', $this->getParameterForModal('prd_exchange_rate', $showModal)));
        $fieldSet->addField(Trans::getFinanceWord('remark'), $this->Field->getTextArea('prd_remark', $this->getParameterForModal('prd_remark', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('prd_id', $this->getParameterForModal('prd_id', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('prd_tax_percent', $this->getParameterForModal('prd_tax_percent', $showModal)));

        return $fieldSet;
    }

    /**
     * Function to set parameter for service data.
     *
     * @param array $service To store the service data
     *
     * @return void
     */
    protected function setService(array $service): void
    {
        if (empty($service) === false) {
            $this->setParameter('prc_srv_id', $service['srv_id']);
            $this->setParameter('prc_srv_code', $service['srv_code']);
            $this->setParameter('prc_srv_name', $service['srv_name']);

        }
    }

    /**
     * Function create copy modal
     *
     * @return Modal
     */
    protected function getCopyModal(): Modal
    {
        $modal = new Modal('PrcCopyMdl', Trans::getFinanceWord('copyPrice'));
        $modal->setFormSubmit($this->getMainFormId(), 'doCopy');
        if ($this->getFormAction() === 'doCopy' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
        } else {
            $this->setCopyParameter();
        }
        # Create Custom Field
        return $modal;
    }


    /**
     * Function load field set for copy price
     *
     * @param bool $showModal To trigger if modal active or not.
     *
     * @return FieldSet
     */
    protected function getCopyTruckingFieldSet(bool $showModal): FieldSet
    {
        # Instantiate FieldSet
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create Custom field
        # Add Trucking Type
        $egField = $this->Field->getSingleSelect('eg', 'prc_eg_name_cp', $this->getParameterForModal('prc_eg_name_cp', $showModal), 'loadSingleSelectAutoComplete');
        $egField->setHiddenField('prc_eg_id_cp', $this->getParameterForModal('prc_eg_id_cp', $showModal));
        $egField->addParameterById('eq_srt_id', 'prc_srt_id', Trans::getFinanceWord('serviceTerm'));
        $egField->addOptionalParameterById('eg_container', 'prc_srt_container');
        $egField->setAutoCompleteFields([
            'prc_eg_code_cp' => 'eg_code',
        ]);
        $egField->setEnableNewButton(false);
        # Container Type
        $conTypeField = $this->Field->getSingleSelect('container', 'prc_container_type_cp', $this->getParameterForModal('prc_container_type_cp', $showModal));
        $conTypeField->setHiddenField('prc_ct_id_cp', $this->getParameterForModal('prc_ct_id_cp', $showModal));
        $conTypeField->setEnableNewButton(false);
        $conTypeField->setAutoCompleteFields([
            'prc_ct_code_cp' => 'ct_code'
        ]);


        # Set Transport Module
        $tmField = $this->Field->getSingleSelect('transportModule', 'prc_transport_module_cp', $this->getParameterForModal('prc_transport_module_cp', $showModal), 'loadNonRoadData');
        $tmField->setHiddenField('prc_tm_id_cp', $this->getParameterForModal('prc_tm_id_cp', $showModal));
        $tmField->setEnableNewButton(false);
        $tmField->setAutoCompleteFields([
            'prc_tm_code_cp' => 'tm_code'
        ]);
        $tmField->addClearField('prc_pol_id_cp');
        $tmField->addClearField('prc_pol_name_cp');
        $tmField->addClearField('prc_pol_code_cp');
        $tmField->addClearField('prc_pod_id_cp');
        $tmField->addClearField('prc_pod_name_cp');
        $tmField->addClearField('prc_pod_code_cp');

        # Set Port origin single select table
        $polField = $this->Field->getSingleSelect('port', 'prc_pol_name_cp', $this->getParameterForModal('prc_pol_name_cp', $showModal));
        $polField->setHiddenField('prc_pol_id_cp', $this->getParameterForModal('prc_pol_id_cp', $showModal));
        $polField->setEnableNewButton(false);
        $polField->setAutoCompleteFields([
            'prc_pol_code_cp' => 'po_code',
        ]);

        $podField = $this->Field->getSingleSelect('port', 'prc_pod_name_cp', $this->getParameterForModal('prc_pod_name_cp', $showModal));
        $podField->setHiddenField('prc_pod_id_cp', $this->getParameterForModal('prc_pod_id_cp', $showModal));
        $podField->setEnableNewButton(false);
        $podField->setAutoCompleteFields([
            'prc_pod_code_cp' => 'po_code',
        ]);

        # district Origin
        $setOrigin = $this->Field->getSingleSelect('district', 'prc_origin_district_cp', $this->getParameterForModal('prc_origin_district_cp', $showModal), 'loadSingleSelectAutoComplete');
        $setOrigin->setHiddenField('prc_dtc_origin_cp', $this->getParameterForModal('prc_dtc_origin_cp', $showModal));
        $setOrigin->setEnableNewButton(false);
        $setOrigin->setAutoCompleteFields([
            'prc_dtc_or_code_cp' => 'dtc_dtcc_code',
            'prc_or_dtcc_code_cp' => 'dtc_dtcc_code',
            'prc_or_dtcc_id_cp' => 'dtc_dtcc_id',
        ]);

        # district Destination
        $setDestination = $this->Field->getSingleSelect('district', 'prc_destination_district_cp', $this->getParameterForModal('prc_destination_district_cp', $showModal), 'loadSingleSelectAutoComplete');
        $setDestination->setHiddenField('prc_dtc_destination_cp', $this->getParameterForModal('prc_dtc_destination_cp', $showModal));
        $setDestination->setEnableNewButton(false);
        $setDestination->setAutoCompleteFields([
            'prc_dtc_des_code_cp' => 'dtc_dtcc_code',
            'prc_des_dtcc_code_cp' => 'dtc_dtcc_code',
            'prc_des_dtcc_id_cp' => 'dtc_dtcc_id',
        ]);

        # Lead Time
        $leadTimeTruck = $this->Field->getNumber('prc_lead_time_cp', $this->getParameterForModal('prc_lead_time_cp', $showModal));
        $originAddress = $this->Field->getText('prc_origin_address_cp', $this->getParameterForModal('prc_origin_address_cp', $showModal));
        $destinationAddress = $this->Field->getText('prc_destination_address_cp', $this->getParameterForModal('prc_destination_address_cp', $showModal));

        $fieldSet->addField(Trans::getFinanceWord('truckType'), $egField, true);
        $fieldSet->addField(Trans::getFinanceWord('containerType'), $conTypeField, $this->isSrtContainer());
        if ($this->isSrtPol() === true && $this->isSrtPod() === true) {
            # Port to Port
            $polField->addParameterById('po_tm_id', 'prc_tm_id', Trans::getFinanceWord('transportModule'));
            $podField->addParameterById('po_tm_id', 'prc_tm_id', Trans::getFinanceWord('transportModule'));

            $fieldSet->addField(Trans::getFinanceWord('transportModule'), $tmField, true);
            $fieldSet->addField(Trans::getFinanceWord('pol'), $polField, true);
            $fieldSet->addField(Trans::getFinanceWord('pod'), $podField, true);
            $fieldSet->addField(Trans::getFinanceWord('leadTime') . ' (' . Trans::getFinanceWord('days') . ')', $leadTimeTruck, true);
        } elseif ($this->isSrtPol() === true && $this->isSrtUnload() === true) {
            # Port to door
            $fieldSet->addField(Trans::getFinanceWord('pol'), $polField, true);
            $fieldSet->addField(Trans::getFinanceWord('destinationDistrict'), $setDestination, true);
            $fieldSet->addField(Trans::getFinanceWord('destinationAddress'), $destinationAddress);
            $fieldSet->addField(Trans::getFinanceWord('destinationCode'), $this->Field->getText('prc_dtc_des_code_cp', $this->getParameterForModal('prc_dtc_des_code_cp', $showModal)), true);
            $fieldSet->addField(Trans::getFinanceWord('leadTime') . ' (' . Trans::getFinanceWord('days') . ')', $leadTimeTruck, true);
        } elseif ($this->isSrtLoad() === true && $this->isSrtPod() === true) {
            $fieldSet->addField(Trans::getFinanceWord('originAddress'), $originAddress);
            $fieldSet->addField(Trans::getFinanceWord('originDistrict'), $setOrigin, true);
            $fieldSet->addField(Trans::getFinanceWord('originCode'), $this->Field->getText('prc_dtc_or_code_cp', $this->getParameterForModal('prc_dtc_or_code_cp', $showModal)), true);
            $fieldSet->addField(Trans::getFinanceWord('pod'), $podField, true);
            $fieldSet->addField(Trans::getFinanceWord('leadTime') . ' (' . Trans::getFinanceWord('days') . ')', $leadTimeTruck, true);
        } else {
            $fieldSet->addField(Trans::getFinanceWord('originAddress'), $originAddress);
            $fieldSet->addField(Trans::getFinanceWord('destinationAddress'), $destinationAddress);
            $fieldSet->addField(Trans::getFinanceWord('originDistrict'), $setOrigin, true);
            $fieldSet->addField(Trans::getFinanceWord('destinationDistrict'), $setDestination, true);
            $fieldSet->addField(Trans::getFinanceWord('originCode'), $this->Field->getText('prc_dtc_or_code_cp', $this->getParameterForModal('prc_dtc_or_code_cp', $showModal)), true);
            $fieldSet->addField(Trans::getFinanceWord('destinationCode'), $this->Field->getText('prc_dtc_des_code_cp', $this->getParameterForModal('prc_dtc_des_code_cp', $showModal)), true);
            $fieldSet->addField(Trans::getFinanceWord('leadTime') . ' (' . Trans::getFinanceWord('days') . ')', $leadTimeTruck, true);
        }


        $fieldSet->addHiddenField($this->Field->getHidden('prc_eg_code_cp', $this->getParameterForModal('prc_eg_code_cp', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_ct_code_cp', $this->getParameterForModal('prc_ct_code_cp', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_tm_code_cp', $this->getParameterForModal('prc_tm_code_cp', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_pol_code_cp', $this->getParameterForModal('prc_pol_code_cp', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_pod_code_cp', $this->getParameterForModal('prc_pod_code_cp', $showModal)));

        $fieldSet->addHiddenField($this->Field->getHidden('prc_or_dtcc_code_cp', $this->getParameterForModal('prc_or_dtcc_code_cp', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_or_dtcc_id_cp', $this->getParameterForModal('prc_or_dtcc_id_cp', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_des_dtcc_id_cp', $this->getParameterForModal('prc_des_dtcc_id_cp', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_des_dtcc_code_cp', $this->getParameterForModal('prc_des_dtcc_code_cp', $showModal)));

        return $fieldSet;
    }

    /**
     * Function load field set for copy price
     *
     * @param bool $showModal To trigger if modal active or not.
     *
     * @return FieldSet
     */
    protected function getCopyInklaringFieldSet(bool $showModal): FieldSet
    {
        # Instantiate FieldSet
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Create Custom field
        # Set Transport Module
        $tmField = $this->Field->getSingleSelect('transportModule', 'prc_transport_module_cp', $this->getParameterForModal('prc_transport_module_cp', $showModal));
        $tmField->setHiddenField('prc_tm_id_cp', $this->getParameterForModal('prc_tm_id_cp', $showModal));
        $tmField->setEnableNewButton(false);
        $tmField->addClearField('prc_po_id_cp');
        $tmField->addClearField('prc_port_cp');

        # Set Container Type
        $containerTypeField = $this->Field->getSingleSelect('container', 'prc_container_type_cp', $this->getParameterForModal('prc_container_type_cp', $showModal));
        $containerTypeField->setHiddenField('prc_ct_id_cp', $this->getParameterForModal('prc_ct_id_cp', $showModal));
        $containerTypeField->setEnableNewButton(false);
        $containerTypeField->setAutoCompleteFields([
            'prc_ct_code_cp' => 'ct_code',
        ]);

        # Set Port origin single select table
        $orPortField = $this->Field->getSingleSelect('port', 'prc_port_cp', $this->getParameterForModal('prc_port_cp', $showModal), 'loadSingleSelectAutoComplete');
        $orPortField->setHiddenField('prc_po_id_cp', $this->getParameterForModal('prc_po_id_cp', $showModal));
        $orPortField->addParameterById('po_tm_id', 'prc_tm_id_cp', Trans::getFinanceWord('transportModule'));
        $orPortField->setEnableNewButton(false);
        $orPortField->setAutoCompleteFields([
            'prc_po_country_cp' => 'po_country',
            'prc_po_code_cp' => 'po_code',
        ]);

        # Set Customs Clear Type
        $customClearType = $this->Field->getSingleSelect('customsClearanceType', 'prc_custom_clearance_type_cp', $this->getParameterForModal('prc_custom_clearance_type_cp', $showModal));
        $customClearType->setHiddenField('prc_cct_id_cp', $this->getParameterForModal('prc_cct_id_cp', $showModal));
        $customClearType->setEnableNewButton(false);
        $customClearType->setAutoCompleteFields([
            'prc_cct_code_cp' => 'cct_code',
        ]);

        $portCountry = $this->Field->getText('prc_po_country_cp', $this->getParameterForModal('prc_po_country_cp', $showModal));
        $portCountry->setReadOnly();

        # Add FieldSet
        $fieldSet->addField(Trans::getFinanceWord('transportModule'), $tmField, true);
        $fieldSet->addField(Trans::getFinanceWord('port'), $orPortField, true);
        $fieldSet->addField(Trans::getFinanceWord('containerType'), $containerTypeField);
        $fieldSet->addField(Trans::getFinanceWord('customClearanceType'), $customClearType, true);
        $fieldSet->addField(Trans::getFinanceWord('countryPort'), $portCountry, true);
        $fieldSet->addField(Trans::getFinanceWord('leadTime') . ' (' . Trans::getFinanceWord('days') . ')', $this->Field->getNumber('prc_lead_time_cp', $this->getParameterForModal('prc_lead_time_cp', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('prc_po_code_cp', $this->getParameterForModal('prc_po_code_cp', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_ct_code_cp', $this->getParameterForModal('prc_ct_code_cp', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('prc_cct_code_cp', $this->getParameterForModal('prc_cct_code_cp', $showModal)));

        return $fieldSet;
    }

    /**
     * Function load field set for copy warehouse price
     *
     * @param bool $showModal To trigger if modal active or not.
     *
     * @return FieldSet
     */
    protected function getCopyWarehouseFieldSet(bool $showModal): FieldSet
    {
        # Instantiate FieldSet
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        # Create Custom field
        # Warehouse
        $whField = $this->Field->getSingleSelect('warehouse', 'prc_warehouse_cp', $this->getParameterForModal('prc_warehouse_cp', $showModal), 'loadSingleSelectPrice');
        $whField->setHiddenField('prc_wh_id_cp', $this->getParameterForModal('prc_wh_id_cp', $showModal));
        $whField->addParameter('wh_ss_id', $this->User->getSsId());
        $whField->setEnableNewButton(false);

        # Add FieldSet
        $fieldSet->addField(Trans::getFinanceWord('warehouse'), $whField, true);
        return $fieldSet;
    }

    /**
     * Function to set parameter for copy trucking price
     *
     * @return void
     */
    protected function setCopyParameter(): void
    {
        $params = $this->getAllParameters();
        foreach ($params as $par => $val) {
            if (str_starts_with($par, 'prc_') === true) {
                $this->setParameter($par . '_cp', $val);
            }
        }
    }

    /**
     * Function to set port parameter for inklaring
     *
     * @return void
     */
    protected function setInklaringPortParameter(): void
    {
        if ($this->isUpdate() === true) {
            if ($this->isSrtPol() === true) {
                $this->setParameter('prc_port', $this->getStringParameter('prc_pol_name'));
                $this->setParameter('prc_po_id', $this->getIntParameter('prc_pol_id'));
                $this->setParameter('prc_po_country', $this->getStringParameter('prc_pol_country'));
                $this->setParameter('prc_po_code', $this->getStringParameter('prc_pol_code'));
            } else {
                $this->setParameter('prc_port', $this->getStringParameter('prc_pod_name'));
                $this->setParameter('prc_po_id', $this->getIntParameter('prc_pod_id'));
                $this->setParameter('prc_po_country', $this->getStringParameter('prc_pod_country'));
                $this->setParameter('prc_po_code', $this->getStringParameter('prc_pod_code'));
            }
        }
    }

    /**
     * Function to check is service term required pol
     *
     * @return bool
     */
    protected function isSrtContainer(): bool
    {
        return $this->getStringParameter('prc_srt_container', 'N') === 'Y';
    }

    /**
     * Function to check is service term required pol
     *
     * @return bool
     */
    protected function isSrtPol(): bool
    {
        return $this->getStringParameter('prc_srt_pol', 'N') === 'Y';
    }

    /**
     * Function to check is service term required pod
     *
     * @return bool
     */
    protected function isSrtPod(): bool
    {
        return $this->getStringParameter('prc_srt_pod', 'N') === 'Y';
    }

    /**
     * Function to check is service term required load
     *
     * @return bool
     */
    protected function isSrtLoad(): bool
    {
        return $this->getStringParameter('prc_srt_load', 'N') === 'Y';
    }

    /**
     * Function to check is service term required unload
     *
     * @return bool
     */
    protected function isSrtUnload(): bool
    {
        return $this->getStringParameter('prc_srt_unload', 'N') === 'Y';
    }

    /**
     * Function to check is port to Port Terms
     *
     * @return bool
     */
    protected function isPortToPort(): bool
    {
        return $this->isSrtPod() && $this->isSrtPol();
    }

    /**
     * Function to check is port to door Terms
     *
     * @return bool
     */
    protected function isPortToDoor(): bool
    {
        return $this->isSrtPol() && $this->isSrtUnload();
    }

    /**
     * Function to check is door to port Terms
     *
     * @return bool
     */
    protected function isDoorToPort(): bool
    {
        return $this->isSrtLoad() && $this->isSrtPod();
    }

    /**
     * Function to check is door to door Terms
     *
     * @return bool
     */
    protected function isDoorToDoor(): bool
    {
        return $this->isSrtLoad() && $this->isSrtUnload();
    }
}
