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
use App\Model\Dao\System\Master\LanguagesDao;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;

/**
 * Class to handle the creation of detail Languages page
 *
 * @package    app
 * @subpackage Model\Detail\System\Master
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2021 Deni Firdaus Waruwu.
 */
class Languages extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'lg', 'lg_id');
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
            'lg_locale' => $this->getStringParameter('lg_locale'),
            'lg_iso' => mb_strtoupper($this->getStringParameter('lg_iso')),
            'lg_active' => $this->getStringParameter('lg_active', 'Y'),
        ];
        $lgDao = new LanguagesDao();
        $lgDao->doInsertTransaction($colVal);
        return $lgDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'lg_locale' => $this->getStringParameter('lg_locale'),
            'lg_iso' => mb_strtoupper($this->getStringParameter('lg_iso')),
            'lg_active' => $this->getStringParameter('lg_active'),
        ];
        $lgDao = new LanguagesDao();
        $lgDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return LanguagesDao::getByReference($this->getDetailReferenceValue());
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
        $this->Validation->checkRequire('lg_locale');
        $this->Validation->checkRequire('lg_iso');
        $this->Validation->checkUnique('lg_iso', 'languages', [
            'lg_id' => $this->getDetailReferenceValue()
        ]);
    }


    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getGeneralPortlet(): Portlet
    {
        # Instantiate Portlet Object
        $portlet = new Portlet('LgGnPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension();

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        # Add Field into field set.
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('lg_locale', $this->getStringParameter('lg_locale')), true);
        $fieldSet->addField(Trans::getWord('isoCode'), $this->Field->getText('lg_iso', $this->getStringParameter('lg_iso')), true);
        if ($this->isUpdate() === true) {
            $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('lg_active', $this->getStringParameter('lg_active')));
        }

        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 6);
        return $portlet;
    }
}
