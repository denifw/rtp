<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\CashAndBank;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelPrimary;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\CashAndBank\BankAccountDao;

/**
 * Class to control the system of BankAccount.
 *
 * @package    app
 * @subpackage Model\Listing\Finance\CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class BankAccount extends AbstractListingModel
{

    /**
     * BankAccount constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ba');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        # User
        $usField = $this->Field->getSingleSelect('us', 'ba_user', $this->getStringParameter('ba_user'));
        $usField->setHiddenField('ba_us_id', $this->getStringParameter('ba_us_id'));
        $usField->addParameter('ba_ss_id', $this->User->getSsId());
        $usField->setEnableDetailButton(false);
        $usField->setEnableNewButton(false);

        $this->ListingForm->addField(Trans::getWord('code'), $this->Field->getText('ba_code', $this->getStringParameter('ba_code')));
        $this->ListingForm->addField(Trans::getWord('description'), $this->Field->getText('ba_description', $this->getStringParameter('ba_description')));
        if ($this->PageSetting->checkPageRight('AllowSeeAllAccount') === true) {
            $this->ListingForm->addField(Trans::getWord('owner'), $usField);
        } else {
            $this->ListingForm->addHiddenField($this->Field->getHidden('ba_us_id', $this->User->getId()));
        }
        $this->ListingForm->addField(Trans::getWord('blocked'), $this->Field->getYesNo('ba_blocked', $this->getStringParameter('ba_blocked')));

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
            'ba_code' => Trans::getWord('code'),
            'ba_description' => Trans::getWord('description'),
            'ba_currency' => Trans::getWord('currency'),
            'ba_current_balance' => Trans::getWord('balance'),
            'ba_user' => Trans::getWord('owner'),
            'ba_type' => Trans::getWord('type'),
            'ba_bn_short_name' => Trans::getWord('bank'),
            'ba_account_number' => Trans::getWord('accountNumber'),
            'ba_status' => Trans::getWord('status'),
        ]);
        # Load the data for BankAccount.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->setColumnType('ba_current_balance', 'float');
        $this->ListingTable->addColumnAttribute('ba_status', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('ba_type', 'style', 'text-align: center;');
        $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['ba_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return BankAccountDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = BankAccountDao::loadData($this->getWhereCondition());
        $results = [];
        foreach ($data as $row) {
            $type = [];
            if ($row['ba_main'] === 'Y') {
                $type[] = new LabelDanger(Trans::getWord('investor'));
                $row['ba_current_balance'] = null;
            }
            if ($row['ba_receivable'] === 'Y') {
                $type[] = new LabelSuccess(Trans::getWord('ar'));
            }
            if ($row['ba_payable'] === 'Y') {
                $type[] = new LabelPrimary(Trans::getWord('ap'));
            }
            $row['ba_type'] = StringFormatter::generateTableView($type);
            if (empty($row['ba_deleted_on']) === false) {
                $status = new LabelDanger(Trans::getWord('deleted'));
            } elseif (empty($row['ba_block_on']) === false) {
                $status = new LabelDark(Trans::getWord('blocked'));
            } else {
                $status = new LabelSuccess(Trans::getWord('active'));
            }
            $row['ba_status'] = $status;
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Function to get the where condition.
     *
     * @return SqlHelper
     */
    private function getWhereCondition(): SqlHelper
    {
        $helper = new SqlHelper();
        $helper->setLimit($this->getLimitTable(), $this->getLimitOffsetTable());
        $helper->addOrderByString($this->ListingSort->getOrderByFieldsString());
        # Set where conditions
        $helper->addStringWhere('ba_ss_id', $this->User->getSsId());
        $helper->addStringWhere('ba_us_id', $this->getStringParameter('ba_us_id'));
        $helper->addLikeWhere('ba_code', $this->getStringParameter('ba_code'));
        $helper->addLikeWhere('ba_description', $this->getStringParameter('ba_description'));
        if ($this->isValidParameter('ba_blocked') === true) {
            $blocked = $this->getStringParameter('ba_blocked');
            if ($blocked === 'Y') {
                $helper->addNullWhere('ba.ba_block_on', false);
            } else {
                $helper->addNullWhere('ba.ba_block_on');
            }
        }
        if ($this->isValidParameter('ba_deleted') === true) {
            $deleted = $this->getStringParameter('ba_deleted');
            if ($deleted === 'Y') {
                $helper->addNullWhere('ba.ba_deleted_on', false);
            } else {
                $helper->addNullWhere('ba.ba_deleted_on');
            }
        }
        return $helper;
    }
}
