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
 * Class to handle the ajax request fo DocumentType.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Document
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class DocumentType extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('dct.dct_code', $this->getStringParameter('search_key'));
        $wheres[] = '(dct.dct_deleted_on IS NULL)';
        $wheres[] = "(dct.dct_active = 'Y')";
        if ($this->isValidParameter('dct_dcg_id') === true) {
            $wheres[] = '(dct.dct_dcg_id = ' . $this->getIntParameter('dct_dcg_id') . ')';
        }
        if ($this->isValidParameter('dcg_code') === true) {
            $wheres[] = "(dcg.dcg_code = '" . $this->getStringParameter('dcg_code') . "')";
        }
        if ($this->isValidParameter('dct_master') === true) {
            $wheres[] = "(dct.dct_master = '" . $this->getStringParameter('dct_master') . "')";
        }
        $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT dct.dct_id, dct.dct_description
                    FROM document_type as dct INNER JOIN
                    document_group as dcg ON dct.dct_dcg_id = dcg.dcg_id' . $strWhere;
        $query .= ' ORDER BY dct.dct_description, dct.dct_id';
        $query .= ' LIMIT 30 OFFSET 0';

        return $this->loadDataForSingleSelect($query, 'dct_description', 'dct_id', true);
    }
}
