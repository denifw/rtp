<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Listing\Finance\Sales;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Finance\Sales\SalesInvoiceDao;
use App\Model\Dao\Relation\OfficeDao;

/**
 * Class to control the system of SalesInvoice.
 *
 * @package    app
 * @subpackage Model\Listing\Finance\Sales
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class SalesInvoice extends AbstractListingModel
{

    /**
     * SalesInvoice constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'salesInvoice');
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
        $invOfField = $this->Field->getSelect('si_of_id', $this->getIntParameter('si_of_id'));
        $wheres = [];
        $wheres[] = '(of_deleted_on is null)';
        $wheres[] = '(of_rel_id = ' . $this->User->getRelId() . ')';
        $wheres[] = "(of_active = 'Y')";
        $wheres[] = "(of_invoice = 'Y')";
        $invOfField->addOptions(OfficeDao::loadSimpleData($wheres), 'of_name', 'of_id');
        $invOfField->setPleaseSelect();

        $vendorField = $this->Field->getSingleSelect('relation', 'si_customer', $this->getStringParameter('si_customer'));
        $vendorField->setHiddenField('si_rel_id', $this->getIntParameter('si_rel_id'));
        $vendorField->addParameter('rel_ss_id', $this->User->getSsId());
        $vendorField->setEnableNewButton(false);
        $vendorField->setEnableDetailButton(false);

        $statusField = $this->Field->getSelect('si_status', $this->getStringParameter('si_status'));
        $statusField->addOption(Trans::getFinanceWord('draft'), '1');
        $statusField->addOption(Trans::getFinanceWord('waitingApproval'), '2');
        $statusField->addOption(Trans::getFinanceWord('rejected'), '3');
        $statusField->addOption(Trans::getFinanceWord('waitingPayment'), '4');
        $statusField->addOption(Trans::getFinanceWord('paid'), '5');
        $statusField->addOption(Trans::getFinanceWord('canceled'), '6');

        $this->ListingForm->addField(Trans::getFinanceWord('number'), $this->Field->getText('si_number', $this->getStringParameter('si_number')));
        $this->ListingForm->addField(Trans::getFinanceWord('invoiceOffice'), $invOfField);
        $this->ListingForm->addField(Trans::getFinanceWord('customer'), $vendorField);
        $this->ListingForm->addField(Trans::getFinanceWord('soNumber'), $this->Field->getText('so_number', $this->getStringParameter('so_number')));
        $this->ListingForm->addField(Trans::getFinanceWord('reference'), $this->Field->getText('si_reference', $this->getStringParameter('si_reference')));
        $this->ListingForm->addField(Trans::getFinanceWord('status'), $statusField);
        $this->ListingForm->setGridDimension(4);
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
            'si_number' => Trans::getFinanceWord('number'),
            'si_customer' => Trans::getFinanceWord('customer'),
            'si_reference' => Trans::getFinanceWord('reference'),
            'si_total_amount' => Trans::getFinanceWord('amount'),
            'si_date' => Trans::getFinanceWord('invoiceDate'),
            'si_time' => Trans::getFinanceWord('time'),
            'si_status' => Trans::getFinanceWord('status'),
        ]);
        # Load the data for SalesInvoice.
        $listingData = $this->loadData();
        $this->ListingTable->setColumnType('si_total_amount', 'float');
        $this->ListingTable->addColumnAttribute('si_status', 'style', 'text-align: center;');
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->setViewActionByHyperlink($this->getUpdateRoute(), ['si_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (si_id)) AS total_rows
                   FROM sales_invoice as si INNER JOIN
                        office as io ON si.si_of_id = io.of_id INNER JOIN
                        relation as rel ON si.si_rel_id = rel.rel_id LEFT OUTER JOIN
                        sales_order as so ON si.si_so_id = so.so_id';
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
        $query = 'SELECT si.si_id, si.si_number, si.si_manual, rel.rel_name as si_customer, so.so_number as si_so_number, so.so_customer_ref,
                    si.si_rel_reference, si.si_paid_ref, si.si_date, si.si_due_date, si.si_pay_time, si.si_approve_on, si.si_paid_on,
                    si.si_deleted_on, sia.sia_id, sia.sia_created_on, sia.sia_deleted_on, (CASE WHEN si.si_manual = \'Y\' THEN sid.sid_total ELSE jos.jos_total END) as si_total_amount,
                    si.si_receive_on
                FROM sales_invoice as si INNER JOIN
                        office as io ON si.si_of_id = io.of_id INNER JOIN
                        relation as rel ON si.si_rel_id = rel.rel_id LEFT OUTER JOIN
                        sales_order as so ON si.si_so_id = so.so_id LEFT OUTER JOIN
                        sales_invoice_approval as sia ON si.si_sia_id = sia.sia_id LEFT OUTER JOIN
                        users as urj ON sia.sia_deleted_by = urj.us_id LEFT OUTER JOIN
                        (SELECT sid_si_id, SUM(sid_total) as sid_total
                            FROM sales_invoice_detail
                            WHERE  (sid_deleted_on IS NULL)
                            GROUP BY sid_si_id) as sid ON si.si_id = sid.sid_si_id LEFT OUTER JOIN
                        (SELECT s.sid_si_id, SUM(j.jos_total) as jos_total
                            FROM sales_invoice_detail as s INNER JOIN
                                job_sales as j ON s.sid_jos_id = j.jos_id
                            WHERE (s.sid_deleted_on IS NULL)
                            GROUP BY s.sid_si_id) as jos ON si.si_id = jos.sid_si_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY si.si_id, si.si_number, si.si_manual, rel.rel_name, so.so_number, so.so_customer_ref,
                    si.si_rel_reference, si.si_paid_ref, si.si_date, si.si_due_date, si.si_approve_on, si.si_paid_on,
                    si.si_deleted_on, sia.sia_id, sia.sia_deleted_on, sid.sid_total, jos.jos_total, si.si_pay_time,
                    sia.sia_created_on, si.si_receive_on ';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        } else {
            $query .= ' ORDER BY si.si_deleted_on DESC, si.si_paid_on DESC, si.si_approve_on DESC, si.si_id';
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
        $siDao = new SalesInvoiceDao();
        foreach ($data as $row) {
            $reference = [];
            if (empty($row['si_so_number']) === false) {
                $reference[] = [
                    'label' => 'SO',
                    'value' => $row['si_so_number'],
                ];
            }
            $reference[] = [
                'label' => 'PO',
                'value' => $row['si_rel_reference'],
            ];
            $row['si_reference'] = StringFormatter::generateKeyValueTableView($reference);
            if (empty($row['si_date']) === false) {
                $row['si_date'] = StringFormatter::generateKeyValueTableView([
                    [
                        'label' => Trans::getFinanceWord('date'),
                        'value' => $dtParser->formatDate($row['si_date']),
                    ],
                    [
                        'label' => Trans::getFinanceWord('dueDate'),
                        'value' => $dtParser->formatDate($row['si_due_date']),
                    ],
                    [
                        'label' => Trans::getFinanceWord('payDate'),
                        'value' => $dtParser->formatDateTime($row['si_pay_time'], 'Y-m-d H:i:s', 'd.M.Y'),
                    ],
                ]);
            }

            $row['si_time'] = StringFormatter::generateKeyValueTableView([
                [
                    'label' => Trans::getFinanceWord('requestedOn'),
                    'value' => $dtParser->formatDateTime($row['sia_created_on']),
                ],
                [
                    'label' => Trans::getFinanceWord('approvedOn'),
                    'value' => $dtParser->formatDateTime($row['si_approve_on']),
                ],
                [
                    'label' => Trans::getFinanceWord('receivedOn'),
                    'value' => $dtParser->formatDateTime($row['si_receive_on']),
                ],
                [
                    'label' => Trans::getFinanceWord('paidOn'),
                    'value' => $dtParser->formatDateTime($row['si_paid_on']),
                ],
            ]);

            $row['si_status'] = $siDao->generateStatus([
                'is_deleted' => !empty($row['si_deleted_on']),
                'is_paid' => !empty($row['si_paid_on']),
                'is_received' => !empty($row['si_receive_on']),
                'is_approved' => !empty($row['si_approve_on']),
                'is_rejected' => !empty($row['sia_created_on']) && !empty($row['sia_deleted_on']),
                'is_requested' => !empty($row['sia_created_on']) && empty($row['sia_deleted_on']),
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
        $wheres[] = '(si.si_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('si_number') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('si.si_number', $this->getStringParameter('si_number'));
        }
        if ($this->isValidParameter('si_reference') === true) {
            $wheres[] = StringFormatter::generateOrLikeQuery($this->getStringParameter('si_reference'), [
                'si.si_rel_reference', 'si.si_paid_ref',
            ]);
        }
        if ($this->isValidParameter('so_number') === true) {
            $wheres[] = StringFormatter::generateLikeQuery('so.so_number', $this->getStringParameter('so_number'));
        }
        if ($this->isValidParameter('si_of_id') === true) {
            $wheres[] = '(si.si_of_id = ' . $this->getIntParameter('si_of_id') . ')';
        }
        if ($this->isValidParameter('si_rel_id') === true) {
            $wheres[] = '(si.si_rel_id = ' . $this->getIntParameter('si_rel_id') . ')';
        }
        if ($this->isValidParameter('si_status') === true) {
            $status = $this->getIntParameter('si_status');
            if ($status === 1) {
                # Draft
                $wheres[] = '(si.si_approve_on IS NULL)';
                $wheres[] = '(si.si_sia_id IS NULL)';
                $wheres[] = '(si.si_id NOT IN (SELECT sia_si_id
                                                FROM sales_invoice_approval
                                                GROUP BY sia_si_id))';
                $wheres[] = '(si.si_deleted_on IS NULL)';
            } else if ($status === 2) {
                # Waiting Approval
                $wheres[] = '(si.si_approve_on IS NULL)';
                $wheres[] = '(si.si_sia_id IS NOT NULL)';
                $wheres[] = '(si.si_deleted_on IS NULL)';
            } else if ($status === 3) {
                # Rejected
                # Waiting Approval
                $wheres[] = '(si.si_approve_on IS NULL)';
                $wheres[] = '(si.si_sia_id IS NULL)';
                $wheres[] = '(si.si_id IN (SELECT sia_si_id
                                                FROM sales_invoice_approval
                                                GROUP BY sia_si_id))';
                $wheres[] = '(si.si_deleted_on IS NULL)';
            } else if ($status === 4) {
                # Waiting Payment
                $wheres[] = '(si.si_approve_on IS NOT NULL)';
                $wheres[] = '(si.si_paid_on IS NULL)';
                $wheres[] = '(si.si_deleted_on IS NULL)';
            } else if ($status === 5) {
                # Paid
                $wheres[] = '(si.si_paid_on IS NOT NULL)';
                $wheres[] = '(si.si_deleted_on IS NULL)';
            } else {
                $wheres[] = '(si.si_deleted_on IS NOT NULL)';
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
