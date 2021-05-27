<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Setting\DashboardDetailDao;

/**
 * Class to handle the ajax request fo DashboardItem.
 *
 * @package    app
 * @subpackage Model\Ajax\Setting
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class DashboardItem extends AbstractBaseAjaxModel
{
    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadDashboardItemByUserGroup(): array
    {
        if ($this->isValidParameter('usg_ss_id') === true && $this->isValidParameter('ugd_ump_id')) {
            $wheres = [];
            $wheres[] = StringFormatter::generateLikeQuery('dsi_title', $this->getStringParameter('search_key'));
            $wheres[] = '(dsi_deleted_on IS NULL)';
            $wheres[] = '(ugds.ugds_deleted_on IS NULL)';
            $wheres[] = '(ugds.ugds_usg_id IN (SELECT ug.usg_id
                                                  FROM   user_group_detail as ugd INNER JOIN
                                                         user_group as ug ON ug.usg_id = ugd.ugd_usg_id
                                                  WHERE  (ugd.ugd_deleted_on IS NULL) AND ((ug.usg_ss_id = ' . $this->getIntParameter('usg_ss_id') . ') OR (ug.usg_ss_id IS NULL)) AND 
                                                         (ugd.ugd_ump_id = ' . $this->getIntParameter('ugd_ump_id') . ') AND (ug.usg_deleted_on IS NULL) AND (ug.usg_active = \'Y\')
														GROUP BY ug.usg_id))';
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT dsi.dsi_id, dsi.dsi_title
                      FROM   dashboard_item AS dsi INNER JOIN
                             user_group_dashboard_item AS ugds ON ugds.ugds_dsi_id = dsi.dsi_id INNER JOIN
                             user_group AS usg ON usg.usg_id = ugds.ugds_usg_id' . $strWhere;
            $query .= ' GROUP BY dsi.dsi_id, dsi.dsi_title';
            $query .= ' ORDER BY dsi_title';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'dsi_title', 'dsi_id');
        }

        return [];
    }

    /**
     * Function to load the data for modal form
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('dsi_id') === true) {
            $number = new NumberFormatter();
            $tempResult = DashboardDetailDao::getByReference($this->getIntParameter('dsi_id'));
            if (empty($tempResult) === false) {
                $result = $tempResult;
                $result['dsi_order_number'] = $number->doFormatCurrency($result['dsi_order']);

                return $result;
            }

            return [];
        }

        return [];
    }

    /**
     * Function to load the data for modal form delete
     *
     * @return array
     */
    public function getByReferenceForDelete(): array
    {
        $result = [];
        if ($this->isValidParameter('dsi_id') === true) {
            $number = new NumberFormatter();
            $dsiData = DashboardDetailDao::getByReference($this->getIntParameter('dsi_id'));
            if (empty($dsiData) === false) {
                $keys = array_keys($dsiData);
                foreach ($keys as $key) {
                    if ($key === 'dsi_order') {
                        $result[$key . '_del_number'] = $number->doFormatCurrency($dsiData[$key]);
                    }
                    $result[$key . '_del'] = $dsiData[$key];
                }
            }
        }

        return $result;
    }

}
