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
 * Class to handle the ajax request fo City.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Location
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class City extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('cty_name', $this->getStringParameter('search_key'));
        $wheres[] = '(cty_deleted_on IS NULL)';
        $wheres[] = "(cty_active = 'Y')";
        if ($this->isValidParameter('cty_cnt_id') === true) {
            $wheres[] = '(cty_cnt_id = ' . $this->getIntParameter('cty_cnt_id') . ')';
        }
        if ($this->isValidParameter('cty_stt_id') === true) {
            $wheres[] = '(cty_stt_id = ' . $this->getIntParameter('cty_stt_id') . ')';
        }

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT cty_id, cty_name
                    FROM city' . $strWhere;
        $query .= ' ORDER BY cty_name';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'cty_name', 'cty_id');
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadCompleteSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('cty.cty_name', $this->getStringParameter('search_key'));
        $wheres[] = '(cty.cty_deleted_on IS NULL)';
        $wheres[] = "(cty.cty_active = 'Y')";
        if ($this->isValidParameter('cty_cnt_id') === true) {
            $wheres[] = '(cty.cty_cnt_id = ' . $this->getIntParameter('cty_cnt_id') . ')';
        }
        if ($this->isValidParameter('cty_stt_id') === true) {
            $wheres[] = '(cty.cty_stt_id = ' . $this->getIntParameter('cty_stt_id') . ')';
        }

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT cty.cty_id, cty.cty_name || \', \' || stt.stt_name || \', \' || cnt.cnt_name AS cty_name
                    FROM city AS cty INNER JOIN
                         state AS stt ON stt.stt_id = cty.cty_stt_id INNER JOIN
                         country AS cnt ON cnt.cnt_id = stt_cnt_id' . $strWhere;
        $query .= ' ORDER BY cty.cty_name';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'cty_name', 'cty_id');
    }
}
