<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\CustomerService;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\CustomerService\SalesOrderIssueDao;
use mysql_xdevapi\SqlStatement;

/**
 * Class to handle the ajax request fo SalesOrderIssue.
 *
 * @package    app
 * @subpackage Model\Ajax\CustomerService
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class SalesOrderIssue extends AbstractBaseAjaxModel
{

    /**
     * Function to load the data for single select for SalesOrderIssue
     *
     * @return array
     */
    public function loadSingleSelectData(): array
    {
        $wheres = [];
        $wheres[] = SqlHelper::generateLikeCondition('soi_number', $this->getStringParameter('search_key'));


        return SalesOrderIssueDao::loadSingleSelectData($wheres);
    }
}
