<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Statistic\Finance;

use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\HyperLink;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Portlet;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractStatisticModel;
use Illuminate\Support\Facades\DB;

/**
 * Model statistic Stock Card Warehouse
 *
 * @package    app
 * @subpackage Model\Statistic\Job\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class OutstandingInvoice extends AbstractStatisticModel
{

    /**
     * Property to store date time object
     *
     * @param DateTimeParser $DtParser
     */
    private $DtParser;

    /**
     * GoodsDamageType constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'outstandingInvoice');
        $this->setParameters($parameters);
        $this->DtParser = new DateTimeParser();
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # Create Customer Field
        $customerField = $this->Field->getSingleSelect('relation', 'customer', $this->getStringParameter('customer'));
        $customerField->setHiddenField('rel_id', $this->getIntParameter('rel_id'));
        $customerField->addParameter('rel_ss_id', $this->User->getSsId());
        $customerField->setEnableDetailButton(false);
        $customerField->setEnableNewButton(false);

        # Create View Field
        $viewField = $this->Field->getRadioGroup('type', $this->getStringParameter('type'));
        $viewField->addRadios([
            'S' => Trans::getFinanceWord('sales'),
            'P' => Trans::getFinanceWord('purchase'),
        ]);

        $this->StatisticForm->addField(Trans::getFinanceWord('customer'), $customerField);
        $this->StatisticForm->addField(Trans::getFinanceWord('invoiceDateFrom'), $this->Field->getCalendar('date_from', $this->getStringParameter('date_from')));
        $this->StatisticForm->addField(Trans::getFinanceWord('invoiceDateUntil'), $this->Field->getCalendar('date_until', $this->getStringParameter('date_until')));
        $this->StatisticForm->addField(Trans::getFinanceWord('invoiceNumber'), $this->Field->getText('inv_number', $this->getStringParameter('inv_number')));
        $this->StatisticForm->addField(Trans::getFinanceWord('invoiceType'), $viewField);
        $this->StatisticForm->addField(Trans::getFinanceWord('showOverDue'), $this->Field->getYesNo('aging', $this->getStringParameter('aging', 'Y')));
        $this->StatisticForm->setGridDimension(4);
    }

    /**
     * Abstract function to load the view.
     *
     * @return void
     */
    public function loadViews(): void
    {
        $this->addContent('result', $this->getResultPortlet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('type');
    }


    /**
     * Function to get the stock card table.
     *
     * @return Portlet
     */
    private function getResultPortlet(): Portlet
    {
        $table = new Table('ResTbl');
        $table->setHeaderRow([
            'inv_number' => Trans::getFinanceWord('invoiceNumber'),
            'inv_relation' => Trans::getFinanceWord('customer'),
            'inv_reference' => Trans::getFinanceWord('reference'),
            'inv_date' => Trans::getFinanceWord('invoiceDate'),
            'inv_amount' => Trans::getFinanceWord('amount'),
        ]);

        if ($this->getStringParameter('type', 'S') === 'P') {
            $table->removeColumn('inv_relation');
            $table->addColumnAfter('inv_number', 'inv_relation', Trans::getFinanceWord('vendor'));
            $data = $this->loadPurchaseData();
        } else {
            $data = $this->loadSalesData();
        }
        if ($this->getStringParameter('aging', 'Y') === 'Y') {
            $table->removeColumn('inv_amount');
            $table->addColumnAtTheEnd('days0', Trans::getFinanceWord('open'));
            $table->addColumnAtTheEnd('days30', Trans::getFinanceWord('overDue') . ' 1 - 30 ' . Trans::getFinanceWord('days'));
            $table->addColumnAtTheEnd('days60', Trans::getFinanceWord('overDue') . ' 31 - 60 ' . Trans::getFinanceWord('days'));
            $table->addColumnAtTheEnd('days90', Trans::getFinanceWord('overDue') . ' 61 - 90 ' . Trans::getFinanceWord('days'));
            $table->addColumnAtTheEnd('days91', Trans::getFinanceWord('overDue') . ' > 90 ' . Trans::getFinanceWord('days'));
            $table->setColumnType('days0', 'float');
            $table->setColumnType('days30', 'float');
            $table->setColumnType('days60', 'float');
            $table->setColumnType('days90', 'float');
            $table->setColumnType('days91', 'float');
            $table->setFooterType('days0', 'SUM');
            $table->setFooterType('days30', 'SUM');
            $table->setFooterType('days60', 'SUM');
            $table->setFooterType('days90', 'SUM');
            $table->setFooterType('days91', 'SUM');
        } else {
            $table->setColumnType('inv_amount', 'float');
            $table->setFooterType('inv_amount', 'SUM');
        }

        $table->addRows($this->doPrepareData($data));
        $table->addColumnAtTheEnd('inv_action', Trans::getWord('action'));
        $table->addColumnAttribute('inv_action', 'style', 'text-align: center;');

        $portlet = new Portlet('ResPtl', 'Results');
        $portlet->addTable($table);
        $this->addDatas('ResPtl', $portlet);

        return $portlet;
    }

    /**
     * Get query to get the quotation data.
     *
     * @param array $data To store the data.
     *
     * @return array
     */
    private function doPrepareData(array $data): array
    {
        $results = [];
        foreach ($data as $row) {
            $ref = [
                [
                    'label' => Trans::getFinanceWord('ref'),
                    'value' => $row['inv_reference'],
                ],
                [
                    'label' => Trans::getFinanceWord('so'),
                    'value' => $row['inv_so'],
                ],

            ];

            $date = [
                [
                    'label' => Trans::getFinanceWord('date'),
                    'value' => $this->DtParser->formatDate($row['inv_date']),
                ],
                [
                    'label' => Trans::getFinanceWord('dueDate'),
                    'value' => $this->DtParser->formatDate($row['inv_due_date']),
                ],

            ];
            if (empty($row['inv_receive']) === false) {
                $date[] = [
                    'label' => Trans::getFinanceWord('receive'),
                    'value' => $this->DtParser->formatDateTime($row['inv_receive'], 'Y-m-d H:i:s', 'd.M.Y'),
                ];
            }
            $row['inv_reference'] = StringFormatter::generateKeyValueTableView($ref);
            $row['inv_date'] = StringFormatter::generateKeyValueTableView($date);
            $agingDays = $this->countAgingDays($row['inv_due_date']);
            if ($agingDays === 0) {
                $row['days0'] = $row['inv_amount'];
            } else if ($agingDays < 31) {
                $row['days30'] = $row['inv_amount'];
            } else if ($agingDays < 61) {
                $row['days60'] = $row['inv_amount'];
            } else if ($agingDays < 91) {
                $row['days90'] = $row['inv_amount'];
            } else {
                $row['days91'] = $row['inv_amount'];
            }
            if ($row['inv_type'] === 'P') {
                $url = url('/purchaseInvoice/detail?pi_id=' . $row['inv_id']);
            } else {
                $url = url('/salesInvoice/detail?si_id=' . $row['inv_id']);
            }
            $btnView = new HyperLink('InvBtn' . $row['inv_type'] . $row['inv_id'], '', $url);
            $btnView->viewAsButton();
            $btnView->setIcon(Icon::Eye)->btnSuccess()->viewIconOnly();
            $row['inv_action'] = $btnView;
            $results[] = $row;
        }


        return $results;

    }

    /**
     * Get query to get the quotation data.
     *
     * @param string $date To store the date.
     *
     * @return int
     */
    private function countAgingDays(string $date): int
    {
        $results = 0;
        $dueDate = DateTimeParser::createFromFormat($date . ' 01:00:00');
        $today = DateTimeParser::createFromFormat(date('Y-m-d') . ' 01:00:00');
        if ($dueDate !== null && $today !== null && $dueDate < $today) {
            $diff = DateTimeParser::different($dueDate, $today);
            $results = (int)$diff['days'];
        }
        return $results;
    }

    /**
     * Get query to get the quotation data.
     *
     * @return array
     */
    private function loadSalesData(): array
    {
        # Set Select query;
        $query = 'SELECT \'S\' as inv_type, si.si_id as inv_id, si.si_number as inv_number, si.si_manual, rel.rel_name as inv_relation, io.of_name as inv_office, si.si_date as inv_date,
                        si.si_due_date as inv_due_date, si.si_receive_on as inv_receive, si.si_rel_reference as inv_reference, so.so_number as inv_so, (CASE WHEN si.si_manual = \'Y\' THEN sid.sid_total ELSE jos.jos_total END) as inv_amount,
                        \'\' as inv_rel_ref
                    FROM sales_invoice as si INNER JOIN
                    relation as rel ON rel.rel_id = si.si_rel_id INNER JOIN
                    office as io ON io.of_id = si.si_of_id LEFT OUTER JOIN
                    sales_order as so ON si.si_so_id = so.so_id LEFT OUTER JOIN
                    (SELECT sid_si_id, SUM(sid_total) as sid_total
                        FROM sales_invoice_detail
                        WHERE (sid_deleted_on IS NULL)
                        GROUP BY sid_si_id) as sid ON si.si_id = sid.sid_si_id LEFT OUTER JOIN
                    (SELECT s.sid_si_id, SUM(j.jos_total) as jos_total
                        FROM sales_invoice_detail as s INNER JOIN
                            job_sales as j ON s.sid_jos_id = j.jos_id
                        WHERE (s.sid_deleted_on IS NULL)
                        GROUP BY s.sid_si_id) as jos ON si.si_id = jos.sid_si_id';
        # Set Where condition.
        $query .= $this->getSalesCondition();
        $query .= ' ORDER BY si.si_due_date, si.si_id';
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Get query to get the quotation data.
     *
     * @return array
     */
    private function loadPurchaseData(): array
    {
        $query = "SELECT 'P' as inv_type, pi.pi_id  as inv_id, pi.pi_number as inv_number, rel.rel_name as inv_relation, io.of_name as inv_office,
                       pi.pi_date as inv_date, pi.pi_due_date as inv_due_date, '' as inv_receive, pi.pi_rel_reference as inv_reference,
                       '' as inv_so, SUM(pid.pid_amount) as inv_amount
                FROM purchase_invoice as pi INNER JOIN
                     relation as rel ON pi.pi_rel_id = rel.rel_id INNER JOIN
                     office as io ON io.of_id = pi.pi_of_id  LEFT OUTER JOIN
                     (SELECT p.pid_pi_id, (CASE WHEN p.pid_jop_id IS NULL THEN p.pid_total ELSE jop.jop_total END) as pid_amount
                      FROM purchase_invoice_detail as p LEFT OUTER JOIN
                           job_purchase jop ON p.pid_jop_id = jop.jop_id
                      where p.pid_deleted_on is null) as pid ON pi.pi_id = pid.pid_pi_id";
        $query .= $this->getPurchaseCondition();
        $query .= ' GROUP BY pi.pi_id, pi.pi_number, rel.rel_name, io.of_name, pi.pi_date, pi.pi_due_date, pi.pi_rel_reference, pi.pi_reference';
        $query .= ' ORDER BY pi.pi_due_date, pi.pi_id';
        $sqlResults = DB::select($query);
        return DataParser::arrayObjectToArray($sqlResults);
    }

    /**
     * Function to get the where condition.
     *
     * @return string
     */
    private function getSalesCondition(): string
    {
        # Set where conditions
        $wheres = [];
        if ($this->isValidParameter('rel_id')) {
            $wheres[] = '(si.si_rel_id = ' . $this->getIntParameter('rel_id') . ')';
        }
        if ($this->isValidParameter('date_from') === true) {
            if ($this->isValidParameter('date_until') === true) {
                $wheres[] = "(si.si_date >= '" . $this->getStringParameter('date_from') . "')";
            } else {
                $wheres[] = "(si.si_date = '" . $this->getStringParameter('date_from') . "')";
            }
        }
        if ($this->isValidParameter('date_until') === true) {
            if ($this->isValidParameter('date_from') === true) {
                $wheres[] = "(si.si_date <= '" . $this->getStringParameter('date_until') . "')";
            } else {
                $wheres[] = "(si.si_date = '" . $this->getStringParameter('date_until') . "')";
            }
        }
        if ($this->isValidParameter('inv_number')) {
            $wheres[] = SqlHelper::generateLikeCondition('si.si_number', $this->getStringParameter('inv_number'));
        }
        $wheres[] = '(si.si_paid_on IS NULL)';
        $wheres[] = '(si.si_deleted_on IS NULL)';
        $wheres[] = '(si.si_ss_id = ' . $this->User->getSsId() . ')';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }

    /**
     * Function to get the where condition.
     *
     * @return string
     */
    private function getPurchaseCondition(): string
    {
        # Set where conditions
        $wheres = [];
        if ($this->isValidParameter('rel_id')) {
            $wheres[] = '(pi.pi_rel_id = ' . $this->getIntParameter('rel_id') . ')';
        }
        if ($this->isValidParameter('date_from') === true) {
            if ($this->isValidParameter('date_until') === true) {
                $wheres[] = "(pi.pi_date >= '" . $this->getStringParameter('date_from') . "')";
            } else {
                $wheres[] = "(pi.pi_date = '" . $this->getStringParameter('date_from') . "')";
            }
        }
        if ($this->isValidParameter('date_until') === true) {
            if ($this->isValidParameter('date_from') === true) {
                $wheres[] = "(pi.pi_date <= '" . $this->getStringParameter('date_until') . "')";
            } else {
                $wheres[] = "(pi.pi_date = '" . $this->getStringParameter('date_until') . "')";
            }
        }
        if ($this->isValidParameter('inv_number')) {
            $wheres[] = SqlHelper::generateLikeCondition('pi.pi_number', $this->getStringParameter('inv_number'));
        }
        $wheres[] = '(pi.pi_paid_on IS NULL)';
        $wheres[] = '(pi.pi_deleted_on IS NULL)';
        $wheres[] = '(pi.pi_ss_id = ' . $this->User->getSsId() . ')';
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }

        # return the where query.
        return $strWhere;
    }
}
