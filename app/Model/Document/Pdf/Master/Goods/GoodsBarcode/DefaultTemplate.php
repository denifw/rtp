<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Document\Pdf\Master\Goods\GoodsBarcode;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Model\Document\Pdf\AbstractBasePdf;
use Illuminate\Support\Facades\DB;

/**
 * Class to generate barcode goods
 *
 * @package    app
 * @subpackage Model\Document\Pdf\Master\Goods
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class DefaultTemplate extends AbstractBasePdf
{

    /**
     * Property to store the goods Data.
     *
     * @var array $Goods
     */
    protected $Goods = [];

    /**
     * AbstractBasePdf constructor.
     */
    public function __construct()
    {
        parent::__construct(Trans::getWord('goods') . '.pdf');
    }

    /**
     * Function to set the content to pdf.
     *
     * @return void
     */
    public function loadContent(): void
    {
        $this->loadData();
        try {
            $this->MPdf->SetHeader();
            $this->MPdf->AddPage('P', '', '', '1', '', 5, 5, 20, 5, 20, 5);
            $this->MPdf->WriteHTML($this->getBarcode());
        } catch (\Exception $e) {
            Message::throwMessage($e->getMessage());
        }
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    private function getBarcode(): string
    {
        $result = '<div style="width: 100%; text-align: center;">';
        $size = count($this->Goods);
        for ($i = 0; $i < $size; $i += 2) {
            $result .= '<div style="width: 100%; text-align: center; margin-bottom: 30px;">';
            $row = $this->Goods[$i];
            $result .= '<div class="barcodecell" style="width: 50%; text-align: center;">';
            $result .= $this->writeBarcode($row['gd_barcode'], 2, 'C128B');
            $result .= '<div>' . $row['gd_barcode'] . '</div>';
            $result .= '</div>';
            if (($i + 1) < $size) {
                $row = $this->Goods[$i + 1];
                $result .= '<div class="barcodecell" style="width: 50%; text-align: center;">';
                $result .= $this->writeBarcode($row['gd_barcode'], 2, 'C128B');
                $result .= '<div>' . $row['gd_barcode'] . '</div>';
                $result .= '</div>';
            }

            $result .= '</div>';
        }
        $result .= '</div>';

        return $result;
    }

    /**
     * Function to load the html content.
     *
     * @return void
     */
    protected function loadData(): void
    {
        $wheres = [];
        $wheres[] = '(gd.gd_ss_id = ' . $this->getIntParameter('gd_ss_id') . ')';
        $wheres[] = '(gd.gd_barcode IS NOT NULL)';
        if ($this->isValidParameter('gd_id') === true) {
            $wheres[] = '(gd.gd_id = ' . $this->getIntParameter('gd_id') . ')';
        }
        if ($this->isValidParameter('gd_rel_id') === true) {
            $wheres[] = '(gd.gd_rel_id = ' . $this->getIntParameter('gd_rel_id') . ')';
        }
        if ($this->isValidParameter('gd_gdc_id')) {
            $wheres[] = '(gd.gd_gdc_id = ' . $this->getIntParameter('gd_gdc_id') . ')';
        }
        if ($this->isValidParameter('gd_br_id')) {
            $wheres[] = '(gd.gd_br_id = ' . $this->getIntParameter('gd_br_id') . ')';
        }
        if ($this->isValidParameter('gd_sku')) {
            $wheres[] = StringFormatter::generateLikeQuery('gd_sku', $this->getStringParameter('gd_sku'));
        }
        if ($this->isValidParameter('gd_name')) {
            $wheres[] = StringFormatter::generateLikeQuery('gd_name', $this->getStringParameter('gd_name'));
        }
        if ($this->isValidParameter('gd_active')) {
            $wheres[] = '(gd.gd_active = \'' . $this->getStringParameter('gd_active') . '\')';
        }
        if ($this->isValidParameter('gd_bundling')) {
            $wheres[] = '(gd.gd_bundling = \'' . $this->getStringParameter('gd_bundling') . '\')';
        }
        if ($this->isValidParameter('gd_sn')) {
            $wheres[] = '(gd.gd_sn = \'' . $this->getStringParameter('gd_sn') . '\')';
        }
        if ($this->isValidParameter('gd_multi_sn')) {
            $wheres[] = '(gd.gd_multi_sn = \'' . $this->getStringParameter('gd_multi_sn') . '\')';
        }
        $strWhere = '';
        if (empty($wheres) === false) {
            $strWhere = ' WHERE ' . implode(' AND ', $wheres);
        }
        $query = 'SELECT gd.gd_id, gdc.gdc_name, br.br_name, gd.gd_sku, gd.gd_name,
                         gd.gd_barcode, rel.rel_name, uom.uom_name, gd.gd_active, 
                        gd.gd_sn, gd.gd_multi_sn, rel.rel_short_name,
                        gd.gd_bundling, gdu.gdu_id, gdu.gdu_length, gdu.gdu_width, gdu.gdu_height, gdu.gdu_volume
                   FROM goods AS gd 
                    INNER JOIN goods_category AS gdc ON gdc.gdc_id = gd.gd_gdc_id 
                    INNER JOIN brand AS br ON br.br_id = gd.gd_br_id 
                    INNER JOIN relation AS rel ON rel.rel_id = gd.gd_rel_id 
                    INNER JOIN unit as uom ON gd.gd_uom_id = uom.uom_id 
                    LEFT OUTER JOIN goods_unit as gdu ON gd.gd_id = gdu.gdu_gd_id AND gd.gd_uom_id = gdu.gdu_uom_id ' . $strWhere;
        # Set group by query.
        $query .= ' GROUP BY gd.gd_id, gdc.gdc_name, br.br_name, gd.gd_sku, gd.gd_name, rel.rel_name, uom.uom_name, gd.gd_active, 
                            gd.gd_sn, gd.gd_multi_sn, rel.rel_short_name, gd.gd_bundling, gdu.gdu_id, gdu.gdu_length, gdu.gdu_width, gdu.gdu_height, gdu.gdu_volume';
        $result = DB::select($query);
        if (empty($result) === false) {
            $this->Goods = DataParser::arrayObjectToArray($result);
        } else {
            Message::throwMessage(Trans::getWord('noDataFound', 'message'), 'ERROR');
        }
    }

    /**
     * Function to load the html content.
     *
     * @return string
     */
    public function loadHtmlContent(): string
    {
        return '';
    }
}
