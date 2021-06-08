<?php
/**
 * Contains code written by the PT Makmur Berkat Teknologi.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Valerius Iman <valerius@mbteknologi.com>
 * @copyright 2020 PT Makmur Berkat Teknologi.
 */

namespace App\Model\Detail\System\Master;

use App\Frame\Formatter\Trans;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\System\Master\BankDao;

/**
 * Class to handle the creation of detail Bank page
 *
 * @package    app
 * @subpackage Model\Detail\Master\Finance
 * @author     Valerius Iman <valerius@mbteknologi.com>
 * @copyright  2020 PT Makmur Berkat Teknologi.
 */
class Bank extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'bn', 'bn_id');
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
            'bn_short_name' => $this->getStringParameter('bn_short_name'),
            'bn_name' => $this->getStringParameter('bn_name'),
            'bn_active' => $this->getStringParameter('bn_active', 'Y'),
        ];

        $bankDao = new BankDao();
        $bankDao->doInsertTransaction($colVal);
        return $bankDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $colVal = [
            'bn_short_name' => $this->getStringParameter('bn_short_name'),
            'bn_name' => $this->getStringParameter('bn_name'),
            'bn_active' => $this->getStringParameter('bn_active', 'Y'),
        ];

        $bankDao = new BankDao();
        $bankDao->doUpdateTransaction($this->getDetailReferenceValue(), $colVal);
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return BankDao::getByReference($this->getDetailReferenceValue());
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
            $this->Validation->checkRequire('bn_short_name', '2', '125');
            $this->Validation->checkUnique('bn_short_name', 'bank', [
                'bn_id' => $this->getDetailReferenceValue()
            ], [
                'bn_short_name' => $this->getStringParameter('bn_short_name')
            ]);
            $this->Validation->checkRequire('bn_name', '2', '255');
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
        $portlet = new Portlet('bankPtl', $this->getDefaultPortletTitle());
        $portlet->setGridDimension(6, 6);

        # Instantiate FieldSet Object
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $fieldSet->addField(Trans::getWord('shortName'), $this->Field->getText('bn_short_name', $this->getStringParameter('bn_short_name')), true);
        $fieldSet->addField(Trans::getWord('name'), $this->Field->getText('bn_name', $this->getStringParameter('bn_name')), true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('bn_active', $this->getStringParameter('bn_active')));

        # Add field to field set

        $portlet->addFieldSet($fieldSet);
        return $portlet;
    }
}
