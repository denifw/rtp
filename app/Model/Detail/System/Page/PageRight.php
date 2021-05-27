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
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\Page\PageCategoryDao;
use App\Model\Dao\System\Page\PageDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\System\Page\PageNotificationDao;
use App\Model\Dao\System\Page\PageRightDao;

/**
 * Class to handle the creation of detail Page page
 *
 * @package    app
 * @subpackage Model\Detail\Page
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class PageRight extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'pageRight', 'pr_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        $colVal = [
            'pr_pg_id' => $this->getIntParameter('pr_pg_id'),
            'pr_name' => $this->getStringParameter('pr_name'),
            'pr_description' => $this->getStringParameter('pr_description'),
            'pr_default' => $this->getStringParameter('pr_default', 'N'),
            'pr_active' => $this->getStringParameter('pr_active', 'Y'),
        ];
        $prDao = new PageRightDao();
        $prDao->doInsertTransaction($colVal);
        return $prDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'pr_pg_id' => $this->getIntParameter('pr_pg_id'),
            'pr_name' => $this->getStringParameter('pr_name'),
            'pr_description' => $this->getStringParameter('pr_description'),
            'pr_default' => $this->getStringParameter('pr_default', 'N'),
            'pr_active' => $this->getStringParameter('pr_active', 'Y'),
        ];
        $prDao = new PageRightDao();
        $prDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return PageRightDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('pr_pg_id');
        $this->Validation->checkRequire('pr_name', 3, 125);
        $this->Validation->checkRequire('pr_description', 3, 255);
        $this->Validation->checkUnique('pr_name', 'page_right', [
            'pr_id' => $this->getDetailReferenceValue(),
        ], [
            'pr_pg_id' => $this->getIntParameter('pr_pg_id'),
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);
        # Page Field
        $pageField = $this->Field->getSingleSelectTable('page', 'pr_pg_title', $this->getStringParameter('pr_pg_title'), 'loadSingleSelectTable');
        $pageField->setHiddenField('pr_pg_id', $this->getIntParameter('pr_pg_id'));
        $pageField->setTableColumns([
            'pg_title' => Trans::getWord('name'),
            'pc_name' => Trans::getWord('category'),
            'pg_route' => Trans::getWord('route'),
            'mn_name' => Trans::getWord('menu'),
        ]);
        $pageField->setFilters([
            'pg_title' => Trans::getWord('name'),
            'pc_name' => Trans::getWord('category'),
            'pg_route' => Trans::getWord('route'),
            'mn_name' => Trans::getWord('menu'),
        ]);
        $pageField->setValueCode('pg_id');
        $pageField->setLabelCode('pg_title');
        $pageField->addParameter('pg_system', 'N');
        $pageField->addParameter('pg_active', 'Y');
        $this->View->addModal($pageField->getModal());

        # add field.
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('pr_name', $this->getStringParameter('pr_name')), true);
        $fieldSet->addField(Trans::getWord('page'), $pageField, true);
        $fieldSet->addField(Trans::getWord('description'), $this->Field->getText('pr_description', $this->getStringParameter('pr_description')), true);
        $fieldSet->addField(Trans::getWord('default'), $this->Field->getYesNo('pr_default', $this->getStringParameter('pr_default', 'N')));
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('pr_active', $this->getStringParameter('pr_active', 'Y')));

        # Create a portlet box.
        $portlet = new Portlet('prGeneralPortlet', $this->getDefaultPortletTitle());
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }


}
