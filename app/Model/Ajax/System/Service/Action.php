<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System\Service;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Service\ActionDao;

/**
 * Class to handle the ajax request fo Action.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Service
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Action extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('ac_description', $this->getStringParameter('search_key'));
        $wheres[] = '(ac_deleted_on IS NULL)';
        if ($this->isValidParameter('ac_srt_id') === true) {
            $wheres[] = '(ac_srt_id = ' . $this->getIntParameter('ac_srt_id') . ')';
        }

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT ac_id, ac_description, ac_order
                    FROM action' . $strWhere;
        $query .= ' ORDER BY ac_order';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'ac_description', 'ac_id');
    }

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('ac_id') === true) {
            return ActionDao::getByReference($this->getIntParameter('ac_id'));
        }

        return [];
    }

}
