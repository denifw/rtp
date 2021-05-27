<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Viewer\Finance\CashAndBank;

use App\Frame\Formatter\DateTimeParser;
use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Gui\Html\Labels\LabelYesNo;
use App\Frame\Gui\Templates\NumberGeneral;
use App\Frame\Mvc\AbstractViewerModel;
use App\Model\Dao\Finance\CashAndBank\BankAccountDao;
use App\Frame\Gui\Portlet;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Finance\CashAndBank\CashAdvanceDao;
use App\Model\Dao\Finance\CashAndBank\ElectronicBalanceDao;

/**
 * Class to handle the creation of detail BankAccount page
 *
 * @package    app
 * @subpackage Model\Viewer\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class BankAccount extends AbstractViewerModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ba', 'ba_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return BankAccountDao::getByReferenceAndSystem($this->getDetailReferenceValue(), $this->User->getSsId());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isValidParameter('ba_deleted_on') === true) {
            $this->setDisableUpdate();
            $this->View->addErrorMessage(Trans::getWord('deletedData', 'message', '', [
                'user' => $this->getStringParameter('ba_deleted_by'),
                'time' => DateTimeParser::format($this->getStringParameter('ba_deleted_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                'reason' => $this->getStringParameter('ba_deleted_reason')
            ]));
        }
        if ($this->isValidParameter('ba_blocked_on') === true) {
            $this->setDisableUpdate();
            $this->View->addErrorMessage(Trans::getWord('blockedAccount', 'message', '', [
                'user' => $this->getStringParameter('ba_block_by'),
                'time' => DateTimeParser::format($this->getStringParameter('ba_block_on'), 'Y-m-d H:i:s', 'd M Y - H:i'),
                'reason' => $this->getStringParameter('ba_block_reason')
            ]));
        }
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
        if ($this->getStringParameter('ba_main') === 'N') {
            $this->Tab->addContent('general', $this->getWidget());
        }

    }

    /**
     * Function to add stock widget
     *
     * @return string
     */
    private function getWidget(): string
    {
        $number = new NumberFormatter();
        $results = '';
        # Balance
        $balance = new NumberGeneral();
        $data = [
            'title' => Trans::getFinanceWord('currentBalance'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-success',
            'amount' => $this->getStringParameter('ba_currency') . ' ' . $number->doFormatFloat($this->getFloatParameter('ba_balance')),
            'uom' => '',
            'url' => '',
        ];
        $balance->setData($data);
        $balance->setGridDimension(6, 6);
        $results .= $balance->createView();

        # Electronic Balance
        if ($this->isValidParameter('ba_us_id') === true) {
            $eCardBalance = ElectronicBalanceDao::getTotalBalanceUser($this->getIntParameter('ba_us_id'));
            $plan = new NumberGeneral();
            $data = [
                'title' => Trans::getFinanceWord('eCardBalance'),
                'icon' => '',
                'tile_style' => 'tile-stats tile-dark-blue',
                'amount' => $this->getStringParameter('ba_currency') . ' ' . $number->doFormatFloat($eCardBalance),
                'uom' => '',
                'url' => '',
            ];
            $plan->setData($data);
            $plan->setGridDimension(6, 6);
            $results .= $plan->createView();
        }

        # Cash Advance
        $totalCashAdvance = CashAdvanceDao::getTotalUnSettlementCashByBankAccount($this->getDetailReferenceValue());
        $cashAdvance = new NumberGeneral();
        $data = [
            'title' => Trans::getFinanceWord('onGoingCash'),
            'icon' => '',
            'tile_style' => 'tile-stats tile-warning',
            'amount' => $this->User->Settings->getCurrencyIso() . ' ' . $number->doFormatFloat($totalCashAdvance),
            'uom' => '',
            'url' => '',
        ];
        $cashAdvance->setData($data);
        $cashAdvance->setGridDimension(6, 6);
        $results .= $cashAdvance->createView();
        return $results;
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        $number = new NumberFormatter($this->User);
        $data = [
            [
                'label' => Trans::getFinanceWord('code'),
                'value' => $this->getStringParameter('ba_code'),
            ],
            [
                'label' => Trans::getFinanceWord('bankName'),
                'value' => $this->getStringParameter('ba_bank_name'),
            ],
            [
                'label' => Trans::getFinanceWord('bankBranch'),
                'value' => $this->getStringParameter('ba_bank_branch'),
            ],
            [
                'label' => Trans::getFinanceWord('accountNumber'),
                'value' => $this->getStringParameter('ba_account_number'),
            ],
            [
                'label' => Trans::getFinanceWord('accountName'),
                'value' => $this->getStringParameter('ba_account_name'),
            ],
            [
                'label' => Trans::getFinanceWord('mainAccount'),
                'value' => new LabelYesNo($this->getStringParameter('ba_main')),
            ],
            [
                'label' => Trans::getFinanceWord('receivable'),
                'value' => new LabelYesNo($this->getStringParameter('ba_receivable')),
            ],
            [
                'label' => Trans::getFinanceWord('payable'),
                'value' => new LabelYesNo($this->getStringParameter('ba_payable')),
            ],
        ];
        if ($this->getStringParameter('ba_main') === 'N') {
            $data[] = [
                'label' => Trans::getFinanceWord('accountManager'),
                'value' => $this->getStringParameter('ba_user')
            ];
        }
        if ($this->getStringParameter('ba_main') === 'N') {
            $data[] = [
                'label' => Trans::getFinanceWord('ceiling'),
                'value' => $number->doFormatFloat($this->getFloatParameter('ba_limit'))
            ];
        }

        $content = StringFormatter::generateCustomTableView($data);

        # Create a portlet box.
        $portlet = new Portlet('CacGnPtl', Trans::getFinanceWord('accountDetail'));
        $portlet->addText($content);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


}
