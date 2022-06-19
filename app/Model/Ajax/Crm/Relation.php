<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Crm;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Crm\RelationDao;

/**
 * Class to handle the ajax request fo Relation.
 *
 * @package    app
 * @subpackage Model\Ajax\Crm\Relation
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class Relation extends AbstractBaseAjaxModel
{
    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('rel_ss_id') === true) {
            $helper = new SqlHelper();
            $helper->addOrLikeWhere(['rel.rel_short_name', 'rel.rel_name'], $this->getStringParameter('search_key'));
            $helper->addStringWhere('rel.rel_ss_id', $this->getStringParameter('rel_ss_id'));
            $helper->addStringWhere('rel.rel_id', $this->getStringParameter('rel_id'));
            $helper->addStringWhere('rel.rel_active', $this->getStringParameter('rel_active'));
            $helper->addNullWhere('rel.rel_deleted_on');
            return RelationDao::loadSingleSelectData('rel_name', $helper);
        }

        return [];
    }

}
