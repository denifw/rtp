<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Custom\ksi\Viewer\Master\Goods;

use App\Frame\Gui\Table;
use App\Frame\Formatter\Trans;

/**
 * Class to handle the creation of detail Goods page
 *
 * @package    app
 * @subpackage Custom\ksi\Viewer\Master\Goods
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Goods extends \App\Model\Viewer\Master\Goods\Goods
{
    /**
     * Load table serial number.
     *
     * @return Table
     */
    protected function loadTableSerialNumber(): Table
    {
        $tbl = parent::loadTableSerialNumber();
        $tbl->addColumnAfter('jid_serial_number', 'jid_weight', Trans::getWord('weight') . ' (KG)');
        $tbl->setColumnType('jid_weight', 'float');
        return $tbl;
    }
}
