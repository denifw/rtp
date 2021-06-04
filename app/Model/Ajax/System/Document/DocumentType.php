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
use App\Model\Dao\System\Document\DocumentTypeDao;

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
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dct.dct_code', $this->getStringParameter('search_key'));
        }
        $wheres[] = SqlHelper::generateStringCondition('dct.dct_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('dct.dct_deleted_on');
        if ($this->isValidParameter('dct_dcg_id') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dct.dct_dcg_id', $this->getStringParameter('dct_dcg_id'));
        }
        if ($this->isValidParameter('dcg_code') === true) {
            $wheres[] = SqlHelper::generateStringCondition('dcg.dcg_code', $this->getStringParameter('dcg_code'));
        }
        return DocumentTypeDao::loadSingleSelectData('dct_description', $wheres);
    }
}
