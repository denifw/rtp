<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Setting\Action;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Setting\Action\SystemActionEventDao;

/**
 * Class to handle the ajax request fo SystemActionEvent.
 *
 * @package    app
 * @subpackage Model\Ajax\Setting\Action
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemActionEvent extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = StringFormatter::generateLikeQuery('sae.sae_description', $this->getStringParameter('search_key'));
        $wheres[] = '(sae.sae_deleted_on IS NULL)';
        $wheres[] = "(sae.sae_active = 'Y')";
        if($this->isValidParameter('sac_ss_id') === true) {
            $wheres[] = '(sac.sac_ss_id = '.$this->getIntParameter('sac_ss_id').')';
        }
        if($this->isValidParameter('sac_ac_id') === true) {
            $wheres[] = '(sac.sac_ac_id = '.$this->getIntParameter('sac_ac_id').')';
        }

        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT sae.sae_id, sae.sae_description
                    FROM system_action_event as sae INNER JOIN
                    system_action as sac ON sae.sae_sac_id = sac.sac_id ' . $strWhere;
        $query .= ' ORDER BY sae_order';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'sae_description', 'sae_id');
    }

    /**
     * Function to load the data for update modal
     *
     * @return array
     */
    public function getByReference(): array
    {
        if ($this->isValidParameter('sae_id') === true) {
            return SystemActionEventDao::getByReference($this->getIntParameter('sae_id'));
        }

        return [];
    }

}
