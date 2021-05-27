<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2019 PT Spada Media Informatika
 */

namespace App\Model\Detail\Setting;

use App\Frame\Document\FileUpload;
use App\Frame\Exceptions\Message;
use App\Frame\Formatter\DataParser;
use App\Frame\Formatter\Trans;
use App\Frame\Gui\Html\Buttons\ModalButton;
use App\Frame\Gui\Html\Labels\Paragraph;
use App\Frame\Gui\Icon;
use App\Frame\Gui\Modal;
use App\Frame\Gui\Table;
use App\Frame\Gui\Templates\ProfileGeneral;
use App\Frame\Mvc\AbstractFormModel;
use App\Frame\Gui\FieldSet;
use App\Frame\Gui\Portlet;
use App\Model\Dao\System\Document\DocumentDao;
use App\Model\Dao\System\Document\DocumentTypeDao;
use App\Model\Dao\System\LanguagesDao;
use App\Model\Dao\System\SystemSettingDao;
use App\Model\Dao\System\SystemSettingKeyDao;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

/**
 * Class to handle the creation of detail System page
 *
 * @package    app
 * @subpackage Model\Detail\Setting
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2019 PT Spada Media Informatika
 */
class System extends AbstractFormModel
{
    /**
     * Constructor to load when there is a new instance created.
     *
     * @param array $parameters To store the parameter from http.
     */
    public function __construct(array $parameters)
    {
        # Call parent construct.
        parent::__construct(get_class($this), 'system', 'ss_id');
        $this->setParameters($parameters);
        $this->setParameter('ss_id', $this->User->getSsId());
    }

    /**
     * Function to do the insert of the transaction.;
     *
     * @return int
     */
    protected function doInsert(): int
    {
        return 0;
    }

