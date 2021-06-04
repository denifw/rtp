<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\System\Document;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\System\Document\DocumentTemplateDao;

/**
 * Class to handle the ajax request fo DocumentTemplate.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Document
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class DocumentTemplate extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for DocumentTemplate
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dt.dt_description', $this->getStringParameter('search_key'));
        }
        if ($this->isValidParameter('dt_dtt_id')) {
            $wheres[] = SqlHelper::generateStringCondition('dt.dt_dtt_id', $this->getStringParameter('dt_dtt_id'));
        }
        return DocumentTemplateDao::loadSingleSelectData('dt_description', $wheres);
    }
}
