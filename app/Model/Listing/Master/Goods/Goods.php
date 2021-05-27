<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Model\Listing\Master\Goods;

use App\Frame\Formatter\StringFormatter;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\PdfButton;
use App\Frame\Gui\Icon;
use App\Frame\Mvc\AbstractListingModel;
use App\Model\Dao\Master\Goods\GoodsDao;

/**
 * Class to manage the creation of the listing Goods page.
 *
 * @package    app
 * @subpackage Model\Listing\Master\Goods
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 spada-informatika.com
 */
class Goods extends AbstractListingModel
{

    /**
     * Goods constructor that will be called when we initiate the class.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'goods');
        $this->setParameters($parameters);
    }

    /**
     * Abstract function to load search form.
     *
     * @return void
     */
    public function loadSearchForm(): void
    {
        $relationField = $this->Field->getSingleSelect('relation', 'rel_name', $this->getStringParameter('rel_name'), 'loadGoodsOwnerData');
        $relationField->setHiddenField('gd_rel_id', $this->getIntParameter('gd_rel_id'));
        $relationField->addParameter('rel_ss_id', $this->User->getSsId());
        $relationField->setEnableNewButton(false);
        $goodsCategoryField = $this->Field->getSingleSelect('goodsCategory', 'gdc_name', $this->getStringParameter('gdc_name'));
        $goodsCategoryField->setHiddenField('gd_gdc_id', $this->getIntParameter('gd_gdc_id'));
        $goodsCategoryField->addParameter('gdc_ss_id', $this->User->getSsId());
        $goodsCategoryField->setEnableNewButton(false);
        $brandField = $this->Field->getSingleSelect('brand', 'br_name', $this->getStringParameter('br_name'));
        $brandField->setHiddenField('gd_br_id', $this->getIntParameter('gd_br_id'));
        $brandField->addParameter('br_ss_id', $this->User->getSsId());
        $brandField->setEnableNewButton(false);
        $this->ListingForm->addField(Trans::getWord('relation'), $relationField);
        $this->ListingForm->addField(Trans::getWord('sku'), $this->Field->getText('gd_sku', $this->getStringParameter('gd_sku')));
        $this->ListingForm->addField(Trans::getWord('brand'), $brandField);
        $this->ListingForm->addField(Trans::getWord('category'), $goodsCategoryField);
        $this->ListingForm->addField(Trans::getWord('name'), $this->Field->getText('gd_name', $this->getStringParameter('gd_name')));
        $this->ListingForm->addField(Trans::getWord('requiredSn'), $this->Field->getYesNo('gd_sn', $this->getStringParameter('gd_sn')));
        $this->ListingForm->addField(Trans::getWord('bundlingEnabled'), $this->Field->getYesNo('gd_bundling', $this->getStringParameter('gd_bundling')));
        $this->ListingForm->addField(Trans::getWord('active'), $this->Field->getYesNo('gd_active', $this->getStringParameter('gd_active')));
    }