    /**
     * Function to do the update of the transaction.;
     *
     * @return void
     */
    protected function doUpdate(): void
    {
        if ($this->getFormAction() === 'doRefreshApi') {
            $a = Uuid::uuid3(Uuid::NAMESPACE_URL, time() . $this->User->getSsId() . $this->User->getId());
            $ssColVal = ['ss_api_key' => $a->getHex()];
            $ssDao = new SystemSettingDao();
            $ssDao->doUpdateTransaction($this->User->getSsId(), $ssColVal);
            $sskColVal = [
                'ssk_api_key' => $a->getHex(),
                'ssk_created_by' => $this->User->getId(),
                'ssk_ss_id' => $this->User->getSsId(),
            ];
            $sskDao = new SystemSettingKeyDao();
            $sskDao->doInsertTransaction($sskColVal);
        } else {
            $ssColVal = [
                'ss_lg_id' => $this->getIntParameter('ss_lg_id'),
                'ss_cur_id' => $this->getIntParameter('ss_cur_id'),
                'ss_decimal_number' => $this->getIntParameter('ss_decimal_number'),
                'ss_decimal_separator' => $this->getStringParameter('ss_decimal_separator'),
                'ss_thousand_separator' => $this->getStringParameter('ss_thousand_separator'),
                'ss_system' => 'N',
                'ss_active' => $this->getStringParameter('ss_active', 'Y'),
            ];

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
                    'doc_ss_id' => $this->User->getSsId(),
                    'doc_group_reference' => $this->User->getSsId(),
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
                $docIcVal = [
                    'doc_dct_id' => 81,
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
            $ssDao->doUpdateTransaction($this->User->getSsId(), $ssColVal);
        }
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
        $wheres[] = '(ss.ss_id = ' . $this->User->getSsId() . ')';
        $strWheres = ' WHERE ' . implode(' AND ', $wheres);
        $query = 'SELECT ss.ss_id, ss.ss_relation, ss.ss_lg_id, ss.ss_decimal_number, ss.ss_decimal_separator, ss.ss_thousand_separator,
                      ss.ss_name_space, ss.ss_system, ss.ss_active,ss.ss_api_key, lg.lg_locale, lg.lg_iso, rel.rel_id, rel.rel_name,
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
                      ss.ss_name_space, ss.ss_system, ss.ss_active,ss.ss_api_key, lg.lg_locale, lg.lg_iso, rel.rel_id, rel.rel_name,
                      rel.rel_short_name, rel.rel_website, rel.rel_email, o.of_id, o.of_name, o.of_invoice,
                      o.of_cnt_id, cnt.cnt_name, o.of_stt_id, stt.stt_name, o.of_cty_id, cty.cty_name,
                      o.of_dtc_id, dtc.dtc_name, o.of_address, o.of_postal_code, o.of_longitude, o.of_latitude,
                      cp.cp_name, cp.cp_email, cp.cp_phone, cp.cp_id, ss.ss_logo, ss.ss_cur_id, cur.cur_name';
        $sqlResults = DB::select($query);
        $result = [];
        if (count($sqlResults) === 1) {
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
            Message::throwMessage(Trans::getWord('pageNotFound', 'message'), 'ERROR');
        }
        $this->Tab->addPortlet('general', $this->getSystemSettingFieldSet());
        $this->Tab->addPortlet('general', $this->getLogoFieldSet());
        $this->Tab->addPortlet('general',$this->getIconFieldSet());
        $this->Tab->addPortlet('general', $this->getApiKeyFieldSet());
        $this->Tab->addPortlet('general', $this->getListSystemSettingKey());
        $this->setEnableCloseButton(false);
    }

    /**
     * Function to load the validation role.
     *
     * @return void
     */
    public function loadValidationRole(): void
    {
        $this->Validation->checkRequire('ss_name_space', 2, 255);
        $this->Validation->checkUnique('ss_name_space', 'system_setting', [
            'ss_id' => $this->getDetailReferenceValue()
        ]);
        $this->Validation->checkRequire('ss_lg_id');
        $this->Validation->checkRequire('ss_cur_id');
        $this->Validation->checkRequire('ss_decimal_number');
        $this->Validation->checkInt('ss_decimal_number', 0);
        $this->Validation->checkRequire('ss_decimal_separator');
        $this->Validation->checkRequire('ss_thousand_separator');
        if($this->isValidParameter('ss_logo') === true) {
            $this->Validation->checkImage('ss_logo');
        }
        if($this->isValidParameter('ss_icon') === true) {
            $this->Validation->checkImage('ss_icon');
        }
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
        $portlet->setGridDimension(12, 12);

        return $portlet;
    }

    /**
     * Function to get the logo field set.
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getLogoFieldSet(): Portlet
    {
        $docData = DocumentDao::loadSystemSettingLogo($this->getDetailReferenceValue(),'logo');
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

    private function getIconFieldSet(): Portlet
    {
        $docData = DocumentDao::loadSystemSettingLogo($this->getDetailReferenceValue(),'icon');
        $path = '';
        $docId = null;
        if(empty($docData) === false) {
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
     * Function to get list API KEY For System Setting
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getListSystemSettingKey(): Portlet
    {
        $table = new Table('RelOfTbl');
        $table->setHeaderRow([
            'ssk_api_key' => Trans::getWord('apiKey'),
            'ssk_us_name' => Trans::getWord('createdBy'),
            'ssk_created_on' => Trans::getWord('createdOn'),
        ]);
        $wheres = [];
        $wheres[] = '(ssk_ss_id = ' . $this->User->getSsId() . ')';
        $data = SystemSettingKeyDao::loadData($wheres);
        $table->addRows($data);

        #setting modal button
        $modalApprove = $this->getApproveModal();
        $this->View->addModal($modalApprove);
        $btnApprove = new ModalButton('btnRefreshApi', Trans::getWord('refreshApiKey'), $modalApprove->getModalId());
        $btnApprove->setIcon(Icon::Refresh)->btnDanger()->pullRight()->btnMedium();

        #Setting portlet
        $portlet = new Portlet('SSKApiKey', Trans::getWord('sskApiKey'));
        $portlet->setGridDimension(12, 12,12);
        $portlet->addTable($table);
        $portlet->addButton($btnApprove);
        return $portlet;
    }

    /**
     * Function to get and refresh API KEY For System Setting
     *
     * @return \App\Frame\Gui\Portlet
     */
    private function getApiKeyFieldSet(): Portlet
    {

        #Setting portlet
        $portlet = new Portlet('SsApiKey', Trans::getWord('apiKey'));
        $portlet->setGridDimension(12, 12,12);
        $portlet->addText('<h3 style="text-align: center;">'.$this->getStringParameter('ss_api_key').'</h3>');
        return $portlet;
    }

    /**
     * Function to get operator modal.
     *
     * @return Modal
     */
    protected function getApproveModal(): Modal
    {
        # Create Fields.
        $modal = new Modal('PiAppMdl', Trans::getWord('confirm'));
        $modal->setFormSubmit($this->getMainFormId(), 'doRefreshApi');
        $text = Trans::getWord('apiKeyRefreshConfirmation', 'message');
        $modal->setBtnOkName(Trans::getWord('yesRefresh'));
        $p = new Paragraph($text);
        $p->setAsLabelLarge()->setAlignCenter();
        $modal->addText($p);
        return $modal;
    }

}
