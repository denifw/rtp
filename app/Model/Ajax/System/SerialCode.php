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
 * Class to handle the ajax request fo SerialCode.
 *
 * @package    app
 * @subpackage Model\Ajax\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SerialCode extends AbstractBaseAjaxModel
{
    /**
     * Function to load page
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('sc_description', $this->getStringParameter('search_key'));
        $wheres[] = '(sc_deleted_on IS NULL)';
        $wheres[] = "(sc_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sc_id, sc_description
                        FROM serial_code ' . $strWhere;
        $query .= ' ORDER BY sc_description';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'sc_description', 'sc_id');
    }

}
