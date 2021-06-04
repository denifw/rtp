<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\System\Page;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\Page\PageCategoryDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail PageCategory page
 *
 * @package    app
 * @subpackage Model\Detail\System\Page
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class PageCategory extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'pc', 'pc_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        $colVal = [
            'pc_name' => $this->getStringParameter('pc_name'),
            'pc_route' => $this->getStringParameter('pc_route'),
            'pc_active' => $this->getStringParameter('pc_active', 'Y'),
        ];
        $pcDao = new PageCategoryDao();
        $pcDao->doInsertTransaction($colVal);

        return $pcDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'pc_name' => $this->getStringParameter('pc_name'),
            'pc_route' => $this->getStringParameter('pc_route'),
            'pc_active' => $this->getStringParameter('pc_active', 'Y'),
        ];
        $pcDao = new PageCategoryDao();
        $pcDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return PageCategoryDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('pc_name', 2, 64);
        $this->Validation->checkRequire('pc_code', 2, 55);
        $this->Validation->checkUnique('pc_code', 'page_category', [
            'pc_id' => $this->getDetailReferenceValue()
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('pc_name', $this->getStringParameter('pc_name')), true);
        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('pc_code', $this->getStringParameter('pc_code')), true);
        $fieldSet->addField(Trans::getWord('route'), $this->Field->getText('pc_route', $this->getStringParameter('pc_route')));
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('pc_active', $this->getStringParameter('pc_active')));

        # Create a portlet box.
        $portlet = new Portlet('PcGeneralPtl', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);

        return $portlet;
    }


}
