<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\System;

use App\Frame\Document\FileUpload;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Templates\ProfileGeneral;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Frame\System\SerialNumber\SerialNumber;
use App\Model\Dao\Relation\ContactPersonDao;
use App\Model\Dao\Relation\OfficeDao;
use App\Model\Dao\Relation\RelationDao;
use App\Model\Dao\Setting\SerialNumberDao;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\Document\DocumentTypeDao;
use App\Model\Dao\System\LanguagesDao;
use App\Model\Dao\System\SystemSettingDao;
use Illuminate\Support\Facades\DB;

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
        parent::__construct(get_class($this), 'systemSetting', 'ss_id');
        $this->setParameters($parameters);
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        # Upload document invoice
        $logoName = '';
        $iconName = '';
        $file = $this->getFileParameter('ss_logo');
        $fileIcon = $this->getFileParameter('ss_icon');
        if ($file !== null) {
            $logoName = strtolower($this->getStringParameter('ss_name_space')) . time() . '.' . $file->getClientOriginalExtension();
        }
        if ($fileIcon !== null) {
            $iconName = strtolower($this->getStringParameter('ss_name_space')) . time() . '.' . $file->getClientOriginalExtension();
        }

        $ssColVal = [
            'ss_relation' => $this->getStringParameter('rel_name'),
            'ss_lg_id' => $this->getIntParameter('ss_lg_id'),
            'ss_cur_id' => $this->getIntParameter('ss_cur_id'),
            'ss_decimal_number' => $this->getIntParameter('ss_decimal_number'),
            'ss_decimal_separator' => $this->getStringParameter('ss_decimal_separator'),
            'ss_thousand_separator' => $this->getStringParameter('ss_thousand_separator'),
            'ss_logo' => $logoName,
            'ss_name_space' => $this->getStringParameter('ss_name_space'),
            'ss_system' => 'N',
            'ss_active' => $this->getStringParameter('ss_active', 'Y'),
            'ss_icon' => $iconName,
        ];
        $ssDao = new SystemSettingDao();
        $ssDao->doInsertTransaction($ssColVal);

        # Insert Serial Number Relation
        $snRelColVal = [
            'sn_sc_id' => 1,
            'sn_ss_id' => $ssDao->getLastInsertId(),
            'sn_rel_id' => null,
            'sn_prefix' => 'REL',
            'sn_separator' => '-',
            'sn_postfix' => '',
            'sn_yearly' => 'Y',
            'sn_monthly' => 'N',
            'sn_length' => 10,
            'sn_increment' => 1,
            'sn_active' => 'Y',
        ];
        $snDao = new SerialNumberDao();
        $snDao->doInsertTransaction($snRelColVal);
        # Insert Serial Number Contact Person
        $snCpColVal = [
            'sn_sc_id' => 2,
            'sn_ss_id' => $ssDao->getLastInsertId(),
            'sn_rel_id' => null,
            'sn_prefix' => 'CP',
            'sn_separator' => '-',
            'sn_postfix' => '',
            'sn_yearly' => 'Y',
            'sn_monthly' => 'N',
            'sn_length' => 10,
            'sn_increment' => 1,
            'sn_active' => 'Y',
        ];
        $snDao->doInsertTransaction($snCpColVal);
        # Insert Relation
        $sn = new SerialNumber($ssDao->getLastInsertId());
        $relNumber = $sn->loadNumber('Relation');
        $relColVal = [
            'rel_number' => $relNumber,
            'rel_ss_id' => $ssDao->getLastInsertId(),
            'rel_name' => $this->getStringParameter('rel_name'),
            'rel_short_name' => $this->getStringParameter('rel_short_name'),
            'rel_website' => $this->getStringParameter('rel_website'),
            'rel_email' => $this->getStringParameter('rel_email'),
            'rel_phone' => $this->getStringParameter('rel_phone'),
            'rel_owner' => 'Y',
            'rel_active' => 'Y',
        ];
        $relDao = new RelationDao();
        $relDao->doInsertTransaction($relColVal);

        # Insert Office
        $ofColVal = [
            'of_rel_id' => $relDao->getLastInsertId(),
            'of_name' => $this->getStringParameter('rel_short_name'),
            'of_main' => 'Y',
            'of_invoice' => 'Y',
            'of_cnt_id' => $this->getIntParameter('of_cnt_id'),
            'of_stt_id' => $this->getIntParameter('of_stt_id'),
            'of_cty_id' => $this->getIntParameter('of_cty_id'),
            'of_dtc_id' => $this->getIntParameter('of_dtc_id'),
            'of_address' => $this->getStringParameter('of_address'),
            'of_postal_code' => $this->getStringParameter('of_postal_code'),
            'of_longitude' => $this->getFloatParameter('of_longitude'),
            'of_latitude' => $this->getFloatParameter('of_latitude'),
            'of_active' => $this->getStringParameter('of_active', 'Y'),
        ];
        $ofDao = new OfficeDao();
        $ofDao->doInsertTransaction($ofColVal);

        # Insert Contact Person
        $cpNumber = $sn->loadNumber('ContactPerson');
        $cpColVal = [
            'cp_number' => $cpNumber,
            'cp_name' => $this->getStringParameter('cp_name'),
            'cp_email' => $this->getStringParameter('cp_email'),
            'cp_phone' => $this->getStringParameter('cp_phone'),
            'cp_of_id' => $ofDao->getLastInsertId(),
            'cp_office_manager' => 'Y',
            'cp_active' => $this->getStringParameter('cp_active', 'Y'),
        ];
        $cpDao = new ContactPersonDao();
        $cpDao->doInsertTransaction($cpColVal);

        # insert picture
        if ($file !== null) {
            $colVal = [
                'doc_dct_id' => 1,
                'doc_ss_id' => $ssDao->getLastInsertId(),
                'doc_group_reference' => $ssDao->getLastInsertId(),
                'doc_type_reference' => null,
                'doc_file_name' => $logoName,
                'doc_description' => $logoName,
                'doc_file_size' => $file->getSize(),
                'doc_file_type' => $file->getClientOriginalExtension(),
                'doc_public' => $this->getStringParameter('doc_public', 'Y'),
            ];
            $docDao = new DocumentDao();
            $docDao->doInsertTransaction($colVal);
            $upload = new FileUpload($docDao->getLastInsertId());
            $upload->upload($file);
        }

        return $ssDao->getLastInsertId();
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        $ssColVal = [
            'ss_lg_id' => $this->getIntParameter('ss_lg_id'),
            'ss_cur_id' => $this->getIntParameter('ss_cur_id'),
            'ss_decimal_number' => $this->getIntParameter('ss_decimal_number'),
            'ss_decimal_separator' => $this->getStringParameter('ss_decimal_separator'),
            'ss_thousand_separator' => $this->getStringParameter('ss_thousand_separator'),
            'ss_system' => 'N',
            'ss_active' => $this->getStringParameter('ss_active', 'Y'),
        ];

        #update logo
        $file = $this->getFileParameter('ss_logo');
        if ($file !== null) {
            $logoName = strtolower($this->getStringParameter('ss_name_space')) . time() . '.' . $file->getClientOriginalExtension();
            $ssColVal['ss_logo'] = $logoName;
            # Delete logo if exist
            $docDao = new DocumentDao();
            if ($this->isValidParameter('doc_id') === true) {
                $docDao->doDeleteTransaction($this->getIntParameter('doc_id'));
            }
            $dct = DocumentTypeDao::getByCode('systemsetting', 'logo');
            $docColVal = [
                'doc_dct_id' => $dct['dct_id'],
                'doc_ss_id' => $this->getDetailReferenceValue(),
                'doc_group_reference' => $this->getDetailReferenceValue(),
                'doc_type_reference' => null,
                'doc_file_name' => $logoName,
                'doc_description' => $logoName,
                'doc_file_size' => $file->getSize(),
                'doc_file_type' => $file->getClientOriginalExtension(),
                'doc_public' => $this->getStringParameter('doc_public', 'Y'),
            ];
            $docDao->doInsertTransaction($docColVal);
            $upload = new FileUpload($docDao->getLastInsertId());
            $upload->upload($file);
        }

        #update icon
        $fileIcon = $this->getFileParameter('ss_icon');
        if ($fileIcon !== null) {
            $iconName = strtolower($this->getStringParameter('ss_name_space')) . 'Icon' . time() . '.' . $fileIcon->getClientOriginalExtension();
            $ssIcVal['ss_icon'] = $iconName;
            # Delete logo if exist
            $DocDao = new DocumentDao();
            if ($this->isValidParameter('doc_ic_id') === true) {
                $DocDao->doDeleteTransaction($this->getIntParameter('doc_ic_id'));
            }
            $dct = DocumentTypeDao::getByCode('systemsetting', 'icon');
            $docIcVal = [
                'doc_dct_id' => $dct['dct_id'],
                'doc_ss_id' => $this->getDetailReferenceValue(),
                'doc_group_reference' => $this->getDetailReferenceValue(),
                'doc_type_reference' => null,
                'doc_file_name' => $iconName,
                'doc_description' => $iconName,
                'doc_file_size' => $fileIcon->getSize(),
                'doc_file_type' => $fileIcon->getClientOriginalExtension(),
                'doc_public' => $this->getStringParameter('doc_public', 'Y'),
            ];
            $DocDao->doInsertTransaction($docIcVal);
            $upload = new FileUpload($DocDao->getLastInsertId());
            $upload->upload($fileIcon);
        }

        $ssDao = new SystemSettingDao();
        $ssDao->doUpdateTransaction($this->getDetailReferenceValue(), $ssColVal);

    }

    /**
     * Abstract function to load the data.
     *
     * @return array
     */
    public function loadData(): array
    {
        $wheres = [];
        $wheres[] = "(rel.rel_owner = 'Y')";
        $wheres[] = "(o.of_main = 'Y')";
        $wheres[] = "(cp.cp_office_manager = 'Y')";
        $wheres[] = '(ss.ss_id = ' . $this->getDetailReferenceValue() . ')';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT ss.ss_id, ss.ss_relation, ss.ss_lg_id, ss.ss_decimal_number, ss.ss_decimal_separator, ss.ss_thousand_separator,
                      ss.ss_name_space, ss.ss_system, ss.ss_active, lg.lg_locale, lg.lg_iso, rel.rel_id, rel.rel_name,
                      rel.rel_short_name, rel.rel_website, rel.rel_email, o.of_id, o.of_name, o.of_invoice,
                      o.of_cnt_id, cnt.cnt_name as of_country, o.of_stt_id, stt.stt_name as of_state, o.of_cty_id, cty.cty_name as of_city,
                      o.of_dtc_id, dtc.dtc_name as of_district, o.of_address, o.of_postal_code, o.of_longitude, o.of_latitude,
                      cp.cp_name, cp.cp_email, cp.cp_phone, cp.cp_id, ss.ss_logo, ss.ss_cur_id, cur.cur_name as ss_currency, ss.ss_icon
                        FROM system_setting as ss INNER JOIN
                        relation as rel ON rel.rel_ss_id = ss.ss_id INNER JOIN
                            office as o ON o.of_rel_id = rel.rel_id INNER JOIN
                            country as cnt ON o.of_cnt_id = cnt.cnt_id INNER JOIN
                            state as stt ON o.of_stt_id = stt.stt_id INNER JOIN
                            city as cty ON o.of_cty_id = cty.cty_id INNER JOIN
                            district as dtc ON o.of_dtc_id = dtc.dtc_id INNER JOIN
                            contact_person as cp ON o.of_id = cp.cp_of_id INNER JOIN
                            languages as lg ON ss.ss_lg_id = lg.lg_id INNER JOIN
                            currency as cur ON ss.ss_cur_id = cur.cur_id ' . $strWheres . '
                        GROUP BY ss.ss_id, ss.ss_relation, ss.ss_lg_id, ss.ss_decimal_number, ss.ss_decimal_separator, ss.ss_thousand_separator,
                      ss.ss_name_space, ss.ss_system, ss.ss_active, lg.lg_locale, lg.lg_iso, rel.rel_id, rel.rel_name,
                      rel.rel_short_name, rel.rel_website, rel.rel_email, o.of_id, o.of_name, o.of_invoice,
                      o.of_cnt_id, cnt.cnt_name, o.of_stt_id, stt.stt_name, o.of_cty_id, cty.cty_name,
                      o.of_dtc_id, dtc.dtc_name, o.of_address, o.of_postal_code, o.of_longitude, o.of_latitude,
                      cp.cp_name, cp.cp_email, cp.cp_phone, cp.cp_id, ss.ss_logo, ss.ss_cur_id, cur.cur_name';
        $sqlResults = DB::select($query);
        $result = [];
        if (\count($sqlResults) === 1) {
            $result = DataParser::objectToArray($sqlResults[0]);
        }

        return $result;
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
            $this->Tab->addPortlet('general', $this->getAddressFieldSet());
            $this->Tab->addPortlet('general', $this->getSettingFieldSet());
        } else {
            $this->Tab->addPortlet('general', $this->getSystemSettingFieldSet());
            $this->Tab->addPortlet('general', $this->getLogoFieldSet());
            $this->Tab->addPortlet('general', $this->getIconFieldSet());
            $this->Tab->addPortlet('general', $this->getRelationFieldSet());
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
            $this->Validation->checkImage('ss_icon');
            $this->Validation->checkRequire('rel_name', 3, 255);
            $this->Validation->checkRequire('rel_short_name', 2, 5);
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
            $this->Validation->checkRequire('cp_email', 3, 255);
            $this->Validation->checkEmail('cp_email');
        }

    }


    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getGeneralFieldSet(): Portlet
    {

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $fieldSet->addField(Trans::getWord('relation'), $this->Field->getText('rel_name', $this->getStringParameter('rel_name')), true);
        $fieldSet->addField(Trans::getWord('nameSpace'), $this->Field->getText('ss_name_space', $this->getStringParameter('ss_name_space')), true);
        $fieldSet->addField(Trans::getWord('website'), $this->Field->getText('rel_website', $this->getStringParameter('rel_website')));
        $fieldSet->addField(Trans::getWord('email'), $this->Field->getText('rel_email', $this->getStringParameter('rel_email')));
        $fieldSet->addField(Trans::getWord('phone'), $this->Field->getText('rel_phone', $this->getStringParameter('rel_phone')));
        $fieldSet->addField(Trans::getWord('logo'), $this->Field->getFile('ss_logo', ''), true);
        $fieldSet->addField(Trans::getWord('picName'), $this->Field->getText('cp_name', $this->getStringParameter('cp_name')), true);
        $fieldSet->addField(Trans::getWord('picEmail'), $this->Field->getText('cp_email', $this->getStringParameter('cp_email')), true);
        $fieldSet->addField(Trans::getWord('picPhone'), $this->Field->getText('cp_phone', $this->getStringParameter('cp_phone')));

        # Create a portlet box.
        $portlet = new Portlet('SsRelPtl', Trans::getWord('relation'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(4, 6, 12);

        return $portlet;
    }

    /**
     * Function to get the address Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getAddressFieldSet(): Portlet
    {
        # Create custom field.
        $countryField = $this->Field->getSingleSelect('country', 'of_country', $this->getStringParameter('of_country'));
        $countryField->setHiddenField('of_cnt_id', $this->getIntParameter('of_cnt_id'));
        $countryField->setDetailReferenceCode('cnt_id');
        $countryField->addClearField('of_state');
        $countryField->addClearField('of_stt_id');
        $countryField->addClearField('of_city');
        $countryField->addClearField('of_cty_id');
        $countryField->addClearField('of_district');
        $countryField->addClearField('of_dtc_id');

        $stateField = $this->Field->getSingleSelect('state', 'of_state', $this->getStringParameter('of_state'));
        $stateField->setHiddenField('of_stt_id', $this->getIntParameter('of_stt_id'));
        $stateField->setDetailReferenceCode('stt_id');
        $stateField->addParameterById('stt_cnt_id', 'of_cnt_id', Trans::getWord('country'));
        $stateField->addClearField('of_city');
        $stateField->addClearField('of_cty_id');
        $stateField->addClearField('of_district');
        $stateField->addClearField('of_dtc_id');

        $cityField = $this->Field->getSingleSelect('city', 'of_city', $this->getStringParameter('of_city'));
        $cityField->setHiddenField('of_cty_id', $this->getIntParameter('of_cty_id'));
        $cityField->setDetailReferenceCode('cty_id');
        $cityField->addParameterById('cty_cnt_id', 'of_cnt_id', Trans::getWord('country'));
        $cityField->addParameterById('cty_stt_id', 'of_stt_id', Trans::getWord('state'));
        $cityField->addClearField('of_district');
        $cityField->addClearField('of_dtc_id');

        $districtField = $this->Field->getSingleSelect('district', 'of_district', $this->getStringParameter('of_district'));
        $districtField->setHiddenField('of_dtc_id', $this->getIntParameter('of_dtc_id'));
        $districtField->setDetailReferenceCode('dtc_id');
        $districtField->addParameterById('dtc_cnt_id', 'of_cnt_id', Trans::getWord('country'));
        $districtField->addParameterById('dtc_stt_id', 'of_stt_id', Trans::getWord('state'));
        $districtField->addParameterById('dtc_cty_id', 'of_cty_id', Trans::getWord('city'));


        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $fieldSet->addField(Trans::getWord('shortName'), $this->Field->getText('rel_short_name', $this->getStringParameter('rel_short_name')), true);
        $fieldSet->addField(Trans::getWord('country'), $countryField, true);
        $fieldSet->addField(Trans::getWord('state'), $stateField, true);
        $fieldSet->addField(Trans::getWord('city'), $cityField, true);
        $fieldSet->addField(Trans::getWord('district'), $districtField, true);
        $fieldSet->addField(Trans::getWord('address'), $this->Field->getText('of_address', $this->getStringParameter('of_address')), true);
        $fieldSet->addField(Trans::getWord('postalCode'), $this->Field->getText('of_postal_code', $this->getStringParameter('of_postal_code')));
        $fieldSet->addField(Trans::getWord('longitude'), $this->Field->getText('of_longitude', $this->getStringParameter('of_longitude')));
        $fieldSet->addField(Trans::getWord('latitude'), $this->Field->getText('of_latitude', $this->getStringParameter('of_latitude')));
        # Create a portlet box.
        $portlet = new Portlet('SsOfPtl', Trans::getWord('address'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(4, 6, 12);

        return $portlet;
    }

    /**
     * Function to get the general Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getSettingFieldSet(): Portlet
    {
        # Create Fields.
        $languageField = $this->Field->getSelect('ss_lg_id', $this->getIntParameter('ss_lg_id'));
        $languageField->addOptions(LanguagesDao::loadActiveData(), 'lg_locale', 'lg_id');

        # Create Decimal Separator field.
        $decimalSeparator = $this->Field->getSelect('ss_decimal_separator', $this->getStringParameter('ss_decimal_separator'));
        $decimalSeparator->addOption(Trans::getWord('comma') . ' (,)', ',');
        $decimalSeparator->addOption(Trans::getWord('dot') . ' (.)', '.');

        # Create Thousand Separator
        $thousandSeparator = $this->Field->getSelect('ss_thousand_separator', $this->getStringParameter('ss_thousand_separator'));
        $thousandSeparator->addOption(Trans::getWord('comma') . ' (,)', ',');
        $thousandSeparator->addOption(Trans::getWord('dot') . ' (.)', '.');

        # Create currency
        $currencyField = $this->Field->getSingleSelect('currency', 'ss_currency', $this->getStringParameter('ss_currency'));
        $currencyField->setHiddenField('ss_cur_id', $this->getIntParameter('ss_cur_id'));
        $currencyField->setEnableDetailButton(false);
        $currencyField->setEnableNewButton(false);

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);

        $fieldSet->addField(Trans::getWord('language'), $languageField, true);
        $fieldSet->addField(Trans::getWord('currency'), $currencyField, true);
        $fieldSet->addField(Trans::getWord('decimalNumber'), $this->Field->getText('ss_decimal_number', $this->getStringParameter('ss_decimal_number')), true);
        $fieldSet->addField(Trans::getWord('decimalSeparator'), $decimalSeparator, true);
        $fieldSet->addField(Trans::getWord('thousandSeparator'), $thousandSeparator, true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('ss_active', $this->getStringParameter('ss_active')));

        # Create a portlet box.
        $portlet = new Portlet('SsSettingPtl', Trans::getWord('setting'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(4, 6, 12);

        return $portlet;
    }

    /**
     * Function to get the system setting Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getSystemSettingFieldSet(): Portlet
    {
        # Create Fields.
        $languageField = $this->Field->getSelect('ss_lg_id', $this->getIntParameter('ss_lg_id'));
        $languageField->addOptions(LanguagesDao::loadActiveData(), 'lg_locale', 'lg_id');

        # Create Decimal Separator field.
        $decimalSeparator = $this->Field->getSelect('ss_decimal_separator', $this->getStringParameter('ss_decimal_separator'));
        $decimalSeparator->addOption(Trans::getWord('comma') . ' (,)', ',');
        $decimalSeparator->addOption(Trans::getWord('dot') . ' (.)', '.');

        # Create Thousand Separator
        $thousandSeparator = $this->Field->getSelect('ss_thousand_separator', $this->getStringParameter('ss_thousand_separator'));
        $thousandSeparator->addOption(Trans::getWord('comma') . ' (,)', ',');
        $thousandSeparator->addOption(Trans::getWord('dot') . ' (.)', '.');

        # Create currency
        $currencyField = $this->Field->getSingleSelect('currency', 'ss_currency', $this->getStringParameter('ss_currency'));
        $currencyField->setHiddenField('ss_cur_id', $this->getIntParameter('ss_cur_id'));
        $currencyField->setEnableDetailButton(false);
        $currencyField->setEnableNewButton(false);
        # Create namespace field

        $nameSpaceField = $this->Field->getText('ss_name_space', $this->getStringParameter('ss_name_space'));
        $nameSpaceField->setReadOnly();

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('nameSpace'), $nameSpaceField, true);
        $fieldSet->addField(Trans::getWord('language'), $languageField, true);
        $fieldSet->addField(Trans::getWord('currency'), $currencyField, true);
        $fieldSet->addField(Trans::getWord('decimalNumber'), $this->Field->getText('ss_decimal_number', $this->getStringParameter('ss_decimal_number')), true);
        $fieldSet->addField(Trans::getWord('decimalSeparator'), $decimalSeparator, true);
        $fieldSet->addField(Trans::getWord('thousandSeparator'), $thousandSeparator, true);
        $fieldSet->addField(Trans::getWord('active'), $this->Field->getYesNo('ss_active', $this->getStringParameter('ss_active')));

        # Create a portlet box.
        $portlet = new Portlet('SsSettingPtl', Trans::getWord('setting'));
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(12, 6);

        return $portlet;
    }

    /**
     * Function to get the logo field set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getLogoFieldSet(): Portlet
    {
        $docData = DocumentDao::loadSystemSettingLogo($this->getDetailReferenceValue(), 'logo');
        $profile = new ProfileGeneral('SsLogo');
        $profile->setHeight(200);
        $profile->setGridDimension(12, 12, 12);
        $data = [
            'title' => $this->getStringParameter('rel_name'),
            'img_path' => $docData['path'],
        ];
        $profile->setData($data);
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getWord('logo'), $this->Field->getFile('ss_logo', ''));
        $fieldSet->addHiddenField($this->Field->getHidden('doc_id', $docData['doc_id']));
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
        $docData = DocumentDao::loadSystemSettingLogo($this->getDetailReferenceValue(), 'icon');
        $path = '';
        $docId = null;
        if (empty($docData) === false) {
            $path = $docData['path'];
            $docId = $docData['doc_id'];
        }
        $icon = new ProfileGeneral('SsIcon');
        $icon->setHeight(200);
        $icon->setGridDimension(12, 12, 12);
        $data = [
            'title' => $this->getStringParameter('rel_name'),
            'img_path' => $path,
        ];
        $icon->setData($data);
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(12, 12, 12);
        $fieldSet->addField(Trans::getWord('icon'), $this->Field->getFile('ss_icon', ''));
        $fieldSet->addHiddenField($this->Field->getHidden('doc_ic_id', $docId));
        # Create a portlet box.
        $portlet = new Portlet('RelIconPtl', Trans::getWord('icon'));
        $portlet->addText($icon->createView());
        $portlet->addFieldSet($fieldSet);
        $portlet->setGridDimension(6, 4, 4);

        return $portlet;
    }

    /**
     * Function to get the Relation Field Set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getRelationFieldSet(): Portlet
    {
        # Create custom field.
        $countryField = $this->Field->getSingleSelect('country', 'of_country', $this->getStringParameter('of_country'));
        $countryField->setHiddenField('of_cnt_id', $this->getIntParameter('of_cnt_id'));
        $countryField->setEnableDetailButton(false);
        $countryField->setReadOnly();

        $stateField = $this->Field->getSingleSelect('state', 'of_state', $this->getStringParameter('of_state'));
        $stateField->setHiddenField('of_stt_id', $this->getIntParameter('of_stt_id'));
        $stateField->setEnableDetailButton(false);
        $stateField->setReadOnly();

        $cityField = $this->Field->getSingleSelect('city', 'of_city', $this->getStringParameter('of_city'));
        $cityField->setHiddenField('of_cty_id', $this->getIntParameter('of_cty_id'));
        $cityField->setEnableDetailButton(false);
        $cityField->setReadOnly();

        $districtField = $this->Field->getSingleSelect('district', 'of_district', $this->getStringParameter('of_district'));
        $districtField->setHiddenField('of_dtc_id', $this->getIntParameter('of_dtc_id'));
        $districtField->setEnableDetailButton(false);
        $districtField->setReadOnly();
        # name Field
        $nameField = $this->Field->getText('rel_name', $this->getStringParameter('rel_name'));
        $nameField->setReadOnly();
        # Website Field
        $shortNameField = $this->Field->getText('rel_short_name', $this->getStringParameter('rel_short_name'));
        $shortNameField->setReadOnly();
        # Website Field
        $websiteField = $this->Field->getText('rel_website', $this->getStringParameter('rel_website'));
        $websiteField->setReadOnly();
        # Email Field
        $emailField = $this->Field->getText('rel_email', $this->getStringParameter('rel_email'));
        $emailField->setReadOnly();
        # Phone Field
        $phoneField = $this->Field->getText('rel_phone', $this->getStringParameter('rel_phone'));
        $phoneField->setReadOnly();
        # PIc Name Field
        $picNameField = $this->Field->getText('cp_name', $this->getStringParameter('cp_name'));
        $picNameField->setReadOnly();
        #Pic Email Field
        $picEmailField = $this->Field->getText('cp_email', $this->getStringParameter('cp_email'));
        $picEmailField->setReadOnly();
        # Pic Phone Field
        $picPhoneField = $this->Field->getText('cp_phone', $this->getStringParameter('cp_phone'));
        $picPhoneField->setReadOnly();
        # Address Field
        $addressField = $this->Field->getText('of_address', $this->getStringParameter('of_address'));
        $addressField->setReadOnly();
        # Postal Code
        $postalCodeField = $this->Field->getText('of_postal_code', $this->getStringParameter('of_postal_code'));
        $postalCodeField->setReadOnly();
        # Longitude Field
        $longitudeField = $this->Field->getText('of_latitude', $this->getStringParameter('of_latitude'));
        $longitudeField->setReadOnly();
        # Latitude Field
        $latitudeField = $this->Field->getText('of_longitude', $this->getStringParameter('of_longitude'));
        $latitudeField->setReadOnly();

        # Add field to field set
        $fieldSet = new FieldSet($this->Validation);
        $fieldSet->setGridDimension(6, 6);

        $fieldSet->addField(Trans::getWord('relation'), $nameField, true);
        $fieldSet->addField(Trans::getWord('shortName'), $shortNameField, true);
        $fieldSet->addField(Trans::getWord('website'), $websiteField);
        $fieldSet->addField(Trans::getWord('email'), $emailField);
        $fieldSet->addField(Trans::getWord('phone'), $phoneField);
        $fieldSet->addField(Trans::getWord('picName'), $picNameField, true);
        $fieldSet->addField(Trans::getWord('picEmail'), $picEmailField, true);
        $fieldSet->addField(Trans::getWord('picPhone'), $picPhoneField);
        $fieldSet->addField(Trans::getWord('country'), $countryField, true);
        $fieldSet->addField(Trans::getWord('state'), $stateField, true);
        $fieldSet->addField(Trans::getWord('city'), $cityField, true);
        $fieldSet->addField(Trans::getWord('district'), $districtField, true);
        $fieldSet->addField(Trans::getWord('address'), $addressField, true);
        $fieldSet->addField(Trans::getWord('postalCode'), $postalCodeField);
        $fieldSet->addField(Trans::getWord('longitude'), $latitudeField);
        $fieldSet->addField(Trans::getWord('latitude'), $longitudeField);
        # Create a portlet box.
        $portlet = new Portlet('SsRelPtl', Trans::getWord('relation'));
        $portlet->addFieldSet($fieldSet);

        return $portlet;
    }

}
