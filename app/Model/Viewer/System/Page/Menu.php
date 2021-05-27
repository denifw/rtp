<?php
/**
 * Created by PhpStorm.
 * User: Deni Firdaus Waruwu
 * Date: 11/04/2019
 * Time: 12:05
 */

namespace App\Model\Viewer\System\Page;

use App\Frame\Formatter\Trans;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\Mvc\AbstractViewerModel;
use App\Model\Dao\System\Page\MenuDao;

/**
 * Class to handle the creation of detail Menu page
 *
 * @package    App
 * @subpackage Model\Detail\System\Page
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class Menu extends AbstractViewerModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(get_class($this), 'menu', 'mn_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'mn_name' => $this->getStringParameter('mn_name'),
            'mn_parent' => $this->getIntParameter('mn_parent'),
            'mn_order' => $this->getIntParameter('mn_order'),
            'mn_icon' => $this->getStringParameter('mn_icon'),
            'mn_active' => $this->getStringParameter('mn_active', 'Y')
        ];
        $menuDao = new MenuDao();
        $menuDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return MenuDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->isUpdate();
        $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {
        # Create a form.
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(4);
        $parentField = $this->Field->getSingleSelect('menu', 'parent_menu', $this->getStringParameter('parent_menu'));
        $parentField->setHiddenField('mn_parent', $this->getIntParameter('mn_parent'));
        $parentField->setEnableNewButton(false);
        $parentField->setEnableDetailButton(false);

        # Add field into fieldset.
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('mn_name', $this->getStringParameter('mn_name')), true);
        $fieldSet->addField(Trans::getWord('parentMenu'), $parentField);
        $fieldSet->addField(Trans::getWord('icon'), $this->Field->getText('mn_icon', $this->getStringParameter('mn_icon')), true);
        $fieldSet->addField(Trans::getWord('sortNumber'), $this->Field->getText('mn_order', $this->getIntParameter('mn_order')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('mn_active', $this->getStringParameter('mn_active')));
        # Create a portlet box.
        $portlet = new Portlet('mnGeneralPortlet', Trans::getWord('detail'));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('mn_name', 3, 125);
        $this->Validation->checkRequire('mn_order');
        $this->Validation->checkInt('mn_order');
        $this->Validation->checkRequire('mn_icon', 7, 125);
        $this->Validation->checkUnique('mn_name', 'menu', [
            'mn_id' => $this->getDetailReferenceValue()
        ], [
            'mn_parent' => $this->getIntParameter('mn_parent')
        ]);
    }
}
