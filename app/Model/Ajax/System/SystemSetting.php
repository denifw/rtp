<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo SystemSetting.
 *
 * @package    app
 * @subpackage Model\Ajax\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemSetting extends AbstractBaseAjaxModel
{
    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('ss_relation', $this->getStringParameter('search_key'));
        $wheres[] = '(ss_deleted_on IS NULL)';
        $wheres[] = "(ss_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT ss_id, ss_relation
                        FROM system_setting ' . $strWhere;
        $query .= ' ORDER BY ss_relation';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'ss_relation', 'ss_id');
    }

}
