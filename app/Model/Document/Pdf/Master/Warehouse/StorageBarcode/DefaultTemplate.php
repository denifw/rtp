<?php

/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Document\Pdf\Master\Warehouse\StorageBarcode;

use App\Frame\Exceptions\Message;
use App\Frame\Formatter\Trans;
use App\Model\Dao\Master\WarehouseStorageDao;
use App\Model\Document\Pdf\AbstractBasePdf;
use Exception;

/**
 * Class to generate barcode storage
 *
 * @package    app
 * @subpackage Model\Document\Pdf\Warehouse
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class DefaultTemplate extends AbstractBasePdf
{

    /**
     * Property to store the storage Data.
     *
     * @var array $Storage
     */
    protected $Storage = [];

    /**
     * AbstractBasePdf constructor.
     */
    public function __construct()
    {
        parent::__construct(Trans::getWord('storageBarcode') . '.pdf');
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
        } catch (Exception $e) {
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
        $size = count($this->Storage);
        for ($i = 0; $i < $size; $i += 2) {
            $result .= '<div style="width: 100%; text-align: center; margin-bottom: 30px;">';
            $row = $this->Storage[$i];
            $result .= '<div class="barcodecell" style="width: 50%; text-align: center;">';
            $result .= $this->writeBarcode($row['whs_name'], 2, 'C128B');
            $result .= '<div>' . $row['whs_name'] . '</div>';
            $result .= '</div>';
            if (($i + 1) < $size) {
                $row = $this->Storage[$i + 1];
                $result .= '<div class="barcodecell" style="width: 50%; text-align: center;">';
                $result .= $this->writeBarcode($row['whs_name'], 2, 'C128B');
                $result .= '<div>' . $row['whs_name'] . '</div>';
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
     * @return string
     */
    private function manualMOLStorageBarcode(): string
    {
        $result = '<div style="width: 100%; text-align: center;">';
        $names = ['A-01-01','PN-2009001', 'SA20090861', 'SA20090862', 'SA20090863', 'SM20080063'];
        foreach ($names as $name) {
            $result .= '<div style="width: 100%; text-align: center; margin-bottom: 80px;">';
            $result .= '<div class="barcodecell" style="width: 100%; text-align: center;">';
            $result .= $this->writeBarcode($name, 2, 'C128B');
            $result .= '<div style = "font-size: 20pt; font-weight: bold;">' . $name . '</div>';
            $result .= '</div>';

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
        if ($this->isValidParameter('wh_id') === false) {
            Message::throwMessage('Invalid parameter for wh_id.');
        }
        $wheres = [];
        $wheres[] = '(wh.wh_id = ' . $this->getIntParameter('wh_id') . ')';
        if ($this->isValidParameter('whs_id') === true) {
            $wheres[] = '(whs.whs_id = ' . $this->getIntParameter('whs_id') . ')';
        }
        $this->Storage = WarehouseStorageDao::loadData($wheres);
        if (empty($this->Storage) === true) {
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
