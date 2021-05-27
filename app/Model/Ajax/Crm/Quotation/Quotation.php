<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Crm\Quotation;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Crm\Quotation\QuotationDao;

/**
 * Class to handle the ajax request fo Quotation.
 *
 * @package    app
 * @subpackage Model\Ajax\Crm\Quotation
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Quotation extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for Quotation
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('qt_ss_id') === false) {
            return [];
        }
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('qt.qt_number', $this->getStringParameter('search_key'));
        $wheres[] = SqlHelper::generateNumericCondition('qt.qt_ss_id', $this->getIntParameter('qt_ss_id'));
        if ($this->isValidParameter('qt_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('qt.qt_rel_id', $this->getIntParameter('qt_rel_id'));
        }
        if ($this->isValidParameter('qt_type') === true) {
            $wheres[] = SqlHelper::generateStringCondition('qt.qt_type', $this->getStringParameter('qt_type'));
        }
        if ($this->isValidParameter('qt_srv_id') === true) {
            $wheres[] = '(qt.qt_id IN (select qs_qt_id 
                                        FROM quotation_service 
                                        WHERE (qs_srv_id = ' . $this->getIntParameter('qt_srv_id') . ') AND (qs_deleted_on IS NULL)
                                        GROUP BY qs_qt_id))';
        }
        $wheres[] = '(qt.qt_deleted_on IS NULL)';

        return QuotationDao::loadSingleSelectData($wheres);
    }

    /**
     * Function to load the data for single select for Quotation
     *
     * @return array
     */
    public function loadUnSubmitData(): array
    {
        if ($this->isValidParameter('qt_ss_id') === false) {
            return [];
        }
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('qt.qt_number', $this->getStringParameter('search_key'));
        $wheres[] = SqlHelper::generateNumericCondition('qt.qt_ss_id', $this->getIntParameter('qt_ss_id'));
        if ($this->isValidParameter('qt_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('qt.qt_rel_id', $this->getIntParameter('qt_rel_id'));
        }
        if ($this->isValidParameter('qt_type') === true) {
            $wheres[] = SqlHelper::generateStringCondition('qt.qt_type', $this->getStringParameter('qt_type'));
        }
        if ($this->isValidParameter('qt_srv_id') === true) {
            $wheres[] = '(qt.qt_id IN (select qs_qt_id 
                                        FROM quotation_service 
                                        WHERE (qs_srv_id = ' . $this->getIntParameter('qt_srv_id') . ') AND (qs_deleted_on IS NULL)
                                        GROUP BY qs_qt_id))';
        }
        $wheres[] = '(qt.qt_deleted_on IS NULL)';
        $wheres[] = '((qt.qt_qts_id IS NULL) OR ((qt.qt_qts_id IS NOT NULL) AND (qts.qts_deleted_on IS NOT NULL)))';

        return QuotationDao::loadSingleSelectData($wheres);
    }

    /**
     * Function to load the data for single select for Quotation
     *
     * @return array
     */
    public function loadApprovedData(): array
    {
        if ($this->isValidParameter('qt_ss_id') === false) {
            return [];
        }
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('qt.qt_number', $this->getStringParameter('search_key'));
        $wheres[] = SqlHelper::generateNumericCondition('qt.qt_ss_id', $this->getIntParameter('qt_ss_id'));
        if ($this->isValidParameter('qt_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('qt.qt_rel_id', $this->getIntParameter('qt_rel_id'));
        }
        if ($this->isValidParameter('qt_type') === true) {
            $wheres[] = SqlHelper::generateStringCondition('qt.qt_type', $this->getStringParameter('qt_type'));
        }
        if ($this->isValidParameter('qt_srv_id') === true) {
            $wheres[] = '(qt.qt_id IN (select qs_qt_id 
                                        FROM quotation_service 
                                        WHERE (qs_srv_id = ' . $this->getIntParameter('qt_srv_id') . ') AND (qs_deleted_on IS NULL)
                                        GROUP BY qs_qt_id))';
        }
        $wheres[] = '(qt.qt_deleted_on IS NULL)';
        $wheres[] = '(qt.qt_approve_on IS NOT NULL)';

        return QuotationDao::loadSingleSelectData($wheres);
    }

    /**
     * Function to load the data for single select for Quotation
     *
     * @return array
     */
    public function loadQuotationSelectTableData(): array
    {
        if ($this->isValidParameter('qt_ss_id') === false) {
            return [];
        }
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('qt.qt_number', $this->getStringParameter('search_key'));
        $wheres[] = SqlHelper::generateNumericCondition('qt.qt_ss_id', $this->getIntParameter('qt_ss_id'));
        if ($this->isValidParameter('qt_rel_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('qt.qt_rel_id', $this->getIntParameter('qt_rel_id'));
        }
        if ($this->isValidParameter('qt_type') === true) {
            $wheres[] = SqlHelper::generateStringCondition('qt.qt_type', $this->getStringParameter('qt_type'));
        }
        if ($this->isValidParameter('qt_deal_name') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dl.dl_name', $this->getStringParameter('qt_deal_name'));
        }
        if ($this->isValidParameter('qt_srv_id') === true) {
            $wheres[] = '(qt.qt_id IN (SELECT qs_qt_id
                                       FROM quotation_service
                                       WHERE (qs_srv_id = ' . $this->getIntParameter('qt_srv_id') . ') AND (qs_deleted_on IS NULL)))';
        }
        if ($this->isValidParameter('qt_srv_ids') === true) {
            $wheres[] = '(qt.qt_id IN (SELECT qs_qt_id 
                                        FROM quotation_service 
                                        WHERE (qs_srv_id  IN (' . $this->getStringParameter('qt_srv_ids') . ')) AND (qs_deleted_on IS NULL)
                                        GROUP BY qs_qt_id))';
        }
        $wheres[] = '(qt.qt_deleted_on IS NULL)';
        $wheres[] = '(qt.qt_approve_on IS NOT NULL)';

        return QuotationDao::loadData($wheres);
    }

}
