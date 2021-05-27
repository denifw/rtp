<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Crm\Quotation;

/**
 * Class to handle the ajax request fo SalesQuotation.
 *
 * @package    app
 * @subpackage Model\Ajax\Crm\Quotation
 * @author     Deni Firdaus Waruwu <deni.fw@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class SalesQuotation extends Quotation
{
    /**
     * Base model for ajax
     *
     * @param array $parameters .
     */
    public function __construct(array $parameters)
    {
        parent::__construct($parameters);
        $this->setParameter('qt_type', 'S');
    }
}
