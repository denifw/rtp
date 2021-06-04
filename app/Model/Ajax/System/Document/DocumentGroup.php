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
use App\Model\Dao\System\Document\DocumentGroupDao;

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
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dcg_description', $this->getStringParameter('search_key'));
        }
        $wheres[] = SqlHelper::generateStringCondition('dcg_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('dcg_deleted_on');
        return DocumentGroupDao::loadSingleSelectData('dcg_description', $wheres);
    }
}
