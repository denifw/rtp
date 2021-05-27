<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Detail\Finance\Purchase;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Finance\Purchase\JobDepositApprovalDao;
use App\Model\Dao\Finance\Purchase\JobDepositDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\Finance\Purchase\JobDepositDetailDao;
use App\Model\Dao\Finance\Purchase\PurchaseInvoiceApprovalDao;
use App\Model\Dao\Finance\Purchase\PurchaseInvoiceDao;
use App\Model\Dao\Finance\Purchase\PurchaseInvoiceDetailDao;
use App\Model\Dao\Job\JobOrderDao;
use App\Model\Dao\Job\JobPurchaseDao;
use App\Model\Dao\Job\JobSalesDao;
use App\Model\Dao\Master\Finance\TaxDetailDao;
use App\Model\Dao\Relation\OfficeDao;
use App\Model\Dao\System\Document\DocumentDao;

/**
 * Class to handle the creation of detail JobDeposit page
 *
 * @package    app
 * @subpackage Model\Detail\Finance\Purchase
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobDeposit extends AbstractFormModel
{

    /**
     * Property to store number object
     *
     * @var NumberFormatter $Number
     */
    protected $Number;
    /**
     * Property to store date time parser object
     *
     * @var DateTimeParser $DtParser
     */
    protected $DtParser;

    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'jd', 'jd_id');
        $this->setParameters($parameters);
        $this->Number = new NumberFormatter();
        $this->DtParser = new DateTimeParser();

    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $colVal = [
            'jd_ss_id' => $this->User->getSsId(),
            'jd_jo_id' => $this->getIntParameter('jd_jo_id'),
            'jd_invoice_of_id' => $this->getIntParameter('jd_invoice_of_id'),
            'jd_rel_id' => $this->getIntParameter('jd_rel_id'),
            'jd_of_id' => $this->getIntParameter('jd_of_id'),
            'jd_cp_id' => $this->getIntParameter('jd_cp_id'),
            'jd_rb_rel' => $this->getIntParameter('jd_rb_rel'),
            'jd_rel_ref' => $this->getStringParameter('jd_rel_ref'),
            'jd_cc_id' => $this->getIntParameter('jd_cc_id'),
            'jd_date' => $this->getStringParameter('jd_date'),
            'jd_return_date' => $this->getStringParameter('jd_return_date'),
            'jd_amount' => $this->getFloatParameter('jd_amount'),
        ];
        $jdDao = new JobDepositDao();
        $jdDao->doInsertTransaction($colVal);
        return $jdDao->getLastInsertId();
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
                'jd_jo_id' => $this->getIntParameter('jd_jo_id'),
                'jd_rel_id' => $this->getIntParameter('jd_rel_id'),
                'jd_of_id' => $this->getIntParameter('jd_of_id'),
                'jd_cp_id' => $this->getIntParameter('jd_cp_id'),
                'jd_rb_rel' => $this->getIntParameter('jd_rb_rel'),
                'jd_rel_ref' => $this->getStringParameter('jd_rel_ref'),
                'jd_cc_id' => $this->getIntParameter('jd_cc_id'),
                'jd_date' => $this->getStringParameter('jd_date'),
                'jd_return_date' => $this->getStringParameter('jd_return_date'),
                'jd_amount' => $this->getFloatParameter('jd_amount'),
            ];
            $jdDao = new JobDepositDao();
            $jdDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } else if ($this->getFormAction() === 'doUploadDocument') {
            $file = $this->getFileParameter('doc_file');
            if ($file !== null) {
                $colVal = [
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_dct_id' => $this->getIntParameter('doc_dct_id'),
                    'doc_group_reference' => $this->getDetailReferenceValue(),
                    'doc_type_reference' => $this->getIntParameter('jd_jo_id'),
                    'doc_file_name' => time() . '.' . $file->getClientOriginalExtension(),
                    'doc_description' => $this->getStringParameter('doc_description'),
                    'doc_file_size' => $file->getSize(),
                    'doc_file_type' => $file->getClientOriginalExtension(),
                    'doc_public' => 'Y',
                ];
                $docDao = new DocumentDao();
                $docDao->doUploadDocument($colVal, $file);
            }
        } else if ($this->getFormAction() === 'doDeleteDocument') {
            $docDao = new DocumentDao();
            $docDao->doDeleteTransaction($this->getIntParameter('doc_id_del'));
        } else if ($this->getFormAction() === 'doDelete') {
            $colVal = [
                'jd_deleted_reason' => $this->getStringParameter('base_delete_reason'),
                'jd_deleted_by' => $this->User->getId(),
                'jd_deleted_on' => date('Y-m-d H:i:s'),
            ];
            $jdDao = new JobDepositDao();
            $jdDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } else if ($this->getFormAction() === 'doRequest') {
            $jdaColVal = [
                'jda_jd_id' => $this->getDetailReferenceValue(),
            ];
            $jdaDao = new JobDepositApprovalDao();
            $jdaDao->doInsertTransaction($jdaColVal);
            $jdDao = new JobDepositDao();
            $jdDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'jd_jda_id' => $jdaDao->getLastInsertId()
            ]);
        } else if ($this->getFormAction() === 'doReject') {
            $jdaColVal = [
                'jda_reject_reason' => $this->getStringParameter('jda_reject_reason'),
                'jda_deleted_by' => $this->User->getId(),
                'jda_deleted_on' => date('Y-m-d H:i:s'),
            ];
            $jdaDao = new JobDepositApprovalDao();
            $jdaDao->doUpdateTransaction($this->getIntParameter('jda_id'), $jdaColVal);
            $jdDao = new JobDepositDao();
            $jdDao->doUpdateTransaction($this->getDetailReferenceValue(), [
                'jd_jda_id' => null
            ]);
        } else if ($this->getFormAction() === 'doApprove') {
            $sn = new SerialNumber($this->User->getSsId());
            $number = $sn->loadNumber('JobDeposit', $this->getIntParameter('jd_invoice_of_id'), $this->getIntParameter('jd_rel_id'), $this->getIntParameter('jd_jo_srv_id'), $this->getIntParameter('jd_jo_srt_id'));
            $colVal = [
                'jd_number' => $number,
                'jd_approved_by' => $this->User->getId(),
                'jd_approved_on' => date('Y-m-d H:i:s'),
            ];
            $jdDao = new JobDepositDao();
            $jdDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } else if ($this->getFormAction() === 'doPaid') {
            $colVal = [
                'jd_rb_paid' => $this->getIntParameter('jd_rb_paid'),
                'jd_pm_id' => $this->getIntParameter('jd_pm_id'),
                'jd_paid_ref' => $this->getStringParameter('jd_paid_ref'),
                'jd_paid_by' => $this->User->getId(),
                'jd_paid_on' => $this->getStringParameter('jd_pay_date') . ' ' . $this->getStringParameter('jd_pay_time') . ':00',
            ];
            $jdDao = new JobDepositDao();
            $jdDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } else if ($this->getFormAction() === 'doUpdateClaim') {
            $taxPercent = TaxDetailDao::getTotalPercentageByTaxId($this->getIntParameter('jdd_tax_id'));
            $rate = $this->getFloatParameter('jdd_rate') * $this->getFloatParameter('jdd_quantity') * $this->getFloatParameter('jdd_exchange_rate');
            $taxAmount = ($rate * $taxPercent) / 100;
            $total = $rate + $taxAmount;
            $jddColVal = [
                'jdd_jd_id' => $this->getDetailReferenceValue(),
                'jdd_cc_id' => $this->getIntParameter('jdd_cc_id'),
                'jdd_description' => $this->getStringParameter('jdd_description'),
                'jdd_quantity' => $this->getFloatParameter('jdd_quantity'),
                'jdd_rate' => $this->getFloatParameter('jdd_rate'),
                'jdd_uom_id' => $this->getIntParameter('jdd_uom_id'),
                'jdd_exchange_rate' => $this->getFloatParameter('jdd_exchange_rate'),
                'jdd_cur_id' => $this->getIntParameter('jdd_cur_id'),
                'jdd_tax_id' => $this->getIntParameter('jdd_tax_id'),
                'jdd_total' => $total,
            ];
            $jddDao = new JobDepositDetailDao();
            if ($this->isValidParameter('jdd_id')) {
                $jddDao->doUpdateTransaction($this->getIntParameter('jdd_id'), $jddColVal);
            } else {
                $jddDao->doInsertTransaction($jddColVal);
            }
        } else if ($this->getFormAction() === 'doDeleteClaim') {
            $jddDao = new JobDepositDetailDao();
            $jddDao->doDeleteTransaction($this->getIntParameter('jdd_id_del'));
        } else if ($this->getFormAction() === 'doSettlement') {
            # 1. Load Claim
            $claims = JobDepositDetailDao::getByJdId($this->getDetailReferenceValue());
            $dateTime = $this->getStringParameter('jd_settle_date') . ' ' . $this->getStringParameter('jd_settle_time') . ':00';
            if (empty($claims) === false) {
                # Insert job sales and purchase
                $jopIds = [];
                $josDao = new JobSalesDao();
                $jopDao = new JobPurchaseDao();
                foreach ($claims as $row) {
                    $josId = null;
                    if ($row['jdd_type'] === 'R') {
                        $josColVal = [
                            'jos_jo_id' => $this->getIntParameter('jd_jo_id'),
                            'jos_rel_id' => $this->getIntParameter('jd_jo_rel_id'),
                            'jos_cc_id' => $row['jdd_cc_id'],
                            'jos_description' => $row['jdd_description'],
                            'jos_quantity' => $row['jdd_quantity'],
                            'jos_rate' => $row['jdd_rate'],
                            'jos_uom_id' => $row['jdd_uom_id'],
                            'jos_exchange_rate' => $row['jdd_exchange_rate'],
                            'jos_cur_id' => $row['jdd_cur_id'],
                            'jos_tax_id' => $row['jdd_tax_id'],
                            'jos_total' => $row['jdd_total'],
                        ];
                        $josDao->doInsertTransaction($josColVal);
                        $josId = $josDao->getLastInsertId();
                    }
                    $jopColVal = [
                        'jop_jo_id' => $this->getIntParameter('jd_jo_id'),
                        'jop_rel_id' => $this->getIntParameter('jd_rel_id'),
                        'jop_cc_id' => $row['jdd_cc_id'],
                        'jop_description' => $row['jdd_description'],
                        'jop_quantity' => $row['jdd_quantity'],
                        'jop_rate' => $row['jdd_rate'],
                        'jop_uom_id' => $row['jdd_uom_id'],
                        'jop_exchange_rate' => $row['jdd_exchange_rate'],
                        'jop_cur_id' => $row['jdd_cur_id'],
                        'jop_tax_id' => $row['jdd_tax_id'],
                        'jop_total' => $row['jdd_total'],
                        'jop_jos_id' => $josId,
                    ];
                    $jopDao->doInsertTransaction($jopColVal);
                    $jopIds[] = $jopDao->getLastInsertId();
                }
                # Insert Purchase Invoice
                $piDao = new PurchaseInvoiceDao();
                $sn = new SerialNumber($this->User->getSsId());
                $number = $sn->loadNumber('PurchaseInvoice', $this->getIntParameter('jd_invoice_of_id'), $this->getIntParameter('jd_rel_id'), $this->getIntParameter('jd_jo_srv_id'));
                $piColVal = [
                    'pi_ss_id' => $this->User->getSsId(),
                    'pi_number' => $number,
                    'pi_srv_id' => $this->getIntParameter('jd_jo_srv_id'),
                    'pi_rel_id' => $this->getIntParameter('jd_rel_id'),
                    'pi_rb_id' => $this->getIntParameter('jd_rb_rel'),
                    'pi_of_id' => $this->getIntParameter('jd_invoice_of_id'),
                    'pi_rel_of_id' => $this->getIntParameter('jd_of_id'),
                    'pi_cp_id' => $this->getIntParameter('jd_cp_id'),
                    'pi_reference' => '',
                    'pi_rel_reference' => $this->getStringParameter('jd_settle_ref'),
                    'pi_date' => $this->getStringParameter('jd_settle_date'),
                    'pi_due_date' => $this->getStringParameter('jd_settle_date'),
                    'pi_approve_by' => $this->User->getId(),
                    'pi_approve_on' => $dateTime,
                    'pi_pay_date' => $this->getStringParameter('jd_settle_date'),
                    'pi_paid_ref' => $this->getStringParameter('jd_number'),
                    'pi_paid_by' => $this->User->getId(),
                    'pi_paid_on' => $dateTime,
                    'pi_paid_rb_id' => $this->getIntParameter('jd_rb_paid'),
                ];
                $piDao->doInsertTransaction($piColVal);
                # Insert Purchase Approval
                $piaColVal = [
                    'pia_pi_id' => $piDao->getLastInsertId(),
                ];
                $piaDao = new PurchaseInvoiceApprovalDao();
                $piaDao->doInsertTransaction($piaColVal);
                # Insert Purchase Detail
                $pidDao = new PurchaseInvoiceDetailDao();
                foreach ($jopIds as $id) {
                    $pidColVal = [
                        'pid_pi_id' => $piDao->getLastInsertId(),
                        'pid_jop_id' => $id,
                    ];
                    $pidDao->doInsertTransaction($pidColVal);
                }
            }
            # Update Job Deposit
            $colVal = [
                'jd_settle_ref' => $this->getStringParameter('jd_settle_ref'),
                'jd_settle_by' => $this->User->getId(),
                'jd_settle_on' => $dateTime,
            ];
            $jdDao = new JobDepositDao();
            $jdDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        } else if ($this->getFormAction() === 'doRefund') {
            $colVal = [
                'jd_rb_return' => $this->getIntParameter('jd_rb_return'),
                'jd_return_by' => $this->User->getId(),
                'jd_return_on' => $this->getStringParameter('jd_return_date') . ' ' . $this->getStringParameter('jd_return_time') . ':00',
            ];
            $jdDao = new JobDepositDao();
            $jdDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
        }
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return JobDepositDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert()) {
            if ($this->isValidParameter('jd_jo_id') === true) {
                $job = JobOrderDao::getByReferenceAndSystem($this->getIntParameter('jd_jo_id'), $this->User->getSsId());
                if (empty($job) === false) {
                    $this->setParameter('jd_jo_number', $job['jo_number']);
                    $this->setParameter('jd_jo_srv_id', $job['jo_srv_id']);
                    $this->setParameter('jd_jo_service', $job['jo_service']);
                } else {
                    $this->setParameter('jd_jo_id', '');
                }
            }
            $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        } else {
            $this->overridePageTitle();
            if ($this->isValidParameter('jd_deleted_on')) {
                $this->View->addErrorMessage(Trans::getWord('deletedData', 'message', '', [
                    'user' => $this->getStringParameter('jd_deleted_by'),
                    'time' => DateTimeParser::format($this->getStringParameter('jd_deleted_on')),
                    'reason' => $this->getStringParameter('jd_delete_reason'),
                ]));
            }
            if ($this->isValidParameter('jda_reject_reason')) {
                $this->View->addErrorMessage(Trans::getWord('rejectRequest', 'message', '', [
                    'user' => $this->getStringParameter('jda_deleted_by'),
                    'time' => DateTimeParser::format($this->getStringParameter('jda_deleted_on')),
                    'reason' => $this->getStringParameter('jda_reject_reason'),
                ]));
            }
            if ($this->isAllowUpdate()) {
                $this->Tab->addPortlet('general', $this->getGeneralPortlet());
            } else {
                $this->Tab->addContent('general', $this->getWidget());
                $this->Tab->addPortlet('general', $this->getDetailViewPortlet());
                $this->Tab->addPortlet('general', $this->getRelationViewPortlet());
                if ($this->isValidParameter('jd_paid_on') === true) {
                    $this->Tab->addPortlet('general', $this->getClaimPortlet());
                }
            }
            $this->Tab->addPortlet('document', $this->getBaseDocumentPortlet('jobdeposit', $this->getDetailReferenceValue()));
            $this->Tab->addPortlet('timeSheet', $this->getTimeSheetFieldSet());

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
            $this->Validation->checkRequire('jd_jo_id');
            $this->Validation->checkRequire('jd_invoice_of_id');
            $this->Validation->checkRequire('jd_jo_service');
            $this->Validation->checkRequire('jd_rel_id');
            $this->Validation->checkRequire('jd_rb_rel');
            $this->Validation->checkRequire('jd_of_id');
            $this->Validation->checkRequire('jd_cc_id');
            $this->Validation->checkRequire('jd_amount');
            $this->Validation->checkFloat('jd_amount');
            $this->Validation->checkRequire('jd_rel_ref');
            $this->Validation->checkRequire('jd_date');
            $this->Validation->checkDate('jd_date');
            $this->Validation->checkRequire('jd_return_date');
            $this->Validation->checkDate('jd_return_date', '', $this->getStringParameter('jd_date', date('Y-m-d')));
        } else if ($this->getFormAction() === 'doReject') {
            $this->Validation->checkRequire('jda_id');
            $this->Validation->checkRequire('jda_reject_reason', 2, 255);
        } else if ($this->getFormAction() === 'doPaid') {
            $this->Validation->checkRequire('jd_pm_id');
            $this->Validation->checkRequire('jd_rb_paid');
            $this->Validation->checkRequire('jd_pay_date');
            $this->Validation->checkDate('jd_pay_date');
            $this->Validation->checkRequire('jd_pay_time');
            $this->Validation->checkTime('jd_pay_time');
            $this->Validation->checkMaxLength('jd_paid_ref', 255);
        } else if ($this->getFormAction() === 'doUpdateClaim') {
            $this->Validation->checkRequire('jdd_cc_id');
            $this->Validation->checkRequire('jdd_uom_id');
            $this->Validation->checkRequire('jdd_description', 2, 255);
            $this->Validation->checkRequire('jdd_quantity');
            $this->Validation->checkFloat('jdd_quantity');
            $this->Validation->checkRequire('jdd_rate');
            $this->Validation->checkFloat('jdd_rate');
            $this->Validation->checkRequire('jdd_tax_id');
            $this->Validation->checkRequire('jdd_cur_id');
            $this->Validation->checkRequire('jdd_exchange_rate');
            if ($this->isValidParameter('jdd_cur_id_') === true) {
                if ($this->User->Settings->getCurrencyId() === $this->getIntParameter('jdd_cur_id')) {
                    $this->Validation->checkFloat('jdd_exchange_rate', 1.0, 1.0);
                } else {
                    $this->Validation->checkFloat('jdd_exchange_rate');
                }
            }
        } else if ($this->getFormAction() === 'doDeleteClaim') {
            $this->Validation->checkRequire('jdd_id_del');
        } else if ($this->getFormAction() === 'doSettlement') {
            $this->Validation->checkRequire('jd_rel_id');
            $this->Validation->checkRequire('jd_jo_id');
            $this->Validation->checkRequire('jd_jo_rel_id');
            $this->Validation->checkRequire('jd_jo_srv_id');
            $this->Validation->checkRequire('jd_invoice_of_id');
            $this->Validation->checkRequire('jd_rb_rel');
            $this->Validation->checkRequire('jd_of_id');
            $this->Validation->checkRequire('jd_rb_paid');
            $this->Validation->checkRequire('jd_number');
            $this->Validation->checkRequire('jd_settle_date');
            $this->Validation->checkDate('jd_settle_date');
            $this->Validation->checkRequire('jd_settle_time');
            $this->Validation->checkTime('jd_settle_time');
            $this->Validation->checkMaxLength('jd_settle_ref', 255);
        } else if ($this->getFormAction() === 'doRefund') {
            $this->Validation->checkRequire('jd_rb_return');
            $this->Validation->checkRequire('jd_return_date');
            $this->Validation->checkDate('jd_return_date');
            $this->Validation->checkRequire('jd_return_time');
            $this->Validation->checkTime('jd_return_time');
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
        # Create Fields.
        # JOb Field
        $jobField = $this->Field->getSingleSelectTable('jobOrder', 'jd_jo_number', $this->getStringParameter('jd_jo_number'), 'loadTableSelectData');
        $jobField->setHiddenField('jd_jo_id', $this->getIntParameter('jd_jo_id'));
        $jobField->setTableColumns([
            'jo_number' => Trans::getFinanceWord('joNumber'),
            'jo_customer' => Trans::getFinanceWord('customer'),
            'jo_service' => Trans::getFinanceWord('service'),
            'jo_service_term' => Trans::getFinanceWord('serviceTerm'),
        ]);
        $jobField->setFilters([
            'jo_number' => Trans::getFinanceWord('joNumber'),
            'jo_service' => Trans::getFinanceWord('service'),
            'jo_service_term' => Trans::getFinanceWord('serviceTerm'),
        ]);
        $jobField->setAutoCompleteFields([
            'jd_jo_srv_id' => 'jo_srv_id',
            'jd_jo_service' => 'jo_service',
        ]);
        $jobField->setValueCode('jo_id');
        $jobField->setLabelCode('jo_number');
        $jobField->addParameter('jo_ss_id', $this->User->getSsId());
        $jobField->addClearField('jd_cc_id');
        $jobField->addClearField('jd_cc_code');
        $this->View->addModal($jobField->getModal());


        $relField = $this->Field->getSingleSelect('relation', 'jd_relation', $this->getStringParameter('jd_relation'));
        $relField->setHiddenField('jd_rel_id', $this->getIntParameter('jd_rel_id'));
        $relField->addParameter('rel_ss_id', $this->User->getSsId());
        $relField->setDetailReferenceCode('rel_id');
        $relField->addClearField('jd_rel_office');
        $relField->addClearField('jd_of_id');
        $relField->addClearField('jd_pic');
        $relField->addClearField('jd_cp_id');
        $relField->addClearField('jd_rb_number_rel');
        $relField->addClearField('jd_rb_rel');

        $relOfField = $this->Field->getSingleSelect('office', 'jd_rel_office', $this->getStringParameter('jd_rel_office'));
        $relOfField->setHiddenField('jd_of_id', $this->getIntParameter('jd_of_id'));
        $relOfField->addParameterById('of_rel_id', 'jd_rel_id', Trans::getFinanceWord('relation'));
        $relOfField->setDetailReferenceCode('of_id');

        $cpField = $this->Field->getSingleSelect('contactPerson', 'jd_pic', $this->getStringParameter('jd_pic'));
        $cpField->setHiddenField('jd_cp_id', $this->getIntParameter('jd_cp_id'));
        $cpField->addParameterById('cp_rel_id', 'jd_rel_id', Trans::getFinanceWord('relation'));
        $cpField->setDetailReferenceCode('cp_id');

        $rbField = $this->Field->getSingleSelect('relationBank', 'jd_rb_number_rel', $this->getStringParameter('jd_rb_number_rel'));
        $rbField->setHiddenField('jd_rb_rel', $this->getIntParameter('jd_rb_rel'));
        $rbField->addParameterById('rb_rel_id', 'jd_rel_id', Trans::getFinanceWord('relation'));
        $rbField->addParameter('rel_ss_id', $this->User->getSsId());
        $rbField->setDetailReferenceCode('rb_id');

        # CostCode Field.
        $costCodeField = $this->Field->getSingleSelect('costCode', 'jd_cc_code', $this->getStringParameter('jd_cc_code'));
        $costCodeField->setHiddenField('jd_cc_id', $this->getIntParameter('jd_cc_id'));
        $costCodeField->addParameter('cc_ss_id', $this->User->getSsId());
        $costCodeField->addParameterById('ccg_jo_id', 'jd_jo_id', Trans::getFinanceWord('jobOrder'));
        $costCodeField->addParameterById('ccg_srv_id', 'jd_jo_srv_id', Trans::getFinanceWord('service'));
        $costCodeField->addParameter('ccg_type', 'D');
        $costCodeField->setEnableDetailButton(false);
        $costCodeField->setEnableNewButton(false);

        $srvField = $this->Field->getText('jd_jo_service', $this->getStringParameter('jd_jo_service'));
        $srvField->setReadOnly();
        $ofInvoiceField = $this->Field->getSelect('jd_invoice_of_id', $this->getIntParameter('jd_invoice_of_id'));
        $ofInvoiceField->addOptions(OfficeDao::loadInvoiceOffice($this->User->getRelId()), 'of_name', 'of_id');


        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $fieldSet->addField(Trans::getFinanceWord('jobOrder'), $jobField, true);
        $fieldSet->addField(Trans::getFinanceWord('relation'), $relField, true);
        $fieldSet->addField(Trans::getFinanceWord('service'), $srvField, true);
        $fieldSet->addField(Trans::getFinanceWord('relationOffice'), $relOfField, true);
        $fieldSet->addField(Trans::getFinanceWord('invoiceOffice'), $ofInvoiceField, true);
        $fieldSet->addField(Trans::getFinanceWord('relationBank'), $rbField, true);
        $fieldSet->addField(Trans::getFinanceWord('costCode'), $costCodeField, true);
        $fieldSet->addField(Trans::getFinanceWord('picRelation'), $cpField);
        $fieldSet->addField(Trans::getFinanceWord('amount'), $this->Field->getNumber('jd_amount', $this->getFloatParameter('jd_amount')), true);
        $fieldSet->addField(Trans::getFinanceWord('invoiceRef'), $this->Field->getText('jd_rel_ref', $this->getStringParameter('jd_rel_ref')), true);
        $fieldSet->addField(Trans::getFinanceWord('date'), $this->Field->getCalendar('jd_date', $this->getStringParameter('jd_date')), true);
        $fieldSet->addField(Trans::getFinanceWord('refundDate'), $this->Field->getCalendar('jd_return_date', $this->getStringParameter('jd_return_date')), true);
        $fieldSet->addHiddenField($this->Field->getHidden('jd_jo_srv_id', $this->getIntParameter('jd_jo_srv_id')));

        # Create a portlet box.
        $portlet = new Portlet('JdFormPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to get job view portlet.
     *
     * @return Portlet
     */
    private function getDetailViewPortlet(): Portlet
    {
        $data = [
            [
                'label' => Trans::getFinanceWord('jobNumber'),
                'value' => $this->getStringParameter('jd_jo_number'),
            ],
            [
                'label' => Trans::getFinanceWord('service'),
                'value' => $this->getStringParameter('jd_jo_service'),
            ],
            [
                'label' => Trans::getFinanceWord('invoiceOffice'),
                'value' => $this->getStringParameter('jd_invoice_office'),
            ],
            [
                'label' => Trans::getFinanceWord('costCode'),
                'value' => $this->getStringParameter('jd_cc_code') . ' - ' . $this->getStringParameter('jd_cc_name'),
            ],
            [
                'label' => Trans::getFinanceWord('date'),
                'value' => $this->DtParser->formatDate($this->getStringParameter('jd_date')),
            ],
            [
                'label' => Trans::getFinanceWord('refundDate'),
                'value' => $this->DtParser->formatDate($this->getStringParameter('jd_return_date')),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('JdViewPtl', Trans::getFinanceWord('details'));
        $portlet->addText($content);
        $portlet->addText($this->Field->getHidden('jd_rel_id', $this->getIntParameter('jd_rel_id')));
        $portlet->addText($this->Field->getHidden('jd_jo_id', $this->getIntParameter('jd_jo_id')));
        $portlet->addText($this->Field->getHidden('jd_jo_rel_id', $this->getIntParameter('jd_jo_rel_id')));
        $portlet->addText($this->Field->getHidden('jd_jo_srv_id', $this->getIntParameter('jd_jo_srv_id')));
        $portlet->addText($this->Field->getHidden('jd_jo_srt_id', $this->getIntParameter('jd_jo_srt_id')));
        $portlet->addText($this->Field->getHidden('jd_invoice_of_id', $this->getIntParameter('jd_invoice_of_id')));
        $portlet->addText($this->Field->getHidden('jd_rb_rel', $this->getIntParameter('jd_rb_rel')));
        $portlet->addText($this->Field->getHidden('jd_of_id', $this->getIntParameter('jd_of_id')));
        $portlet->addText($this->Field->getHidden('jd_cp_id', $this->getIntParameter('jd_cp_id')));
        $portlet->addText($this->Field->getHidden('jd_number', $this->getStringParameter('jd_number')));
        if ($this->isValidParameter('jd_paid_on') === true) {
            $portlet->addText($this->Field->getHidden('jd_rb_paid', $this->getIntParameter('jd_rb_paid')));
        }
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to get job view portlet.
     *
     * @return Portlet
     */
    private function getRelationViewPortlet(): Portlet
    {
        $data = [
            [
                'label' => Trans::getFinanceWord('relation'),
                'value' => $this->getStringParameter('jd_relation'),
            ],
            [
                'label' => Trans::getFinanceWord('relationOffice'),
                'value' => $this->getStringParameter('jd_rel_office'),
            ],
            [
                'label' => Trans::getFinanceWord('pic'),
                'value' => $this->getStringParameter('jd_pic'),
            ],
            [
                'label' => Trans::getFinanceWord('bankAccount'),
                'value' => $this->getStringParameter('jd_bank_rel') . ' - ' . $this->getStringParameter('jd_rb_number_rel'),
            ],
            [
                'label' => Trans::getFinanceWord('accountName'),
                'value' => $this->getStringParameter('jd_rb_name_rel'),
            ],
            [
                'label' => Trans::getFinanceWord('bankBranch'),
                'value' => $this->getStringParameter('jd_rb_branch_rel'),
            ],
            [
                'label' => Trans::getFinanceWord('reference'),
                'value' => $this->getStringParameter('jd_rel_ref'),
            ],
        ];
        $content = StringFormatter::generateCustomTableView($data);
        # Create a portlet box.
        $portlet = new Portlet('JdRelViewPtl', Trans::getFinanceWord('relation'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


    /**
     * Function to add stock widget
     *
     * @return string
     */
    private function getWidget(): string
    {

        $results = '';
        # Create over due widget
        $aging = 0;
        $class = 'tile-stats tile-warning';
        $dueDate = DateTimeParser::createFromFormat($this->getStringParameter('jd_return_date') . ' 01:00:00', 'Y-m-d H:i:s');
        $today = DateTimeParser::createFromFormat(date('Y-m-d') . ' 01:00:00', 'Y-m-d H:i:s');
        $isPaid = false;
        if ($this->isValidParameter('jd_return_on')) {
            $class = 'tile-stats tile-success';
            $isPaid = true;
            $today = DateTimeParser::createFromFormat(mb_substr($this->getStringParameter('jd_return_on'), 0, 10) . ' 01:00:00', 'Y-m-d H:i:s');
        }
        if ($dueDate !== null && $today !== null) {
            $diff = DateTimeParser::different($dueDate, $today);
            $diffDays = (int)$diff['days'];
            if ($dueDate > $today) {
                if ($isPaid === false && $diffDays > 2) {
                    $class = 'tile-stats tile-success';
                }
            }
            if ($dueDate < $today) {
                $class = 'tile-stats tile-danger';
                $aging = $diffDays;
            }
        }

        $dueDateWidget = new NumberGeneral();
        $data = [
            'title' => Trans::getFinanceWord('overDueDays'),
            'icon' => '',
            'tile_style' => $class,
            'amount' => $this->Number->doFormatInteger($aging),
            'uom' => '',
            'url' => '',
        ];
        $dueDateWidget->setData($data);
        $dueDateWidget->setGridDimension(3, 6);
        $results .= $dueDateWidget->createView();

        $amount = new NumberGeneral();
        $data = [
            'title' => Trans::getFinanceWord('depositAmount'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-primary',
            'amount' => 'IDR ' . $this->Number->doFormatFloat($this->getFloatParameter('jd_amount')),
            'uom' => '',
            'url' => '',
        ];
        $amount->setData($data);
        $amount->setGridDimension(3, 6);
        $results .= $amount->createView();
        # Cash Advance
        $claimAmount = new NumberGeneral();
        $data = [
            'title' => Trans::getFinanceWord('claimAmount'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-danger',
            'amount' => 'IDR ' . $this->Number->doFormatFloat($this->getFloatParameter('jd_claim_amount')),
            'uom' => '',
            'url' => '',
        ];
        $claimAmount->setData($data);
        $claimAmount->setGridDimension(3, 6);
        $results .= $claimAmount->createView();
        # Registered
        $returnAmount = new NumberGeneral();
        $data = [
            'title' => Trans::getFinanceWord('refundAmount'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-dark',
            'amount' => 'IDR ' . $this->Number->doFormatFloat($this->getFloatParameter('jd_amount') - $this->getFloatParameter('jd_claim_amount')),
            'uom' => '',
            'url' => '',
        ];
        $returnAmount->setData($data);
        $returnAmount->setGridDimension(3, 6);
        $results .= $returnAmount->createView();

        return $results;
    }


    /**
     * Function to check if user has access to paid request.
     *
     * @return void
     */
    private function overridePageTitle(): void
    {
        $jdDao = new JobDepositDao();
        $status = $jdDao->generateStatus([
            'is_deleted' => $this->isValidParameter('jd_deleted_on'),
            'is_return' => $this->isValidParameter('jd_return_on'),
            'is_settle' => $this->isValidParameter('jd_settle_on'),
            'is_paid' => $this->isValidParameter('jd_paid_on'),
            'is_approved' => $this->isValidParameter('jd_approved_on'),
            'is_requested' => $this->isValidParameter('jda_id'),
            'is_rejected' => $this->isValidParameter('jda_deleted_on'),
        ]);
        $title = $this->PageSetting->getPageDescription();
        if ($this->isValidParameter('jd_number')) {
            $title = $this->getStringParameter('jd_number');
        }
        $this->View->setDescription($title . ' - ' . $status);
    }

    /**
     * Function to check is user allow to update
     *
     * @return bool
     */
    private function isAllowUpdate(): bool
    {
        return $this->PageSetting->checkPageRight('AllowUpdate')
            && (!$this->isValidParameter('jda_id') || ($this->isValidParameter('jda_id') && $this->isValidParameter('jda_deleted_on')))
            && !$this->isValidParameter('jd_deleted_on');
    }


    /**
     * Function to get request modal
     *
     * @return Modal
     */
    private function getRequestModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JdReqMdl', Trans::getFinanceWord('requestConfirmation'));
        $text = Trans::getWord('requestApprovalConfirmation', 'message');
        $modal->setFormSubmit($this->getMainFormId(), 'doRequest');
        $modal->setBtnOkName(Trans::getFinanceWord('yesRequest'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }

    /**
     * Function to get approve modal
     *
     * @return Modal
     */
    private function getApproveModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JdAppMdl', Trans::getFinanceWord('approveConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doApprove');
        $modal->setBtnOkName(Trans::getFinanceWord('yesApprove'));
        $text = Trans::getWord('approvalRequestConfirmation', 'message');
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);

        return $modal;
    }

    /**
     * Function to get reject modal
     *
     * @return Modal
     */
    private function getRejectModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('PcRejMdl', Trans::getFinanceWord('rejectConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doReject');
        $modal->setBtnOkName(Trans::getFinanceWord('yesReject'));
        $showModal = false;
        if ($this->getFormAction() === 'doReject' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12);
        $fieldSet->addField(Trans::getFinanceWord('reason'), $this->Field->getTextArea('jda_reject_reason', $this->getParameterForModal('jda_reject_reason', $showModal)), true);
        $fieldSet->addHiddenField($this->Field->getHidden('jda_id', $this->getIntParameter('jda_id')));

        $text = Trans::getWord('rejectRequestConfirmation', 'message');
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get paid modal
     *
     * @return Modal
     */
    private function getPaidModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JdPaidMdl', Trans::getFinanceWord('paidConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doPaid');
        $modal->setBtnOkName(Trans::getFinanceWord('yesPaid'));
        $showModal = false;
        if ($this->getFormAction() === 'doPaid' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        if ($this->isValidParameter('jd_pay_date') === false) {
            $this->setParameter('jd_pay_date', date('Y-m-d'));
        }
        if ($this->isValidParameter('jd_pay_time') === false) {
            $this->setParameter('jd_pay_time', date('Y-m-d'));
        }

        $rbField = $this->Field->getSingleSelect('relationBank', 'jd_rb_number_paid', $this->getParameterForModal('jd_rb_number_paid', $showModal));
        $rbField->setHiddenField('jd_rb_paid', $this->getParameterForModal('jd_rb_paid', $showModal));
        $rbField->addParameter('rb_rel_id', $this->User->getRelId());
        $rbField->addParameter('rel_ss_id', $this->User->getSsId());
        $rbField->setDetailReferenceCode('rb_id');

        $pmField = $this->Field->getSingleSelect('paymentMethod', 'jd_payment_method', $this->getParameterForModal('jd_payment_method', $showModal));
        $pmField->setHiddenField('jd_pm_id', $this->getParameterForModal('jd_pm_id', $showModal));
        $pmField->addParameter('pm_ss_id', $this->User->getSsId());
        $pmField->setEnableNewButton(false);
        $pmField->setEnableDetailButton(false);

        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $fieldSet->addField(Trans::getFinanceWord('paymentMethod'), $pmField, true);
        $fieldSet->addField(Trans::getFinanceWord('bankAp'), $rbField, true);
        $fieldSet->addField(Trans::getFinanceWord('date'), $this->Field->getCalendar('jd_pay_date', $this->getParameterForModal('jd_pay_date', true)), true);
        $fieldSet->addField(Trans::getFinanceWord('time'), $this->Field->getTime('jd_pay_time', $this->getParameterForModal('jd_pay_time', true)), true);
        $fieldSet->addField(Trans::getFinanceWord('transactionId'), $this->Field->getText('jd_paid_ref', $this->getParameterForModal('jd_paid_ref', $showModal)));

        $text = Trans::getWord('paymentDepositConfirmation', 'message');
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get approve modal
     *
     * @return Modal
     */
    private function getSettlementModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JdSettleMdl', Trans::getFinanceWord('settlementConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doSettlement');
        $modal->setBtnOkName(Trans::getFinanceWord('yesConfirm'));
        $showModal = false;
        if ($this->getFormAction() === 'doSettlement' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        if ($this->isValidParameter('jd_settle_date') === false) {
            $this->setParameter('jd_settle_date', date('Y-m-d'));
        }
        if ($this->isValidParameter('jd_settle_time') === false) {
            $this->setParameter('jd_settle_time', date('Y-m-d'));
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $fieldSet->addField(Trans::getFinanceWord('date'), $this->Field->getCalendar('jd_settle_date', $this->getParameterForModal('jd_settle_date', true)), true);
        $fieldSet->addField(Trans::getFinanceWord('time'), $this->Field->getTime('jd_settle_time', $this->getParameterForModal('jd_settle_time', true)), true);
        $fieldSet->addField(Trans::getFinanceWord('reference'), $this->Field->getText('jd_settle_ref', $this->getParameterForModal('jd_settle_ref', $showModal)));

        $text = Trans::getWord('claimSettlementConfirmation', 'message');
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);
        return $modal;
    }

    /**
     * Function to get approve modal
     *
     * @return Modal
     */
    private function getReturnModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('JdRefundMdl', Trans::getFinanceWord('refundConfirmation'));
        $modal->setFormSubmit($this->getMainFormId(), 'doRefund');
        $modal->setBtnOkName(Trans::getFinanceWord('yesConfirm'));
        $showModal = false;
        if ($this->getFormAction() === 'doRefund' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        if ($this->isValidParameter('jd_return_date') === false) {
            $this->setParameter('jd_return_date', date('Y-m-d'));
        }
        if ($this->isValidParameter('jd_return_time') === false) {
            $this->setParameter('jd_return_time', date('Y-m-d'));
        }

        $rbField = $this->Field->getSingleSelect('relationBank', 'jd_rb_number_return', $this->getParameterForModal('jd_rb_number_return', $showModal));
        $rbField->setHiddenField('jd_rb_return', $this->getParameterForModal('jd_rb_return', $showModal));
        $rbField->addParameter('rb_rel_id', $this->User->getRelId());
        $rbField->addParameter('rel_ss_id', $this->User->getSsId());
        $rbField->setDetailReferenceCode('rb_id');


        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        $fieldSet->addField(Trans::getFinanceWord('date'), $this->Field->getCalendar('jd_return_date', $this->getParameterForModal('jd_return_date', true)), true);
        $fieldSet->addField(Trans::getFinanceWord('time'), $this->Field->getTime('jd_return_time', $this->getParameterForModal('jd_return_time', true)), true);
        $fieldSet->addField(Trans::getFinanceWord('bankAr'), $rbField, true);

        $text = Trans::getWord('depositRefundConfirmation', 'message');
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $modal->addFieldSet($fieldSet);
        return $modal;
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        if ($this->isUpdate() === true) {
            # Create job button
            $joDao = new JobOrderDao();
            $btnView = new HyperLink('BtnJobView', $this->getStringParameter('jd_jo_number'), $joDao->getJobUrl('detail', $this->getIntParameter('jd_jo_srt_id'), $this->getIntParameter('jd_jo_id')));
            $btnView->viewAsButton();
            $btnView->setIcon(Icon::Eye)->btnWarning()->pullRight()->btnMedium();
            $this->View->addButtonAtTheBeginning($btnView);

            if ($this->isAllowUpdate()) {
                # enable delete button
                $this->setEnableDeleteButton();
                # Create button Request
                $modal = $this->getRequestModal();
                $this->View->addModal($modal);
                $btnReq = new ModalButton('btnReqPc', Trans::getFinanceWord('request'), $modal->getModalId());
                $btnReq->setIcon(Icon::PaperPlane)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButton($btnReq);
            } else {
                # Disable update
                $this->setDisableUpdate();
            }
            if (!$this->isValidParameter('jd_approved_on') && $this->isValidParameter('jda_id') && $this->isValidParameter('jda_deleted_on') === false && $this->PageSetting->checkPageRight('AllowApproveReject')) {
                # Create button Approve
                $approveMdl = $this->getApproveModal();
                $this->View->addModal($approveMdl);
                $btnApp = new ModalButton('btnAppPd', Trans::getFinanceWord('approve'), $approveMdl->getModalId());
                $btnApp->setIcon(Icon::Check)->btnSuccess()->pullRight()->btnMedium();
                $this->View->addButton($btnApp);
                # Create button Reject
                $rejectMdl = $this->getRejectModal();
                $this->View->addModal($rejectMdl);
                $btnRej = new ModalButton('btnRejPd', Trans::getFinanceWord('reject'), $rejectMdl->getModalId());
                $btnRej->setIcon(Icon::Times)->btnDanger()->pullRight()->btnMedium();
                $this->View->addButton($btnRej);
            }
            if ($this->isValidParameter('jd_approved_on') && !$this->isValidParameter('jd_paid_on') && $this->PageSetting->checkPageRight('AllowPayment')) {
                # Create button Reject
                $paidMdl = $this->getPaidModal();
                $this->View->addModal($paidMdl);
                $btnPaid = new ModalButton('btnPaidJd', Trans::getFinanceWord('paid'), $paidMdl->getModalId());
                $btnPaid->setIcon(Icon::Money)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButton($btnPaid);
            }
            if ($this->isValidParameter('jd_paid_on') && !$this->isValidParameter('jd_settle_on') && $this->PageSetting->checkPageRight('AllowSettlement')) {
                # Create button Reject
                $paidMdl = $this->getSettlementModal();
                $this->View->addModal($paidMdl);
                $btnPaid = new ModalButton('btnSettleJd', Trans::getFinanceWord('claimSettlement'), $paidMdl->getModalId());
                $btnPaid->setIcon(Icon::Check)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButton($btnPaid);
            }
            if ($this->isValidParameter('jd_settle_on') && !$this->isValidParameter('jd_return_on') && $this->PageSetting->checkPageRight('AllowPayment')) {
                # Create button Reject
                $paidMdl = $this->getReturnModal();
                $this->View->addModal($paidMdl);
                $btnPaid = new ModalButton('btnRefundJd', Trans::getFinanceWord('refund'), $paidMdl->getModalId());
                $btnPaid->setIcon(Icon::Money)->btnPrimary()->pullRight()->btnMedium();
                $this->View->addButton($btnPaid);
            }
        }
        parent::loadDefaultButton();
    }


    /**
     * Function to get the purchase Field Set.
     *
     * @return Portlet
     */
    protected function getClaimPortlet(): Portlet
    {
        # insert modal
        $modal = $this->getClaimModal();
        $this->View->addModal($modal);
        # delete modal
        $modalDelete = $this->getClaimDeleteModal();
        $this->View->addModal($modalDelete);

        $table = new Table('JoJddTbl');
        $table->setHeaderRow([
            'jdd_description' => Trans::getFinanceWord('description'),
            'jdd_quantity' => Trans::getFinanceWord('quantity'),
            'jdd_rate' => Trans::getFinanceWord('rate'),
            'jdd_exchange_rate' => Trans::getFinanceWord('exchangeRate'),
            'jdd_tax_name' => Trans::getFinanceWord('tax'),
            'jdd_total' => Trans::getFinanceWord('total'),
            'jdd_type' => Trans::getFinanceWord('type'),
        ]);
        $rows = [];
        $data = JobDepositDetailDao::getByJdId($this->getDetailReferenceValue());
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $row['jdd_description'] = $row['jdd_cc_code'] . ' ' . $row['jdd_description'];
            $row['jdd_quantity'] = $number->doFormatFloat($row['jdd_quantity']) . ' ' . $row['jdd_uom_code'];
            $row['jdd_rate'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jdd_rate']);
            $row['jdd_exchange_rate'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jdd_exchange_rate']);
            $row['jdd_total'] = $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($row['jdd_total']);
            if ($row['jdd_type'] === 'P') {
                $row['jdd_type'] = new LabelPrimary(Trans::getFinanceWord('cogs'));
            } else {
                $row['jdd_type'] = new LabelDark(Trans::getFinanceWord('reimburse'));
            }
            $rows[] = $row;
        }
        $table->addRows($rows);
        $table->addColumnAttribute('jdd_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('jdd_exchange_rate', 'style', 'text-align: right;');
        $table->addColumnAttribute('jdd_quantity', 'style', 'text-align: right;');
        $table->addColumnAttribute('jdd_total', 'style', 'text-align: right;');
        $table->addColumnAttribute('jdd_tax_name', 'style', 'text-align: center;');
        $table->addColumnAttribute('jdd_type', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('JoJddPtl', Trans::getFinanceWord('claim'));
        if ($this->isValidParameter('jd_settle_on') === false && $this->PageSetting->checkPageRight('AllowUpdate')) {
            $table->setUpdateActionByModal($modal, 'jdd', 'getByReference', ['jdd_id']);
            $table->setDeleteActionByModal($modalDelete, 'jdd', 'getByReferenceForDelete', ['jdd_id']);
            # create new purchase button
            $btnPurchaseMdl = new ModalButton('btnJoJddMdl', Trans::getFinanceWord('addClaim'), $modal->getModalId());
            $btnPurchaseMdl->setIcon(Icon::Plus)->btnPrimary()->pullRight()->btnMedium();
            $portlet->addButton($btnPurchaseMdl);
        }
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get purchase modal.
     *
     * @return Modal
     */
    protected function getClaimModal(): Modal
    {
        $modal = new Modal('JddAddMdl', Trans::getFinanceWord('claim'));
        $modal->setFormSubmit($this->getMainFormId(), 'doUpdateClaim');
        $showModal = false;
        if ($this->getFormAction() === 'doUpdateClaim' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        # Cost Code Field
        $ccField = $this->Field->getSingleSelectTable('costCode', 'jdd_cc_code', $this->getParameterForModal('jdd_cc_code', $showModal), 'loadPurchaseTable');
        $ccField->setHiddenField('jdd_cc_id', $this->getParameterForModal('jdd_cc_id', $showModal));
        $ccField->setTableColumns([
            'cc_group_name' => Trans::getWord('group'),
            'cc_code' => Trans::getWord('code'),
            'cc_name' => Trans::getWord('name'),
            'cc_type_name' => Trans::getWord('type'),
        ]);
        $ccField->setFilters([
            'cc_group' => Trans::getWord('group'),
            'cc_code' => Trans::getWord('code'),
            'cc_name' => Trans::getWord('name'),
        ]);
        $ccField->setAutoCompleteFields([
            'jdd_description' => 'cc_name',
            'jdd_type_name' => 'cc_type_name',
        ]);
        $ccField->setValueCode('cc_id');
        $ccField->setLabelCode('cc_name');
        $ccField->addParameter('cc_ss_id', $this->User->getSsId());
        $ccField->addParameterById('ccg_srv_id', 'jd_jo_srv_id', Trans::getWord('service'));
        $ccField->setParentModal($modal->getModalId());
        $this->View->addModal($ccField->getModal());

        $uomField = $this->Field->getSingleSelect('unit', 'jdd_uom_code', $this->getParameterForModal('jdd_uom_code', $showModal));
        $uomField->setHiddenField('jdd_uom_id', $this->getParameterForModal('jdd_uom_id', $showModal));
        $uomField->setDetailReferenceCode('uom_id');
        $uomField->setEnableNewButton(false);
        $uomField->setEnableDetailButton(false);

        $taxField = $this->Field->getSingleSelect('tax', 'jdd_tax_name', $this->getParameterForModal('jdd_tax_name', $showModal));
        $taxField->setHiddenField('jdd_tax_id', $this->getParameterForModal('jdd_tax_id', $showModal));
        $taxField->addParameter('tax_ss_id', $this->User->getSsId());
        $taxField->setDetailReferenceCode('tax_id');
        $taxField->setEnableNewButton(false);
        $taxField->setEnableDetailButton(false);

        $ccgTypeField = $this->Field->getText('jdd_type_name', $this->getParameterForModal('jdd_type_name', $showModal));
        $ccgTypeField->setReadOnly();
        $curField = $this->Field->getSingleSelect('currency', 'jdd_cur_iso', $this->getParameterForModal('jdd_cur_iso', $showModal));
        $curField->setHiddenField('jdd_cur_id', $this->getParameterForModal('jdd_cur_id', $showModal));
        $curField->setEnableDetailButton(false);
        $curField->setEnableNewButton(false);

        $fieldSet->addField(Trans::getFinanceWord('costCode'), $ccField, true);
        $fieldSet->addField(Trans::getFinanceWord('uom'), $uomField, true);
        $fieldSet->addField(Trans::getFinanceWord('description'), $this->Field->getText('jdd_description', $this->getParameterForModal('jdd_description', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('quantity'), $this->Field->getNumber('jdd_quantity', $this->getParameterForModal('jdd_quantity', $showModal)), true);

        $fieldSet->addField(Trans::getFinanceWord('type'), $ccgTypeField);
        $fieldSet->addField(Trans::getFinanceWord('rate'), $this->Field->getNumber('jdd_rate', $this->getParameterForModal('jdd_rate', $showModal)), true);
        $fieldSet->addField(Trans::getFinanceWord('currency'), $curField, true);
        $fieldSet->addField(Trans::getFinanceWord('tax'), $taxField, true);
        $fieldSet->addField(Trans::getFinanceWord('exchangeRate'), $this->Field->getNumber('jdd_exchange_rate', $this->getParameterForModal('jdd_exchange_rate', $showModal)), true);
        # Add hidden field
        $fieldSet->addHiddenField($this->Field->getHidden('jdd_id', $this->getParameterForModal('jdd_id', $showModal)));
        $modal->addFieldSet($fieldSet);

        return $modal;
    }

    /**
     * Function to get purchase delete modal.
     *
     * @return Modal
     */
    protected function getClaimDeleteModal(): Modal
    {
        $modal = new Modal('JddDltMdl', Trans::getWord('purchase'));
        $modal->setFormSubmit($this->getMainFormId(), 'doDeleteClaim');
        $showModal = false;
        if ($this->getFormAction() === 'doDeleteClaim' && $this->isValidPostValues() === false) {
            $modal->setShowOnLoad();
            $showModal = true;
        }
        $p = new Paragraph(Trans::getMessageWord('deleteConfirmation'));
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->addField(Trans::getFinanceWord('costCode'), $this->Field->getText('jdd_cc_code_del', $this->getParameterForModal('jdd_cc_code_del', $showModal)));
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('jdd_description_del', $this->getParameterForModal('jdd_description_del', $showModal)));
        $fieldSet->addField(Trans::getWord('qty'), $this->Field->getNumber('jdd_quantity_del', $this->getParameterForModal('jdd_quantity_del', $showModal)));
        $fieldSet->addField(Trans::getWord('rate'), $this->Field->getNumber('jdd_rate_del', $this->getParameterForModal('jdd_rate_del', $showModal)));
        $fieldSet->addField(Trans::getWord('uom'), $this->Field->getText('jdd_uom_code_del', $this->getParameterForModal('jdd_uom_code_del', $showModal)));
        $fieldSet->addField(Trans::getWord('tax'), $this->Field->getText('jdd_tax_name_del', $this->getParameterForModal('jdd_tax_name_del', $showModal)));
        $fieldSet->addHiddenField($this->Field->getHidden('jdd_id_del', $this->getParameterForModal('jdd_id_del', $showModal)));
        $fieldSet->setGridDimension(6, 6);
        $modal->addFieldSet($fieldSet);
        $modal->setBtnOkName(Trans::getWord('yesDelete'));

        return $modal;
    }

    /**
     * Function to get the time sheet field set
     *
     * @return Portlet
     */
    protected function getTimeSheetFieldSet(): Portlet
    {
        $table = new Table('PdTimeTbl');
        $table->setHeaderRow([
            'jd_ts_action' => Trans::getWord('action'),
            'jd_ts_creator' => Trans::getWord('user'),
            'jd_ts_time' => Trans::getWord('time'),
            'jd_ts_remark' => Trans::getWord('remark'),
        ]);
        $table->addRows($this->loadTimeSheetData());
        $table->setColumnType('pd_ts_time', 'datetime');
        # Create a portlet box.
        $portlet = new Portlet('PdTimePtl', Trans::getWord('timeSheet'));
        $portlet->addTable($table);

        return $portlet;
    }

    /**
     * Function to get the contact Field Set.
     *
     * @return array
     */
    private function loadTimeSheetData(): array
    {
        $result = [];
        $request = JobDepositApprovalDao::getByJdId($this->getDetailReferenceValue());
        if ($this->isValidParameter('jd_deleted_on') === true) {
            $result[] = [
                'jd_ts_action' => Trans::getFinanceWord('deleted'),
                'jd_ts_creator' => $this->getStringParameter('jd_deleted_by'),
                'jd_ts_time' => $this->getStringParameter('jd_deleted_on'),
                'jd_ts_remark' => $this->getStringParameter('jd_delete_reason'),
            ];
        }
        if ($this->isValidParameter('jd_return_on') === true) {
            $result[] = [
                'jd_ts_action' => Trans::getFinanceWord('refund'),
                'jd_ts_creator' => $this->getStringParameter('jd_return_by'),
                'jd_ts_time' => $this->getStringParameter('jd_return_on'),
                'jd_ts_remark' => '',
            ];
        }
        if ($this->isValidParameter('jd_settle_on') === true) {
            $result[] = [
                'jd_ts_action' => Trans::getFinanceWord('settlement'),
                'jd_ts_creator' => $this->getStringParameter('jd_settle_by'),
                'jd_ts_time' => $this->getStringParameter('jd_settle_on'),
                'jd_ts_remark' => $this->getStringParameter('jd_settle_ref'),
            ];
        }
        if ($this->isValidParameter('jd_paid_on') === true) {
            $result[] = [
                'jd_ts_action' => Trans::getFinanceWord('paid'),
                'jd_ts_creator' => $this->getStringParameter('jd_paid_by'),
                'jd_ts_time' => $this->getStringParameter('jd_paid_on'),
                'jd_ts_remark' => $this->getStringParameter('jd_paid_ref'),
            ];
        }
        if ($this->isValidParameter('jd_approved_on') === true) {
            $result[] = [
                'jd_ts_action' => Trans::getFinanceWord('approve'),
                'jd_ts_creator' => $this->getStringParameter('jd_approved_by'),
                'jd_ts_time' => $this->getStringParameter('jd_approved_on'),
                'jd_ts_remark' => '',
            ];
        }
        foreach ($request as $row) {
            if (empty($row['jda_deleted_on']) === false) {
                $result[] = [
                    'jd_ts_action' => Trans::getFinanceWord('reject'),
                    'jd_ts_creator' => $row['jda_deleted_by'],
                    'jd_ts_time' => $row['jda_deleted_on'],
                    'jd_ts_remark' => $row['jda_reject_reason'],
                ];
            }
            $result[] = [
                'jd_ts_action' => Trans::getFinanceWord('request'),
                'jd_ts_creator' => $row['jda_created_by'],
                'jd_ts_time' => $row['jda_created_on'],
                'jd_ts_remark' => '',
            ];
        }
        $result[] = [
            'jd_ts_action' => Trans::getFinanceWord('draft'),
            'jd_ts_creator' => $this->getStringParameter('jd_created_by'),
            'jd_ts_time' => $this->getStringParameter('jd_created_on'),
            'jd_ts_remark' => '',
        ];


        return $result;
    }

}
