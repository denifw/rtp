<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\System\Access;

use App\Frame\Formatter\SqlHelper;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Table;
use App\Frame\Gui\TableDatas;
use App\Frame\Gui\Templates\ProfileGeneral;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Crm\ContactPersonDao;
use App\Model\Dao\Crm\OfficeDao;
use App\Model\Dao\Crm\RelationDao;
use App\Model\Dao\System\Access\SerialNumberDao;
use App\Model\Dao\System\Access\SystemServiceDao;
use App\Model\Dao\System\Access\SystemSettingDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\Document\DocumentTypeDao;
use App\Model\Dao\System\Master\SerialCodeDao;

/**
 * Class to handle the creation of detail SystemSetting page
 *
 * @package    app
 * @subpackage Model\Detail\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class SystemSetting extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'ss', 'ss_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return string
     */
    protected function doInsert(): string
    {
        # Upload document invoice
        $logoName = '';
        $file = $this->getFileParameter('ss_logo');
        if ($file !== null) {
            $logoName = time() . '.' . $file->getClientOriginalExtension();
        }

        $ssColVal = [
            'ss_relation' => $this->getStringParameter('rel_name'),
            'ss_lg_id' => $this->getStringParameter('ss_lg_id'),
            'ss_cur_id' => $this->getStringParameter('ss_cur_id'),
            'ss_decimal_number' => $this->getIntParameter('ss_decimal_number'),
            'ss_decimal_separator' => $this->getStringParameter('ss_decimal_separator'),
            'ss_thousand_separator' => $this->getStringParameter('ss_thousand_separator'),
            'ss_logo' => $logoName,
            'ss_name_space' => mb_strtolower($this->getStringParameter('ss_name_space')),
            'ss_system' => 'N',
            'ss_active' => $this->getStringParameter('ss_active', 'Y'),
        ];
        $ssDao = new SystemSettingDao();
        $ssDao->doInsertTransaction($ssColVal);
        $ssId = $ssDao->getLastInsertId();

        # Upload picture
        $dct = DocumentTypeDao::getByCode('ss', 'logo');
        if ($file !== null && empty($dct) === false) {
            $colVal = [
                'doc_dct_id' => $dct['dct_id'],
                'doc_ss_id' => $ssId,
                'doc_group_reference' => $ssId,
                'doc_type_reference' => null,
                'doc_file_name' => $logoName,
                'doc_description' => $logoName,
                'doc_file_size' => $file->getSize(),
                'doc_file_type' => $file->getClientOriginalExtension(),
                'doc_public' => $this->getStringParameter('doc_public', 'N'),
            ];
            $docDao = new DocumentDao();
            $docDao->doUploadDocument($colVal, $file);
        }

        # Insert Serial Number
        $wheres = [];
        $wheres[] = SqlHelper::generateStringCondition('sc_active', 'Y');
        $wheres[] = SqlHelper::generateNullCondition('sc_deleted_on');
        $scData = SerialCodeDao::loadData($wheres);
        $snDao = new SerialNumberDao();
        foreach ($scData as $row) {
            $snRelColVal = [
                'sn_sc_id' => $row['sc_id'],
                'sn_ss_id' => $ssId,
                'sn_relation' => 'N',
                'sn_prefix' => $row['sc_code'],
                'sn_separator' => '-',
                'sn_postfix' => '',
                'sn_yearly' => 'Y',
                'sn_monthly' => 'Y',
                'sn_length' => 5,
                'sn_increment' => 1,
                'sn_format' => 'A',
                'sn_active' => 'Y',
            ];
            $snDao->doInsertTransaction($snRelColVal);
        }

        # Insert Relation
        $sn = new SerialNumber($ssDao->getLastInsertId());
        $relNumber = $sn->loadNumber('REL');
        $relColVal = [
            'rel_number' => $relNumber,
            'rel_ss_id' => $ssId,
            'rel_name' => $this->getStringParameter('rel_name'),
            'rel_short_name' => $this->getStringParameter('ss_name_space'),
            'rel_website' => $this->getStringParameter('rel_website'),
            'rel_email' => $this->getStringParameter('rel_email'),
            'rel_phone' => $this->getStringParameter('rel_phone'),
            'rel_active' => 'Y',
        ];
        $relDao = new RelationDao();
        $relDao->doInsertTransaction($relColVal);
        $relId = $relDao->getLastInsertId();

        # Insert Office
        $ofColVal = [
            'of_rel_id' => $relId,
            'of_name' => $this->getStringParameter('ss_name_space'),
            'of_invoice' => 'Y',
            'of_cnt_id' => $this->getStringParameter('of_cnt_id'),
            'of_stt_id' => $this->getStringParameter('of_stt_id'),
            'of_cty_id' => $this->getStringParameter('of_cty_id'),
            'of_dtc_id' => $this->getStringParameter('of_dtc_id'),
            'of_address' => $this->getStringParameter('of_address'),
            'of_postal_code' => $this->getStringParameter('of_postal_code'),
            'of_longitude' => $this->getFloatParameter('of_longitude'),
            'of_latitude' => $this->getFloatParameter('of_latitude'),
            'of_active' => 'Y',
        ];
        $ofDao = new OfficeDao();
        $ofDao->doInsertTransaction($ofColVal);
        $ofId = $ofDao->getLastInsertId();
        # Insert Contact Person
        $cpNumber = $sn->loadNumber('CP');
        $cpColVal = [
            'cp_number' => $cpNumber,
            'cp_name' => $this->getStringParameter('cp_name'),
            'cp_of_id' => $ofId,
            'cp_active' => 'Y',
        ];
        $cpDao = new ContactPersonDao();
        $cpDao->doInsertTransaction($cpColVal);
        $cpId = $cpDao->getLastInsertId();
        # Do Update reference
        $ssDao->doUpdateTransaction($ssId, [
            'ss_rel_id' => $relId
        ]);
        $relDao->doUpdateTransaction($relId, [
            'rel_of_id' => $ofId,
            'rel_cp_id' => $cpId
        ]);
        $ofDao->doUpdateTransaction($ofId, [
            'of_cp_id' => $cpId
        ]);

        return $ssId;
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        #update logo
        $docDao = new DocumentDao();
        $fileLogo = $this->getFileParameter('ss_logo');
        $idLogo = $this->getStringParameter('ss_logo_id');
        if ($fileLogo !== null) {
            # Delete Existing Logo
            if ($this->isValidParameter('ss_logo_id') === true) {
                $docDao->doDeleteTransaction($idLogo);
            }
            # Insert new Logo
            $logoName = time() . '.' . $fileLogo->getClientOriginalExtension();
            $dct = DocumentTypeDao::getByCode('ss', 'logo');
            $docColVal = [
                'doc_dct_id' => $dct['dct_id'],
                'doc_ss_id' => $this->getDetailReferenceValue(),
                'doc_group_reference' => $this->getDetailReferenceValue(),
                'doc_type_reference' => null,
                'doc_file_name' => $logoName,
                'doc_description' => $logoName,
                'doc_file_size' => $fileLogo->getSize(),
                'doc_file_type' => $fileLogo->getClientOriginalExtension(),
                'doc_public' => 'N',
            ];
            $docDao->doUploadDocument($docColVal, $fileLogo);
            $idLogo = $docDao->getLastInsertId();
        }

        #update icon
        $fileIcon = $this->getFileParameter('ss_icon');
        $idIcon = $this->getStringParameter('ss_icon_id');
        if ($fileIcon !== null) {
            # Delete existing data.
            if ($this->isValidParameter('ss_icon_id') === true) {
                $docDao->doDeleteTransaction($idIcon);
            }
            # Insert new Icon
            $iconName = time() . '.' . $fileIcon->getClientOriginalExtension();
            $dct = DocumentTypeDao::getByCode('ss', 'icon');
            $docIcVal = [
                'doc_dct_id' => $dct['dct_id'],
                'doc_ss_id' => $this->getDetailReferenceValue(),
                'doc_group_reference' => $this->getDetailReferenceValue(),
                'doc_type_reference' => null,
                'doc_file_name' => $iconName,
                'doc_description' => $iconName,
                'doc_file_size' => $fileIcon->getSize(),
                'doc_file_type' => $fileIcon->getClientOriginalExtension(),
                'doc_public' => 'N',
            ];
            $docDao->doUploadDocument($docIcVal, $fileIcon);
            $idIcon = $docDao->getLastInsertId();
        }
        $ssColVal = [
            'ss_lg_id' => $this->getStringParameter('ss_lg_id'),
            'ss_cur_id' => $this->getStringParameter('ss_cur_id'),
            'ss_decimal_number' => $this->getIntParameter('ss_decimal_number'),
            'ss_decimal_separator' => $this->getStringParameter('ss_decimal_separator'),
            'ss_thousand_separator' => $this->getStringParameter('ss_thousand_separator'),
            'ss_logo_id' => $idLogo,
            'ss_icon_id' => $idIcon,
            'ss_active' => $this->getStringParameter('ss_active'),
        ];
        $ssDao = new SystemSettingDao();
        $ssDao->doUpdateTransaction($this->getDetailReferenceValue(), $ssColVal);

        # Start Update system Service
        $srvIds = $this->getArrayParameter('srv_id');
        $ssrIds = $this->getArrayParameter('ssr_id');
        $ssrActives = $this->getArrayParameter('ssr_active');
        if (count($srvIds) > 0) {
            $ssrDao = new SystemServiceDao();
            foreach ($ssrIds as $key => $value) {
                if (array_key_exists($key, $ssrActives) === true && $ssrActives[$key] === 'Y') {
                    if (empty($value) === true) {
                        $colValRl = [
                            'ssr_ss_id' => $this->getDetailReferenceValue(),
                            'ssr_srv_id' => $srvIds[$key]
                        ];
                        $ssrDao->doInsertTransaction($colValRl);
                    } else {
                        $ssrDao->doUndoDeleteTransaction($value);
                    }
                } elseif (empty($value) === false) {
                    $ssrDao->doDeleteTransaction($value);
                }
            }
        }
        # End Update system Service
    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        return SystemSettingDao::getByReference($this->getDetailReferenceValue());
    }

    /**
     * Abstract function to load form of the page.
     *
     * @return void
     */
    public function loadForm(): void
    {
        if ($this->isInsert() === true) {
            $this->Tab->addPortlet('general', $this->getGeneralFieldSet());
            $this->Tab->addPortlet('general', $this->getSettingFieldSet());
        } else {
            $this->Tab->addPortlet('general', $this->getSystemSettingFieldSet());
            $this->Tab->addPortlet('general', $this->getLogoFieldSet());
            $this->Tab->addPortlet('general', $this->getIconFieldSet());

            $this->Tab->addPortlet('service', $this->getServicePortlet());

        }
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        # Validate Setting
        $this->Validation->checkRequire('ss_name_space', 2, 255);
        $this->Validation->checkUnique('ss_name_space', 'system_setting', [
            'ss_id' => $this->getDetailReferenceValue(),
        ]);
        $this->Validation->checkRequire('ss_lg_id');
        $this->Validation->checkRequire('ss_cur_id');
        $this->Validation->checkRequire('ss_decimal_number');
        $this->Validation->checkInt('ss_decimal_number', 0);
        $this->Validation->checkRequire('ss_decimal_separator');
        $this->Validation->checkRequire('ss_thousand_separator');
        if ($this->isInsert() === true) {
            # Validate Relation
            $this->Validation->checkRequire('ss_logo');
            $this->Validation->checkImage('ss_logo');
            $this->Validation->checkRequire('rel_name', 3, 255);
            if ($this->isValidParameter('rel_email') === true) {
                $this->Validation->checkEmail('rel_email');
            }
            # Validate Address
            $this->Validation->checkRequire('of_cnt_id');
            $this->Validation->checkRequire('of_stt_id');
            $this->Validation->checkRequire('of_cty_id');
            $this->Validation->checkRequire('of_dtc_id');
            $this->Validation->checkRequire('of_address', 5, 255);
            # Validate Contact Person
            $this->Validation->checkRequire('cp_name', 3, 255);
        } else {
            if ($this->isValidParameter('ss_logo') === true) {
                $this->Validation->checkImage('ss_logo');
            }
            if ($this->isValidParameter('ss_icon') === true) {
                $this->Validation->checkImage('ss_icon');
            }
        }

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
        $fieldSet->setGridDimension();

        # Create custom field.
        $countryField = $this->Field->getSingleSelect('cnt', 'of_country', $this->getStringParameter('of_country'));
        $countryField->setHiddenField('of_cnt_id', $this->getStringParameter('of_cnt_id'));
        $countryField->setDetailReferenceCode('cnt_id');
        $countryField->addClearField('of_state');
        $countryField->addClearField('of_stt_id');
        $countryField->addClearField('of_city');
        $countryField->addClearField('of_cty_id');
        $countryField->addClearField('of_district');
        $countryField->addClearField('of_dtc_id');

        $stateField = $this->Field->getSingleSelect('stt', 'of_state', $this->getStringParameter('of_state'));
        $stateField->setHiddenField('of_stt_id', $this->getStringParameter('of_stt_id'));
        $stateField->setDetailReferenceCode('stt_id');
        $stateField->addParameterById('stt_cnt_id', 'of_cnt_id', Trans::getWord('country'));
        $stateField->addClearField('of_city');
        $stateField->addClearField('of_cty_id');
        $stateField->addClearField('of_district');
        $stateField->addClearField('of_dtc_id');

        $cityField = $this->Field->getSingleSelect('cty', 'of_city', $this->getStringParameter('of_city'));
        $cityField->setHiddenField('of_cty_id', $this->getStringParameter('of_cty_id'));
        $cityField->setDetailReferenceCode('cty_id');
        $cityField->addParameterById('cty_cnt_id', 'of_cnt_id', Trans::getWord('country'));
        $cityField->addParameterById('cty_stt_id', 'of_stt_id', Trans::getWord('state'));
        $cityField->addClearField('of_district');
        $cityField->addClearField('of_dtc_id');

        $districtField = $this->Field->getSingleSelect('dtc', 'of_district', $this->getStringParameter('of_district'));
        $districtField->setHiddenField('of_dtc_id', $this->getStringParameter('of_dtc_id'));
        $districtField->setDetailReferenceCode('dtc_id');
        $districtField->addParameterById('dtc_cnt_id', 'of_cnt_id', Trans::getWord('country'));
        $districtField->addParameterById('dtc_stt_id', 'of_stt_id', Trans::getWord('state'));
        $districtField->addParameterById('dtc_cty_id', 'of_cty_id', Trans::getWord('city'));

        # Add field into field set
        $fieldSet->addField(Trans::getWord('relation'), $this->Field->getText('rel_name', $this->getStringParameter('rel_name')), true);
        $fieldSet->addField(Trans::getWord('nameSpace'), $this->Field->getText('ss_name_space', $this->getStringParameter('ss_name_space')), true);
        $fieldSet->addField(Trans::getWord('country'), $countryField, true);
        $fieldSet->addField(Trans::getWord('state'), $stateField, true);
        $fieldSet->addField(Trans::getWord('picName'), $this->Field->getText('cp_name', $this->getStringParameter('cp_name')), true);
        $fieldSet->addField(Trans::getWord('website'), $this->Field->getText('rel_website', $this->getStringParameter('rel_website')));
        $fieldSet->addField(Trans::getWord('city'), $cityField, true);
        $fieldSet->addField(Trans::getWord('district'), $districtField, true);
        $fieldSet->addField(Trans::getWord('email'), $this->Field->getText('rel_email', $this->getStringParameter('rel_email')));
        $fieldSet->addField(Trans::getWord('phone'), $this->Field->getText('rel_phone', $this->getStringParameter('rel_phone')));
        $fieldSet->addField(Trans::getWord('address'), $this->Field->getText('of_address', $this->getStringParameter('of_address')), true);
        $fieldSet->addField(Trans::getWord('postalCode'), $this->Field->getText('of_postal_code', $this->getStringParameter('of_postal_code')));
        $fieldSet->addField(Trans::getWord('longitude'), $this->Field->getText('of_longitude', $this->getStringParameter('of_longitude')));
        $fieldSet->addField(Trans::getWord('latitude'), $this->Field->getText('of_latitude', $this->getStringParameter('of_latitude')));

        # Create a portlet box.
        $portlet = new Portlet('SsRelPtl', Trans::getWord('relation'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12, 12, 12);

        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return Portlet
     */
    private function getSettingFieldSet(): Portlet
    {
        # Create Fields.
        $languageField = $this->Field->getSingleSelect('lg', 'ss_language', $this->getStringParameter('ss_language'));
        $languageField->setHiddenField('ss_lg_id', $this->getStringParameter('ss_lg_id'));
        $languageField->setEnableNewButton(false);

        # Create Decimal Separator field.
        $decimalSeparator = $this->Field->getSelect('ss_decimal_separator', $this->getStringParameter('ss_decimal_separator'));
        $decimalSeparator->addOption(Trans::getWord('comma') . ' (,)', ',');
        $decimalSeparator->addOption(Trans::getWord('dot') . ' (.)', '.');

        # Create Thousand Separator
        $thousandSeparator = $this->Field->getSelect('ss_thousand_separator', $this->getStringParameter('ss_thousand_separator'));
        $thousandSeparator->addOption(Trans::getWord('comma') . ' (,)', ',');
        $thousandSeparator->addOption(Trans::getWord('dot') . ' (.)', '.');

        # Create currency
        $currencyField = $this->Field->getSingleSelect('cur', 'ss_currency', $this->getStringParameter('ss_currency'));
        $currencyField->setHiddenField('ss_cur_id', $this->getStringParameter('ss_cur_id'));
        $currencyField->setEnableDetailButton(false);
        $currencyField->setEnableNewButton(false);

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(4);

        $fieldSet->addField(Trans::getWord('language'), $languageField, true);
        $fieldSet->addField(Trans::getWord('logo'), $this->Field->getFile('ss_logo', ''), true);
        $fieldSet->addField(Trans::getWord('currency'), $currencyField, true);
        $fieldSet->addField(Trans::getWord('decimalNumber'), $this->Field->getText('ss_decimal_number', $this->getStringParameter('ss_decimal_number')), true);
        $fieldSet->addField(Trans::getWord('decimalSeparator'), $decimalSeparator, true);
        $fieldSet->addField(Trans::getWord('thousandSeparator'), $thousandSeparator, true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('ss_active', $this->getStringParameter('ss_active')));

        # Create a portlet box.
        $portlet = new Portlet('SsSettingPtl', Trans::getWord('settings'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12, 12, 12);

        return $portlet;
    }

    /**
     * Function to get the system setting Field Set.
     *
     * @return Portlet
     */
    private function getSystemSettingFieldSet(): Portlet
    {
        # Create Fields.
        $languageField = $this->Field->getSingleSelect('lg', 'ss_language', $this->getStringParameter('ss_language'));
        $languageField->setHiddenField('ss_lg_id', $this->getStringParameter('ss_lg_id'));
        $languageField->setEnableNewButton(false);

        # Create Decimal Separator field.
        $decimalSeparator = $this->Field->getSelect('ss_decimal_separator', $this->getStringParameter('ss_decimal_separator'));
        $decimalSeparator->addOption(Trans::getWord('comma') . ' (,)', ',');
        $decimalSeparator->addOption(Trans::getWord('dot') . ' (.)', '.');

        # Create Thousand Separator
        $thousandSeparator = $this->Field->getSelect('ss_thousand_separator', $this->getStringParameter('ss_thousand_separator'));
        $thousandSeparator->addOption(Trans::getWord('comma') . ' (,)', ',');
        $thousandSeparator->addOption(Trans::getWord('dot') . ' (.)', '.');

        # Create currency
        $currencyField = $this->Field->getSingleSelect('cur', 'ss_currency', $this->getStringParameter('ss_currency'));
        $currencyField->setHiddenField('ss_cur_id', $this->getStringParameter('ss_cur_id'));
        $currencyField->setEnableDetailButton(false);
        $currencyField->setEnableNewButton(false);
        # Create namespace field

        $nameSpaceField = $this->Field->getText('ss_name_space', $this->getStringParameter('ss_name_space'));
        $nameSpaceField->setReadOnly();

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('systemName'), $this->Field->getText('ss_relation', $this->getStringParameter('ss_relation')), true);
        $fieldSet->addField(Trans::getWord('nameSpace'), $nameSpaceField, true);
        $fieldSet->addField(Trans::getWord('language'), $languageField, true);
        $fieldSet->addField(Trans::getWord('currency'), $currencyField, true);
        $fieldSet->addField(Trans::getWord('decimalNumber'), $this->Field->getText('ss_decimal_number', $this->getStringParameter('ss_decimal_number')), true);
        $fieldSet->addField(Trans::getWord('decimalSeparator'), $decimalSeparator, true);
        $fieldSet->addField(Trans::getWord('thousandSeparator'), $thousandSeparator, true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('ss_active', $this->getStringParameter('ss_active')));
        $fieldSet->addHiddenField($this->Field->getHidden('ss_rel_id', $this->getStringParameter('ss_rel_id')));

        # Create a portlet box.
        $portlet = new Portlet('SsSettingPtl', Trans::getWord('settings'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12, 6);

        return $portlet;
    }

    /**
     * Function to get the logo field set.
     *
     * @return Portlet
     */
    private function getLogoFieldSet(): Portlet
    {
        $path = DocumentDao::getDocumentPathById($this->getStringParameter('ss_logo_id'));
        $profile = new ProfileGeneral('SsLogo');
        $profile->setHeight(200);
        $profile->setGridDimension(12, 12, 12);
        $data = [
            'title' => $this->getStringParameter('ss_relation'),
            'img_path' => $path,
        ];
        $profile->setData($data);
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getWord('logo'), $this->Field->getFile('ss_logo', ''));
        $fieldSet->addHiddenField($this->Field->getHidden('ss_logo_id', $this->getStringParameter('ss_logo_id')));
        # Create a portlet box.
        $portlet = new Portlet('RelLogoPtl', Trans::getWord('logo'));
        $portlet->addText($profile->createView());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 4, 4);

        return $portlet;
    }

    /**
     * Function to get the icon field set.
     *
     * @return Portlet
     */
    private function getIconFieldSet(): Portlet
    {
        $path = DocumentDao::getDocumentPathById($this->getStringParameter('ss_icon_id'));
        $icon = new ProfileGeneral('SsIcon');
        $icon->setHeight(200);
        $icon->setGridDimension(12, 12, 12);
        $data = [
            'title' => $this->getStringParameter('ss_relation'),
            'img_path' => $path,
        ];
        $icon->setData($data);
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getWord('icon'), $this->Field->getFile('ss_icon', ''));
        $fieldSet->addHiddenField($this->Field->getHidden('ss_icon_id', $this->getStringParameter('ss_icon_id')));
        # Create a portlet box.
        $portlet = new Portlet('RelIconPtl', Trans::getWord('icon'));
        $portlet->addText($icon->createView());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 4, 4);

        return $portlet;
    }


    /**
     * Function to get the page Field Set.
     *
     * @return Portlet
     */
    private function getServicePortlet(): Portlet
    {
        # Create a table.
        $table = new Table('SsSsrTbl');
        $table->setHeaderRow([
            'ssr_id' => '',
            'srv_id' => '',
            'srv_code' => Trans::getWord('serviceCode'),
            'srv_name' => Trans::getWord('serviceName'),
            'ssr_active' => Trans::getWord('active'),
        ]);
        $data = SystemServiceDao::loadSystemServiceData($this->getDetailReferenceValue());
        $index = 0;
        $results = [];
        foreach ($data as $row) {
            $row['ssr_id'] = $this->Field->getHidden('ssr_id[' . $index . ']', $row['ssr_id']);
            $row['srv_id'] = $this->Field->getHidden('srv_id[' . $index . ']', $row['srv_id']);
            $checked = false;
            if ($row['ssr_active'] === 'Y') {
                $checked = true;
                $table->addCellAttribute('ssr_active', $index, 'class', 'bg-green');
            }
            $check = $this->Field->getCheckBox('ssr_active[' . $index . ']', 'Y', $checked);
            $row['ssr_active'] = $check;
            $results[] = $row;
            $index++;
        }
        $table->addRows($results);
        # Add special settings to the table
        $table->addColumnAttribute('ssr_active', 'style', 'text-align: center;');
        # Create a portlet box.
        $portlet = new Portlet('SsSsrPtl', Trans::getWord('service'));
        $portlet->addTable($table);

        return $portlet;
    }


}
