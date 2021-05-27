<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System\Location;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo State.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class State extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('stt_name', $this->getStringParameter('search_key'));
        $wheres[] = '(stt_deleted_on IS NULL)';
        $wheres[] = "(stt_active = 'Y')";
        if ($this->isValidParameter('stt_cnt_id') === true) {
            $wheres[] = '(stt_cnt_id = ' . $this->getIntParameter('stt_cnt_id') . ')';
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT stt_id, stt_name
                    FROM state' . $strWhere;
        $query .= ' ORDER BY stt_name';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'stt_name', 'stt_id');
    }
}
