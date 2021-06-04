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
use App\Model\Dao\System\Document\DocumentTemplateTypeDao;

/**
 * Class to handle the ajax request fo DocumentTemplateType.
 *
 * @package    app
 * @subpackage Model\Ajax\System\Document
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class DocumentTemplateType extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for DocumentTemplateType
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        if ($this->isValidParameter('search_key') === true) {
            $wheres[] = SqlHelper::generateLikeCondition('dtt_description', $this->getStringParameter('search_key'));
        }
        return DocumentTemplateTypeDao::loadSingleSelectData('dtt_description', $wheres);
    }
}
