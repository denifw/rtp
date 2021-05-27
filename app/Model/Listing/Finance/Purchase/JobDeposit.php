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
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Finance\Purchase\JobDepositDao;

/**
 * Class to control the system of JobDeposit.
 *
 * @package    app
 * @subpackage Model\Listing\Finance\Purchase
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class JobDeposit extends AbstractListingModel
{

    /**
     * JobDeposit constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'jd');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $relationField = $this->Field->getSingleSelect('relation', 'jd_relation', $this->getStringParameter('jd_relation'));
        $relationField->setHiddenField('jd_rel_id', $this->getIntParameter('jd_rel_id'));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setEnableNewButton(false);
        $relationField->setEnableDetailButton(false);

        $statusField = $this->Field->getSelect('jd_status', $this->getStringParameter('jd_status'));
        $statusField->addOption(Trans::getFinanceWord('draft'), '1');
        $statusField->addOption(Trans::getFinanceWord('waitingApproval'), '2');
        $statusField->addOption(Trans::getFinanceWord('rejected'), '3');
        $statusField->addOption(Trans::getFinanceWord('waitingPayment'), '4');
        $statusField->addOption(Trans::getFinanceWord('waitingSettlement'), '5');
        $statusField->addOption(Trans::getFinanceWord('waitingRefund'), '6');
        $statusField->addOption(Trans::getFinanceWord('completed'), '7');
        $statusField->addOption(Trans::getFinanceWord('canceled'), '8');


        $this->ListingForm->addField(Trans::getFinanceWord('number'), $this->Field->getText('jd_number', $this->getStringParameter('jd_number')));
        $this->ListingForm->addField(Trans::getFinanceWord('jobNumber'), $this->Field->getText('jo_number', $this->getStringParameter('jo_number')));
        $this->ListingForm->addField(Trans::getFinanceWord('relation'), $relationField);
        $this->ListingForm->addField(Trans::getFinanceWord('reference'), $this->Field->getText('jd_reference', $this->getStringParameter('jd_reference')));
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
            'jd_number' => Trans::getFinanceWord('number'),
            'job_order' => Trans::getFinanceWord('jobOrder'),
            'rel_name' => Trans::getFinanceWord('relation'),
            'jd_cc_name' => Trans::getFinanceWord('description'),
            'jd_ref' => Trans::getFinanceWord('reference'),
            'jd_amount' => Trans::getFinanceWord('amount'),
            'jd_date' => Trans::getFinanceWord('date'),
            'jd_status' => Trans::getFinanceWord('status'),
        ]);
        # Load the data for JobDeposit.
        $listingData = $this->loadData();
        $this->ListingTable->addRows($listingData);
        $this->ListingTable->addColumnAttribute('jd_number', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('jd_status', 'style', 'text-align: center;');
        $this->ListingTable->setViewActionByHyperlink($this->getUpdateRoute(), ['jd_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        # Set Select query;
        $query = 'SELECT count(DISTINCT (jd_id)) AS total_rows
                   FROM job_deposit as jd INNER JOIN
                        job_order as jo ON jd.jd_jo_id = jo.jo_id INNER JOIN
                        service as srv ON jo.jo_srv_id = srv.srv_id INNER JOIN
                        service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                        relation as rel ON jd.jd_rel_id = rel.rel_id INNER JOIN
                        cost_code as cc ON jd.jd_cc_id = cc.cc_id INNER JOIN
                        office as o ON jd.jd_of_id = o.of_id';
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
        $query = 'SELECT jd.jd_id, jd.jd_number, jo.jo_number, srv.srv_name, srt.srt_name, rel.rel_name, jd.jd_date, jd.jd_return_date,
                        jd.jd_amount, jd.jd_approved_on, jd.jd_paid_on, jd.jd_settle_on, jd.jd_return_on, jd.jd_deleted_on,
                        jd.jd_deleted_reason, jd.jd_paid_ref, jd.jd_settle_ref, jda.jda_id, jda.jda_reject_reason, jda.jda_deleted_on,
                        (CASE WHEN jdd.jdd_total IS NULL THEN 0 ELSE jdd.jdd_total END) as jd_claim_amount,
                        jd.jd_rel_ref, cc.cc_name as jd_cc_name
                   FROM job_deposit as jd INNER JOIN
                        job_order as jo ON jd.jd_jo_id = jo.jo_id INNER JOIN
                        service as srv ON jo.jo_srv_id = srv.srv_id INNER JOIN
                        service_term as srt ON jo.jo_srt_id = srt.srt_id INNER JOIN
                        relation as rel ON jd.jd_rel_id = rel.rel_id INNER JOIN
                        cost_code as cc ON jd.jd_cc_id = cc.cc_id INNER JOIN
                        office as o ON jd.jd_of_id = o.of_id LEFT OUTER JOIN
                         (SELECT jdd_jd_id, SUM(jdd_total) as jdd_total
                            FROM job_deposit_detail
                            WHERE jdd_deleted_on IS NULL
                            GROUP BY jdd_jd_id) as jdd ON jd.jd_id = jdd.jdd_jd_id LEFT OUTER JOIN
                              job_deposit_approval as jda ON jd.jd_jda_id = jda.jda_id LEFT OUTER JOIN
                              users as uad ON jda.jda_deleted_by = uad.us_id';
        # Set Where condition.
        $query .= $this->getWhereCondition();
        # Set group by query.
        $query .= ' GROUP BY jd.jd_id, jo.jo_number, srv.srv_name, srt.srt_name, rel.rel_name, jd.jd_date, jd.jd_return_date,
                        jd.jd_amount, jd.jd_approved_on, jd.jd_paid_on, jd.jd_settle_on, jd.jd_return_on, jd.jd_deleted_on,
                        jd.jd_deleted_reason, jd.jd_paid_ref, jd.jd_settle_ref, jda.jda_id, jda.jda_reject_reason,
                        jda.jda_deleted_on, jdd.jdd_total, jd.jd_rel_ref, jd.jd_number, cc.cc_name';
        # Set order by query.
        if (empty($this->ListingSort->getSelectedField()) === false) {
            $query .= $this->ListingSort->getOrderByQuery();
        }

        return $this->doPrepareData($this->loadDatabaseRow($query));
    }


    /**
     * Function to get the where condition.
     *
     * @param array $data to store the data.
     *
     * @return array
     */
    private function doPrepareData(array $data): array
    {
        $results = [];
        $dtParser = new DateTimeParser();
        $jdDao = new JobDepositDao();
        $number = new NumberFormatter();
        foreach ($data as $row) {
            $row['job_order'] = StringFormatter::generateTableView([
                $row['jo_number'],
                $row['srv_name'] . ' - ' . $row['srt_name'],
            ], 'text-align: center;');
            $row['jd_ref'] = StringFormatter::generateKeyValueTableView([
                [
                    'label' => Trans::getFinanceWord('invoiceRef'),
                    'value' => $row['jd_rel_ref'],
                ],
                [
                    'label' => Trans::getFinanceWord('payment'),
                    'value' => $row['jd_paid_ref'],
                ],
                [
                    'label' => Trans::getFinanceWord('settlement'),
                    'value' => $row['jd_settle_ref'],
                ],
            ]);
            $row['jd_date'] = StringFormatter::generateKeyValueTableView([
                [
                    'label' => Trans::getFinanceWord('deposit'),
                    'value' => $dtParser->formatDate($row['jd_date']),
                ],
                [
                    'label' => Trans::getFinanceWord('refund'),
                    'value' => $dtParser->formatDate($row['jd_return_date']),
                ],
            ]);
            $amounts = [
                [
                    'label' => Trans::getFinanceWord('deposit'),
                    'value' => $number->doFormatFloat((float)$row['jd_amount']),
                ],
                [
                    'label' => Trans::getFinanceWord('claim'),
                    'value' => $number->doFormatFloat((float)$row['jd_claim_amount']),
                ],
            ];
            if (empty($row['jd_settle_on']) === false) {
                $refund = (float)$row['jd_amount'] - (float)$row['jd_claim_amount'];
                $amounts[] = [
                    'label' => Trans::getFinanceWord('refund'),
                    'value' => $number->doFormatFloat($refund),
                ];
            }
            $row['jd_amount'] = StringFormatter::generateKeyValueTableView($amounts);

            $row['jd_status'] = $jdDao->generateStatus([
                'is_deleted' => !empty($row['jd_deleted_on']),
                'is_return' => !empty($row['jd_return_on']),
                'is_settle' => !empty($row['jd_settle_on']),
                'is_paid' => !empty($row['jd_paid_on']),
                'is_approved' => !empty($row['jd_approved_on']),
                'is_requested' => !empty($row['jda_id']),
                'is_rejected' => !empty($row['jda_deleted_on']),
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
        if ($this->isValidParameter('jd_number')) {
            $wheres[] = SqlHelper::generateLikeCondition('jd.jd_number', $this->getStringParameter('jd_number'));
        }
        if ($this->isValidParameter('jo_number')) {
            $wheres[] = SqlHelper::generateLikeCondition('jo.jo_number', $this->getStringParameter('jo_number'));
        }
        if ($this->isValidParameter('jd_reference')) {
            $wheres[] = SqlHelper::generateOrLikeCondition(['jd.jd_rel_ref', 'jd.jd_paid_ref', 'jd.jd_settle_ref'], $this->getStringParameter('jd_reference'));
        }
        if ($this->isValidParameter('jd_rel_id')) {
            $wheres[] = '(jd.jd_rel_id = ' . $this->getIntParameter('jd_rel_id') . ')';
        }
        if ($this->isValidParameter('jd_status') === true) {
            $status = $this->getIntParameter('jd_status');
            if ($status === 1) {
                # Draft
                $wheres[] = '(jd.jd_approved_on IS NULL)';
                $wheres[] = '(jd.jd_jda_id IS NULL)';
                $wheres[] = '(jd.jd_id NOT IN (SELECT jda_jd_id
                                                FROM job_deposit_approval
                                                GROUP BY jda_jd_id))';
                $wheres[] = '(jd.jd_deleted_on IS NULL)';
            } else if ($status === 2) {
                # Waiting Approval
                $wheres[] = '(jd.jd_approved_on IS NULL)';
                $wheres[] = '(jd.jd_jda_id IS NOT NULL)';
                $wheres[] = '(jd.jd_deleted_on IS NULL)';
            } else if ($status === 3) {
                # Rejected
                $wheres[] = '(jd.jd_approved_on IS NULL)';
                $wheres[] = '(jd.jd_jda_id IS NULL)';
                $wheres[] = '(jd.jd_id IN (SELECT jda_jd_id
                                                FROM job_deposit_approval
                                                GROUP BY jda_jd_id))';
                $wheres[] = '(jd.jd_deleted_on IS NULL)';
            } else if ($status === 4) {
                # Waiting Payment
                $wheres[] = '(jd.jd_approved_on IS NOT NULL)';
                $wheres[] = '(jd.jd_paid_on IS NULL)';
                $wheres[] = '(jd.jd_deleted_on IS NULL)';
            } else if ($status === 5) {
                # Waiting Settlement
                $wheres[] = '(jd.jd_paid_on IS NOT NULL)';
                $wheres[] = '(jd.jd_settle_on IS NULL)';
                $wheres[] = '(jd.jd_deleted_on IS NULL)';
            } else if ($status === 6) {
                # Waiting Refund
                $wheres[] = '(jd.jd_settle_on IS NOT NULL)';
                $wheres[] = '(jd.jd_return_on IS NULL)';
                $wheres[] = '(jd.jd_deleted_on IS NULL)';
            } else if ($status === 7) {
                # Completed
                $wheres[] = '(jd.jd_return_on IS NOT NULL)';
                $wheres[] = '(jd.jd_deleted_on IS NULL)';
            } else {
                $wheres[] = '(jd.jd_deleted_on IS NOT NULL)';
            }
        }

        $wheres[] = '(jd.jd_ss_id = ' . $this->User->getSsId() . ')';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
