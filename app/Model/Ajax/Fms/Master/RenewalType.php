<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Model\Ajax\Fms\Master;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo RenewalType.
 *
 * @package    app
 * @subpackage Model\Ajax\Fms\Master
 * @author     Ano Surino <ano@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class RenewalType extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        if ($this->isValidParameter('rnt_ss_id') === true) {
            $wheres = [];
            $wheres[] = '(rnt.rnt_ss_id = ' . $this->getIntParameter('rnt_ss_id') . ')';
            $wheres[] = StringFormatter::generateLikeQuery('rnt_name', $this->getStringParameter('search_key'));
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
            $query = 'SELECT rnt.rnt_id, rnt.rnt_name
                      FROM renewal_type AS rnt' . $strWhere;
            $query .= ' ORDER BY rnt.rnt_name';
            $query .= ' LIMIT 30 OFFSET 0';

            return $this->loadDataForSingleSelect($query, 'rnt_name', 'rnt_id');
        }

        return [];
    }
}
