<?php

/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System\Service;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Service\ServiceTermDao;

/**
 * Class to handle the ajax request fo ServiceTerm.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Service
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class ServiceTerm extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('srt_srv_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('srt.srt_srv_id', $this->getIntParameter('srt_srv_id'));
        }
        if ($this->isValidParameter('srv_code') === true) {
            $wheres[] = SqlHelper::generateLowerStringCondition('srv.srv_code', $this->getStringParameter('srv_code'));
        }
        if ($this->isValidParameter('srt_container') === true) {
            $wheres[] = SqlHelper::generateStringCondition('srt.srt_container', $this->getStringParameter('srt_container'));
        }
        if ($this->isValidParameter('ssr_ss_id')) {
            $subWhere = [];
            $subWhere[] = SqlHelper::generateStringCondition('ssr_active', 'Y');
            $subWhere[] = SqlHelper::generateNullCondition('ssr_deleted_on');
            $subWhere[] = SqlHelper::generateNumericCondition('ssr_ss_id', $this->getIntParameter('ssr_ss_id'));
            $strSubWhere = ' WHERE ' . implode(' AND ', $subWhere);
            $wheres[] = '(srt.srt_id IN (SELECT ssr_srt_id FROM system_service ' . $strSubWhere . ' GROUP BY ssr_srt_id))';
        }
        $wheres[] = SqlHelper::generateLikeCondition('srt.srt_name', $this->getStringParameter('search_key'));
        return ServiceTermDao::loadSingleSelectData($wheres);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadServiceTermInboundOutbound(): array
    {
        $wheres = [];
        if ($this->isValidParameter('ssr_ss_id')) {
            $subWhere = [];
            $subWhere[] = SqlHelper::generateStringCondition('ssr_active', 'Y');
            $subWhere[] = SqlHelper::generateNullCondition('ssr_deleted_on');
            $subWhere[] = SqlHelper::generateNumericCondition('ssr_ss_id', $this->getIntParameter('ssr_ss_id'));
            $strSubWhere = ' WHERE ' . implode(' AND ', $subWhere);
            $wheres[] = '(srt.srt_id IN (SELECT ssr_srt_id FROM system_service ' . $strSubWhere . ' GROUP BY ssr_srt_id))';
        }
        $wheres[] = "(srt.srt_route IN ('joWhInbound', 'joWhOutbound'))";
        $wheres[] = SqlHelper::generateLikeCondition('srt.srt_name', $this->getStringParameter('search_key'));
        return ServiceTermDao::loadSingleSelectData($wheres);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectAutoComplete(): array
    {
        $wheres = [];
        if ($this->isValidParameter('srt_srv_id') === true) {
            $wheres[] = SqlHelper::generateNumericCondition('srt.srt_srv_id', $this->getIntParameter('srt_srv_id'));
        }
        if ($this->isValidParameter('srv_code') === true) {
            $wheres[] = SqlHelper::generateLowerStringCondition('srv.srv_code', $this->getStringParameter('srv_code'));
        }
        if ($this->isValidParameter('ssr_ss_id')) {
            $subWhere = [];
            $subWhere[] = SqlHelper::generateStringCondition('ssr_active', 'Y');
            $subWhere[] = SqlHelper::generateNullCondition('ssr_deleted_on');
            $subWhere[] = SqlHelper::generateNumericCondition('ssr_ss_id', $this->getIntParameter('ssr_ss_id'));
            $strSubWhere = ' WHERE ' . implode(' AND ', $subWhere);

            $wheres[] = '(srt.srt_id IN (SELECT ssr_srt_id FROM system_service ' . $strSubWhere . ' GROUP BY ssr_srt_id))';
        }
        $wheres[] = SqlHelper::generateLikeCondition('srt.srt_name', $this->getStringParameter('search_key'));
        return ServiceTermDao::loadSingleSelectData($wheres);
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadDataWithService(): array
    {
        $wheres = [];
        if ($this->isValidParameter('srt_srv_id') === true) {
            $wheres[] = '(srt_srv_id = ' . $this->getIntParameter('srt_srv_id') . ')';
        }
        if ($this->isValidParameter('ssr_ss_id')) {
            $subWhere = [];
            $subWhere[] = "(ssr_active = 'Y')";
            $subWhere[] = '(ssr_deleted_on IS NULL)';
            $subWhere[] = '(ssr_ss_id = ' . $this->getIntParameter('ssr_ss_id') . ')';
            $strSubWhere = ' WHERE ' . implode(' AND ', $subWhere);

            $wheres[] = '(srt.srt_srt_id IN (SELECT srt_srt_id FROM system_service ' . $strSubWhere . ' GROUP BY srt_srt_id))';
        }
        $wheres[] = SqlHelper::generateLikeCondition('srt_name', $this->getStringParameter('search_key'));
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT srt_id, srt_srv_id, srt_order, srv.srv_name || \' \' || srt_name as name
                    FROM service_term as srt INNER JOIN
                    service as srv ON srv.srv_id = srt.srt_srv_id ' . $strWhere;
        $query .= ' GROUP BY srt_id, srt_srv_id, srt_order, srt_name, srv.srv_name';
        $query .= ' ORDER BY srt_srv_id, srt_order, srt_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'name', 'srt_id');
    }

}
