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
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo Service.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Service
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Service extends AbstractBaseAjaxModel
{

    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('ssr_ss_id')) {
            $subWhere = [];
            $subWhere[] = "(ssr_active = 'Y')";
            $subWhere[] = '(ssr_deleted_on IS NULL)';
            $subWhere[] = '(ssr_ss_id = ' . $this->getIntParameter('ssr_ss_id') . ')';
            $strSubWhere = ' WHERE ' . implode(' AND ', $subWhere);
            $wheres[] = '(srv_id IN (SELECT ssr_srv_id
                                        FROM system_service ' . $strSubWhere . '
                                        GROUP BY ssr_srv_id))';
        }
        $wheres[] = SqlHelper::generateLikeCondition('srv_name', $this->getStringParameter('search_key'));
        $wheres[] = '(srv_deleted_on IS NULL)';
        $wheres[] = "(srv_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT srv_id, srv_name, srv_code
                        FROM service ' . $strWhere;
        $query .= ' GROUP BY srv_id, srv_name, srv_code';
        $query .= ' ORDER BY srv_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'srv_name', 'srv_id');
    }

    /**
     * Function to load page
     *
     * @return array
     */
    public function loadServiceForGenerateSerialNumber(): array
    {
        $wheres = [];
        if ($this->isValidParameter('ssr_ss_id')) {
            $subWhere = [];
            $subWhere[] = "(ssr_active = 'Y')";
            $subWhere[] = '(ssr_deleted_on IS NULL)';
            $subWhere[] = '(ssr_ss_id = ' . $this->getIntParameter('ssr_ss_id') . ')';
            $strSubWhere = ' WHERE ' . implode(' AND ', $subWhere);
            $wheres[] = '(srv_id IN (SELECT ssr_srv_id FROM system_service ' . $strSubWhere . ' GROUP BY ssr_srv_id))';
        }
        if ($this->isValidParameter('sn_rel_id')) {
            $subWhere = [];
            $subWhere[] = "(sn_active = 'Y')";
            $subWhere[] = '(sn_deleted_on IS NULL)';

            $subWhere[] = '(sn_rel_id = ' . $this->getIntParameter('sn_rel_id') . ')';
            $strSubWhere = ' WHERE ' . implode(' AND ', $subWhere);
            $wheres[] = '(srv_id NOT IN (SELECT (CASE WHEN sn_srv_id IS NULL THEN 0 ELSE sn_srv_id END) FROM serial_number ' . $strSubWhere . ' GROUP BY sn_srv_id))';
        }
        $wheres[] = SqlHelper::generateLikeCondition('srv_name', $this->getStringParameter('search_key'));
        $wheres[] = '(srv_deleted_on IS NULL)';
        $wheres[] = "(srv_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT srv_id, srv_name
                        FROM service ' . $strWhere;
        $query .= ' GROUP BY srv_id, srv_name';
        $query .= ' ORDER BY srv_name';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'srv_name', 'srv_id');
    }


    /**
     * @return array
     */
    public function loadServiceFromSsId(): array
    {
        $wheres = [];
        if ($this->isValidParameter('ssr_srv_id') === true) {
            $wheres[] = '(ssr.ssr_ss_id =' . $this->getIntParameter('ssr_srv_id') . ' )';
        }
        $wheres[] = SqlHelper::generateLikeCondition('srv_name', $this->getStringParameter('search_key'));
        $wheres[] = '(srv_deleted_on IS NULL)';
        $wheres[] = "(srv_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT distinct(srv.srv_name), srv.srv_id, ssr.ssr_ss_id, srv.srv_code
                        FROM service as srv
                         INNER JOIN system_service as ssr on ssr.ssr_srv_id = srv.srv_id' . $strWhere;
        $query .= ' GROUP BY srv_id,ssr.ssr_ss_id';
        $query .= ' ORDER BY srv_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'srv_name', 'srv_id');
    }

    /**
     * @return array
     */
    public function loadSoService(): array
    {
        if ($this->isValidParameter('sos_so_id') === true) {
            $wheres = [];
            $wheres[] = SqlHelper::generateLikeCondition('srv.srv_name', $this->getStringParameter('search_key'));
            $wheres[] = SqlHelper::generateNumericCondition('sos.sos_so_id', $this->getIntParameter('sos_so_id'));
            $wheres[] = SqlHelper::generateStringCondition('srt.srt_active', 'Y');
            $wheres[] = SqlHelper::generateStringCondition('srv.srv_active', 'Y');
            $wheres[] = '(sos.sos_deleted_on IS NULL)';
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT srv.srv_id, srv.srv_name
                        FROM sales_order_service as sos
                         INNER JOIN service_term as srt on sos.sos_srt_id = srt.srt_id
                         INNER JOIN service as srv ON srv.srv_id = srt.srt_srv_id ' . $strWhere;
            $query .= ' ORDER BY srv.srv_name, srv.srv_id';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'srv_name', 'srv_id');
        }
        return [];
    }
}
