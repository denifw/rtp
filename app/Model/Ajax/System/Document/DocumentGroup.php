<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Ajax\System\Document;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;

/**
 * Class to handle the ajax request fo DocumentGroup.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Document
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DocumentGroup extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('dcg_description', $this->getStringParameter('search_key'));
        $wheres[] = '(dcg_deleted_on IS NULL)';
        $wheres[] = "(dcg_active = 'Y')";
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT dcg_id, dcg_description
                    FROM document_group' . $strWhere;
        $query .= ' ORDER BY dcg_description,dcg_id';
        $query .= ' LIMIT 30 OFFSET 0';
        return $this->loadDataForSingleSelect($query, 'dcg_description', 'dcg_id');
    }
}