    /**
     * Abstract function to load the data.
     *
     * @return void
     */
    public function loadResultTable(): void
    {
        # set header column table
        $this->ListingTable->setHeaderRow(
            [
                'gd_rel_short' => Trans::getWord('relation'),
                'gd_sku' => Trans::getWord('sku'),
                'gd_brand' => Trans::getWord('brand'),
                'gd_category' => Trans::getWord('category'),
                'gd_name' => Trans::getWord('name'),
                'gd_uom_name' => Trans::getWord('defaultUom'),
                'gd_weight' => Trans::getWord('weight') . ' (KG)',
                'gd_volume' => Trans::getWord('cbm'),
                'gd_sn' => Trans::getWord('sn'),
                'gd_bundling' => Trans::getWord('bundling'),
                'gd_active' => Trans::getWord('active'),
            ]
        );
        # Load the data for Goods.
        $this->ListingTable->addRows($this->loadData());
        # Add special settings to the table
        $this->ListingTable->setColumnType('gd_bundling', 'yesno');
        $this->ListingTable->setColumnType('gd_sn', 'yesno');
        $this->ListingTable->setColumnType('gd_active', 'yesno');
        $this->ListingTable->setColumnType('gd_weight', 'float');
        $this->ListingTable->setColumnType('gd_volume', 'float');
        $this->ListingTable->addColumnAttribute('gd_rel_short', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('gd_sku', 'style', 'text-align: center;');
        $this->ListingTable->addColumnAttribute('gd_uom_name', 'style', 'text-align: center;');
        $this->ListingTable->setViewActionByHyperlink($this->getViewRoute(), ['gd_id']);
        if ($this->isAllowUpdate() === true) {
            $this->ListingTable->setUpdateActionByHyperlink($this->getUpdateRoute(), ['gd_id']);
        }
    }

    /**
     * Abstract function to load the total row of the listing data.
     *
     * @return int
     */
    protected function getTotalRows(): int
    {
        return GoodsDao::loadTotalData($this->getWhereCondition());
    }

    /**
     * Function to load default button
     *
     * @return void
     */
    protected function loadDefaultButton(): void
    {
        parent::loadDefaultButton();
        $barcodeButton = new PdfButton('GdBrcdPrt', Trans::getWord('printBarcode'), 'goodsbarcode');
        $barcodeButton->setIcon(Icon::Print)->btnPrimary()->pullRight()->btnMedium();
        $barcodeButton->addParameter('gd_ss_id', $this->User->getSsId());
        $this->View->addButtonAtTheBeginning($barcodeButton);
        if ($this->isValidParameter('gd_rel_id')) {
            $barcodeButton->addParameter('gd_rel_id', $this->getIntParameter('gd_rel_id'));
        }
        if ($this->isValidParameter('gd_gdc_id')) {
            $barcodeButton->addParameter('gd_gdc_id', $this->getIntParameter('gd_gdc_id'));
        }
        if ($this->isValidParameter('gd_br_id')) {
            $barcodeButton->addParameter('gd_br_id', $this->getIntParameter('gd_br_id'));
        }
        if ($this->isValidParameter('gd_sku')) {
            $barcodeButton->addParameter('gd_sku', $this->getStringParameter('gd_sku'));
        }
        if ($this->isValidParameter('gd_name')) {
            $barcodeButton->addParameter('gd_name', $this->getStringParameter('gd_name'));
        }
        if ($this->isValidParameter('gd_active')) {
            $barcodeButton->addParameter('gd_active', $this->getStringParameter('gd_active'));
        }
        if ($this->isValidParameter('gd_bundling')) {
            $barcodeButton->addParameter('gd_bundling', $this->getStringParameter('gd_bundling'));
        }
        if ($this->isValidParameter('gd_sn')) {
            $barcodeButton->addParameter('gd_sn', $this->getStringParameter('gd_sn'));
        }
        if ($this->isValidParameter('gd_multi_sn')) {
            $barcodeButton->addParameter('gd_multi_sn', $this->getStringParameter('gd_multi_sn'));
        }
    }

    /**
     * Get query to get the listing data.
     *
     * @return array
     */
    private function loadData(): array
    {
        return GoodsDao::loadData(
            $this->getWhereCondition(),
            $this->ListingSort->getOrderByFields(),
            $this->getLimitTable(),
            $this->getLimitOffsetTable()
        );
    }

    /**
     * Function to get the where condition.
     *
     * @return array
     */
    private function getWhereCondition(): array
    {
        # Set where conditions
        $wheres = [];
        $wheres[] = '(gd.gd_ss_id = ' . $this->User->getSsId() . ')';
        if ($this->isValidParameter('gd_rel_id')) {
            $wheres[] = '(gd.gd_rel_id = ' . $this->getIntParameter('gd_rel_id') . ')';
        }
        if ($this->isValidParameter('gd_gdc_id')) {
            $wheres[] = '(gd.gd_gdc_id = ' . $this->getIntParameter('gd_gdc_id') . ')';
        }
        if ($this->isValidParameter('gd_br_id')) {
            $wheres[] = '(gd.gd_br_id = ' . $this->getIntParameter('gd_br_id') . ')';
        }
        if ($this->isValidParameter('gd_sku')) {
            $wheres[] = StringFormatter::generateLikeQuery('gd.gd_sku', $this->getStringParameter('gd_sku'));
        }
        if ($this->isValidParameter('gd_name')) {
            $wheres[] = StringFormatter::generateLikeQuery('gd.gd_name', $this->getStringParameter('gd_name'));
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
        return $wheres;
    }
}
