<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Listing\Finance\Purchase;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Finance\Purchase\PurchaseInvoiceDao;
use App\Model\Dao\Relation\OfficeDao;

/**
 * Class to control the system of Invoice.
 *
 * @package    app
 * @subpackage Model\Listing\Finance\Purchase
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class PurchaseInvoice extends AbstractListingModel
{

    /**
     * Invoice constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'purchaseInvoice');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create Fields.
        $invOfField = $this->Field->getSelect('pi_of_id', $this->getIntParameter('pi_of_id'));
        $wheres = [];
        $wheres[] = '(of_deleted_on is null)';
        $wheres[] = '(of_rel_id = ' . $this->User->getRelId() . ')';
        $wheres[] = "(of_active = 'Y')";
        $wheres[] = "(of_invoice = 'Y')";
        $invOfField->addOptions(OfficeDao::loadSimpleData($wheres), 'of_name', 'of_id');
        $invOfField->setPleaseSelect();

        $srvField = $this->Field->getSingleSelect('service', 'pi_service', $this->getStringParameter('pi_service'));
        $srvField->setHiddenField('pi_srv_id', $this->getIntParameter('pi_srv_id'));
        $srvField->addParameter('ssr_ss_id', $this->User->getSsId());
        $srvField->setEnableDetailButton(false);
        $srvField->setEnableNewButton(false);

        $vendorField = $this->Field->getSingleSelect('relation', 'pi_vendor', $this->getStringParameter('pi_vendor'));
        $vendorField->setHiddenField('pi_rel_id', $this->getIntParameter('pi_rel_id'));
        $vendorField->addParameter('rel_ss_id', $this->User->getSsId());
        $vendorField->setEnableNewButton(false);
        $vendorField->setEnableDetailButton(false);

        $statusField = $this->Field->getSelect('pi_status', $this->getStringParameter('pi_status'));
        $statusField->addOption(Trans::getFinanceWord('draft'), '1');
        $statusField->addOption(Trans::getFinanceWord('waitingApproval'), '2');
        $statusField->addOption(Trans::getFinanceWord('rejected'), '3');
        $statusField->addOption(Trans::getFinanceWord('waitingPayment'), '4');
        $statusField->addOption(Trans::getFinanceWord('paid'), '5');
        $statusField->addOption(Trans::getFinanceWord('canceled'), '6');

        $this->ListingForm->addField(Trans::getFinanceWord('number'), $this->Field->getText('pi_number', $this->getStringParameter('pi_number')));
        $this->ListingForm->addField(Trans::getFinanceWord('invoiceOffice'), $invOfField);
        $this->ListingForm->addField(Trans::getFinanceWord('service'), $srvField);
        $this->ListingForm->addField(Trans::getFinanceWord('vendor'), $vendorField);
        $this->ListingForm->addField(Trans::getFinanceWord('joNumber'), $this->Field->getText('jo_number', $this->getStringParameter('jo_number')));
        $this->ListingForm->addField(Trans::getFinanceWord('reference'), $this->Field->getText('pi_reference', $this->getStringParameter('pi_reference')));
        $this->ListingForm->addField(Trans::getFinanceWord('status'), $statusField);
    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        # set header column table
        $this->ListingTable->setHeaderRow([
            'pi_number' => Trans::getFinanceWord('number'),
            'pi_relation' => Trans::getFinanceWord('vendor'),
            'srv_name' => Trans::getFinanceWord('service'),
            'pi_reference' => Trans::getFinanceWord('reference'),
            'pi_amount' => Trans::getFinanceWord('amount'),
            'pi_date' => Trans::getFinanceWord('invoiceDate'),
            'pi_time' => Trans::getFinanceWord('time'),
            'pi_status' => Trans::getFinanceWord('status'),
        ]);
        # Load the data for Invoice.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setColumnType('pi_amount', 'float');
        $this->ListingTable->addColumnAttribute('pi_status', 'style', 'text-align: center;');
        $this->ListingTable->setViewActionByHyperlink($this->getUpdateRoute(), ['pi_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (pi_id)) AS total_rows
                   FROM purchase_invoice as pi INNER JOIN
                relation as rel ON rel.rel_id = pi.pi_rel_id INNER JOIN
                service as srv ON srv.srv_id = pi.pi_srv_id INNER JOIN
                office as io ON io.of_id = pi.pi_of_id LEFT OUTER JOIN
                cash_advance as ca ON pi.pi_ca_id = ca.ca_id ';
        # Set where condition.
        $query .= $this->getWhereCondition();

        return $this->loadTotalListingRows($query);
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        # Set Select query;
        $query = 'SELECT pi.pi_id, pi.pi_number, pi.pi_rel_id, rel.rel_name as pi_relation,
                    pi.pi_rel_reference, pi.pi_date, pi.pi_due_date, pi.pi_approve_on, pi.pi_paid_on,
                    pia.pia_id, pia.pia_pi_id, pia.pia_created_on, pia.pia_reject_reason, pia.pia_deleted_on,
                    srv.srv_name, io.of_name, pi.pi_reference, ca.ca_number, SUM(pid_amount) as pi_amount,
                    pi.pi_deleted_on
                FROM purchase_invoice as pi INNER JOIN
                relation as rel ON rel.rel_id = pi.pi_rel_id INNER JOIN
                service as srv ON srv.srv_id = pi.pi_srv_id INNER JOIN
                office as io ON io.of_id = pi.pi_of_id LEFT OUTER JOIN
                cash_advance as ca ON pi.pi_ca_id = ca.ca_id LEFT OUTER JOIN
                 (SELECT p.pid_pi_id, (CASE WHEN p.pid_jop_id IS NULL THEN p.pid_total ELSE jop.jop_total END) as pid_amount
                    FROM purchase_invoice_detail as p LEFT OUTER JOIN
                    job_purchase jop ON p.pid_jop_id = jop.jop_id
                    where p.pid_deleted_on is null) as pid ON pi.pi_id = pid.pid_pi_id LEFT OUTER JOIN
                    purchase_invoice_approval as pia ON pi.pi_pia_id = pia.pia_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY pi.pi_id, pi.pi_number, pi.pi_rel_id, rel.rel_name,
                    pi.pi_rel_reference, pi.pi_date, pi.pi_due_date, pi.pi_approve_on, pi.pi_paid_on,
                    pia.pia_id, pia.pia_pi_id, pia.pia_created_on, pia.pia_reject_reason, pia.pia_deleted_on,
                    srv.srv_name, io.of_name, pi.pi_reference, ca.ca_number, pi.pi_deleted_on';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY pi.pi_id';
        }

        return $this->doPrepareData($this->loadDatabaseRow($query));
    }

    /**
     * Function to get the where condition.
     *
     * @param array $data To store the data
     *
     * @return array
     */
    private function doPrepareData(array $data): array
    {
        $results = [];
        $dtParser = new DateTimeParser();
        $piDao = new PurchaseInvoiceDao();
        foreach ($data as $row) {
            $reference = [];
            if (empty($row['pi_reference']) === false) {
                $reference[] = [
                    'label' => 'PO',
                    'value' => $row['pi_reference'],
                ];
            }
            $reference[] = [
                'label' => 'INV',
                'value' => $row['pi_rel_reference'],
            ];
            $row['pi_reference'] = StringFormatter::generateKeyValueTableView($reference);
            $row['pi_date'] = StringFormatter::generateKeyValueTableView([
                [
                    'label' => Trans::getFinanceWord('date'),
                    'value' => $dtParser->formatDate($row['pi_date']),
                ],
                [
                    'label' => Trans::getFinanceWord('dueDate'),
                    'value' => $dtParser->formatDate($row['pi_due_date']),
                ],
            ]);

            $row['pi_time'] = StringFormatter::generateKeyValueTableView([
                [
                    'label' => Trans::getFinanceWord('requestedOn'),
                    'value' => $dtParser->formatDateTime($row['pia_created_on']),
                ],
                [
                    'label' => Trans::getFinanceWord('approvedOn'),
                    'value' => $dtParser->formatDateTime($row['pi_approve_on']),
                ],
                [
                    'label' => Trans::getFinanceWord('paidOn'),
                    'value' => $dtParser->formatDateTime($row['pi_paid_on']),
                ],
            ]);

            $row['pi_status'] = $piDao->generateStatus([
                'is_deleted' => !empty($row['pi_deleted_on']),
                'is_paid' => !empty($row['pi_paid_on']),
                'is_approved' => !empty($row['pi_approve_on']),
                'is_rejected' => !empty($row['pia_created_on']) && !empty($row['pia_deleted_on']),
                'is_requested' => !empty($row['pia_created_on']) && empty($row['pia_deleted_on']),
            ]);

            $results[] = $row;
        }
        return $results;

    }

    /**
     * Function to get the where condition.
     *
     * @return string
     */
    private function getWhereCondition(): string
    {
        # Set where conditions
        $wheres = [];
        $wheres[] = '(pi.pi_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('pi_number') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('pi.pi_number', $this->getStringParameter('pi_number'));
        }
        if ($this->isValidParameter('pi_reference') === true) {
            $wheres[] = StringFormatter::generateOrLikeQuery($this->getStringParameter('pi_reference'), [
                'pi.pi_reference', 'pi.pi_reference',
            ]);
        }
        if ($this->isValidParameter('jo_number') === true) {
            $joWheres = [];
            $joWheres[] = StringFormatter::generateLikeQuery('jo.jo_number', $this->getStringParameter('jo_number'));
            $joWheres[] = '(jo.jo_deleted_on is NULL)';
            $joWheres[] = '(jo.jo_ss_id = ' . $this->User->getSsId() . ')';
            $joWheres[] = '(jop.jop_deleted_on is NULL)';
            $joWheres[] = '(pid.pid_deleted_on is NULL)';
            $strJoWheres = ' WHERE ' . implode(' AND ', $joWheres);
            $wheres[] = '(pi.pi_id IN (SELECT (CASE WHEN pid.pid_pi_id IS NULL THEN 0 ELSE pid.pid_pi_id END)
                                    FROM purchase_invoice_detail as pid INNER JOIN
                                    job_purchase as jop ON pid.pid_jop_id = jop.jop_id INNER JOIN
                                    job_order as jo ON jo.jo_id = jop.jop_jo_id ' . $strJoWheres . '
                                    GROUP BY pid.pid_pi_id))';
        }
        if ($this->isValidParameter('pi_of_id') === true) {
            $wheres[] = '(pi.pi_of_id = ' . $this->getIntParameter('pi_of_id') . ')';
        }
        if ($this->isValidParameter('pi_srv_id') === true) {
            $wheres[] = '(pi.pi_srv_id = ' . $this->getIntParameter('pi_srv_id') . ')';
        }
        if ($this->isValidParameter('pi_rel_id') === true) {
            $wheres[] = '(pi.pi_rel_id = ' . $this->getIntParameter('pi_rel_id') . ')';
        }
        if ($this->isValidParameter('pi_status') === true) {
            $status = $this->getIntParameter('pi_status');
            if ($status === 1) {
                # Draft
                $wheres[] = '(pi.pi_approve_on IS NULL)';
                $wheres[] = '(pi.pi_pia_id IS NULL)';
                $wheres[] = '(pi.pi_id NOT IN (SELECT pia_pi_id
                                                FROM purchase_invoice_approval
                                                GROUP BY pia_pi_id))';
                $wheres[] = '(pi.pi_deleted_on IS NULL)';
            } else if ($status === 2) {
                # Waiting Approval
                $wheres[] = '(pi.pi_approve_on IS NULL)';
                $wheres[] = '(pi.pi_pia_id IS NOT NULL)';
                $wheres[] = '(pi.pi_deleted_on IS NULL)';
            } else if ($status === 3) {
                # Rejected
                # Waiting Approval
                $wheres[] = '(pi.pi_approve_on IS NULL)';
                $wheres[] = '(pi.pi_pia_id IS NULL)';
                $wheres[] = '(pi.pi_id IN (SELECT pia_pi_id
                                                FROM purchase_invoice_approval
                                                GROUP BY pia_pi_id))';
                $wheres[] = '(pi.pi_deleted_on IS NULL)';
            } else if ($status === 4) {
                # Waiting Payment
                $wheres[] = '(pi.pi_approve_on IS NOT NULL)';
                $wheres[] = '(pi.pi_paid_on IS NULL)';
                $wheres[] = '(pi.pi_deleted_on IS NULL)';
            } else if ($status === 5) {
                # Paid
                $wheres[] = '(pi.pi_paid_on IS NOT NULL)';
                $wheres[] = '(pi.pi_deleted_on IS NULL)';
            } else {
                $wheres[] = '(pi.pi_deleted_on IS NOT NULL)';
            }
        }


        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
