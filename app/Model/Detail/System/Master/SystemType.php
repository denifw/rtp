<?php
/**
 * Contains code written by the Deni Firdaus Waruwu.
 * Any other use of this code is in violation of copy rights.
 *
 * @package    Project
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */

namespace App\Model\Detail\System\Master;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Model\Dao\System\Master\SystemTypeDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail SystemType page
 *
 * @package    app
 * @subpackage Model\Detail\System\Master
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class SystemType extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'sty', 'sty_id');
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
            'sty_group' => mb_strtolower($this->getStringParameter('sty_group')),
            'sty_code' => mb_strtolower($this->getStringParameter('sty_code')),
            'sty_name' => $this->getStringParameter('sty_name'),
            'sty_active' => $this->getStringParameter('sty_active', 'Y')
        ];
        $styDao = new SystemTypeDao();
        $styDao->doInsertTransaction($colVal);
        return $styDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'sty_group' => mb_strtolower($this->getStringParameter('sty_group')),
            'sty_code' => mb_strtolower($this->getStringParameter('sty_code')),
            'sty_name' => $this->getStringParameter('sty_name'),
            'sty_active' => $this->getStringParameter('sty_active')
        ];
        $styDao = new SystemTypeDao();
        $styDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return SystemTypeDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        $this->Tab->addPortlet('general', $this->getGeneralPortlet());
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        if ($this->getFormAction() === null) {
            $this->Validation->checkRequire('sty_group', 2, 256);
            $this->Validation->checkRequire('sty_code', 1, 256);
            $this->Validation->checkRequire('sty_name', 2, 256);
            $this->Validation->checkUnique('sty_code', 'system_type', [
                'sty_id' => $this->getDetailReferenceValue()
            ], [
                'sty_group' => $this->getStringParameter('sty_group')
            ]);
        } else {
            parent::loadValidationRole();
        }
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('StyPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(6, 6);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $fieldSet->addField(Trans::getWord('group'), $this->Field->getText('sty_group', $this->getStringParameter('sty_group')));
        $fieldSet->addField(Trans::getWord('code'), $this->Field->getText('sty_code', $this->getStringParameter('sty_code')));
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('sty_name', $this->getStringParameter('sty_name')));
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('sty_active', $this->getStringParameter('sty_active')));


        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }
}
