<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Ano Surino <ano@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Ajax\Job\Warehouse\Bundling;

use App\Frame\Formatter\NumberFormatter;
use App\Frame\Mvc\AbstractBaseAjaxModel;
use App\Model\Dao\Job\Warehouse\Bundling\JobBundlingMaterialDao;
use App\Model\Dao\Master\Goods\GoodsDao;

/**
 * Class to handle the ajax request fo JobBundlingMaterial.
 *
 * @package    app
 * @subpackage Model\Ajax\Job\Warehouse\Bundling
 * @author     Ano Surino <ano@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class JobBundlingMaterial extends AbstractBaseAjaxModel
{

    /**
     * Function to load data by reference id
     *
     * @return array
     */
    public function getByIdForUpdate(): array
    {
        $result = [];
        if ($this->isValidParameter('jbm_id') === true) {
            $wheres = [];
            $wheres[] = '(jbm.jbm_id = ' . $this->getIntParameter('jbm_id') . ')';
            $data = JobBundlingMaterialDao::loadData($wheres);
            if (count($data) === 1) {
                $gdDao = new GoodsDao();
                $result = $data[0];
                $result['jbm_goods'] = $gdDao->formatFullName($result['jbm_gd_category'], $result['jbm_gd_brand'], $result['jbm_gd_name']);
                $number = new NumberFormatter();
                $result['jbm_quantity_number'] = $number->doFormatFloat($result['jbm_quantity']);
                $result['jbm_weight_number'] = $number->doFormatFloat($result['jbm_weight']);
                $result['jbm_length_number'] = $number->doFormatFloat($result['jbm_length']);
                $result['jbm_width_number'] = $number->doFormatFloat($result['jbm_width']);
                $result['jbm_height_number'] = $number->doFormatFloat($result['jbm_height']);
                if (empty($result['jbm_gdt_id']) === false) {
                    $result['jbm_condition'] = 'N';
                } else {
                    $result['jbm_condition'] = 'Y';
                }
            }
        }

        return $result;
    }
}
