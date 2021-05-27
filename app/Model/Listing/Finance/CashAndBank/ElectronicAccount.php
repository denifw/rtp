<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Listing\Finance\CashAndBank;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Labels\LabelDanger;
use App\Frame\Gui\Html\Labels\LabelDark;
use App\Frame\Gui\Html\Labels\LabelSuccess;
use App\Frame\Gui\Html\Labels\LabelWarning;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Finance\CashAndBank\ElectronicAccountDao;

/**
 * Class to control the system of ElectronicAccount.
 *
 * @package    app
 * @subpackage Model\Listing\Finance/CashAndBank
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2021 PT Makmur Berkat Teknologi.
 */
class ElectronicAccount extends AbstractListingModel
{

    /**
     * ElectronicAccount constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ea');
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
        $usField = $this->Field->getSingleSelect('user', 'ea_user', $this->getStringParameter('ea_user'), 'loadElectronicAccountUser');
        $usField->setHiddenField('ea_us_id', $this->getIntParameter('ea_us_id'));
        $usField->addParameter('ea_ss_id', $this->User->getSsId());
        $usField->setEnableDetailButton(false);
        $usField->setEnableNewButton(false);
        if ($this->PageSetting->checkPageRight('AllowSeeAllUserAccount') === false) {
            $usField->addParameter('ea_us_id', $this->User->getId());
        }

        $this->ListingForm->addField(Trans::getFinanceWord('code'), $this->Field->getText('ea_code', $this->getStringParameter('ea_code')));
        $this->ListingForm->addField(Trans::getFinanceWord('description'), $this->Field->getText('ea_description', $this->getStringParameter('ea_description')));
        $this->ListingForm->addField(Trans::getFinanceWord('accountManager'), $usField);
        $this->ListingForm->addField(Trans::getFinanceWord('blocked'), $this->Field->getYesNo('ea_blocked', $this->getStringParameter('ea_blocked')));
        $this->ListingForm->addField(Trans::getFinanceWord('deleted'), $this->Field->getYesNo('ea_deleted', $this->getStringParameter('ea_deleted')));

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
            'ea_code' => Trans::getFinanceWord('code'),
            'ea_description' => Trans::getFinanceWord('description'),
            'ea_user' => Trans::getFinanceWord('user'),
            'ea_balance' => Trans::getFinanceWord('balance'),
            'ea_status' => Trans::getFinanceWord('status'),
        ]);
        # Load the data for ElectronicAccount.
        $this->ListingTable->addRows($this->loadData());
        $this->ListingTable->addColumnAttribute('ea_code', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('ea_balance', 'style', 'text-align: right;');
        $this->ListingTable->addColumnAttribute('ea_status', 'style', 'text-align: center;');
        $this->ListingTable->setViewActionByHyperlink($this->getUpdateRoute(), ['ea_id']);
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return ElectronicAccountDao::loadTotalData($this->getWhereCondition());
    }


    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        $data = ElectronicAccountDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable());

        $results = [];
        $number = new NumberFormatter($this->User);
        foreach ($data as $row) {
            $row['ea_balance'] = $row['ea_currency'] . ' ' . $number->doFormatFloat($row['ea_balance']);
            if (empty($row['ea_deleted_on']) === false) {
                $status = new LabelDark(Trans::getFinanceWord('deleted'));
            } elseif (empty($row['ea_block_on']) === false) {
                $status = new LabelWarning(Trans::getFinanceWord('blocked'));
            } else {
                $status = new LabelSuccess(Trans::getFinanceWord('active'));
            }
            $row['ea_status'] = $status;
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Function to get the where condition.
     *
     * @return array
     */
    private function getWhereCondition(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateNumericCondition('ea.ea_ss_id', $this->User->getSsId());
        $allowSeeAllAccount = $this->PageSetting->checkPageRight('AllowSeeAllUserAccount');
        if ($allowSeeAllAccount === false) {
            $wheres[] = SqlHelper::generateNumericCondition('ea.ea_us_id', $this->User->getId());
        }
        if ($allowSeeAllAccount === true && $this->isValidParameter('ea_us_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('ea.ea_us_id', $this->getIntParameter('ea_us_id'));
        }
        if ($this->isValidParameter('ea_code') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ea.ea_code', $this->getStringParameter('ea_code'));
        }
        if ($this->isValidParameter('ea_description') === true) {
            $wheres[] = SqlHelper::generateStringCondition('ea.ea_description', $this->getStringParameter('ea_description'));
        }
        if ($this->isValidParameter('ea_blocked') === true) {
            if ($this->getStringParameter('ea_blocked', 'N') === 'Y') {
                $wheres[] = SqlHelper::generateNullCondition('ea.ea_block_on', false);
            } else {
                $wheres[] = SqlHelper::generateNullCondition('ea.ea_block_on');
            }
        }
        if ($this->isValidParameter('ea_deleted') === true) {
            if ($this->getStringParameter('ea_deleted', 'N') === 'Y') {
                $wheres[] = SqlHelper::generateNullCondition('ea.ea_deleted_on', false);
            } else {
                $wheres[] = SqlHelper::generateNullCondition('ea.ea_deleted_on');
            }
        }
        return $wheres;
    }
}
