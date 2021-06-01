<?php
/**
 * Contains code written by the PT Spada Media Informatika.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Matalogix
 * @author    Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright 2020 PT Spada Media Informatika
 */

namespace App\Frame\System\Session;

/**
 *
 *
 * @package    app
 * @subpackage Frame\System
 * @author     Deni Firdaus Waruwu <deni.fw@spada-informatika.com>
 * @copyright  2020 PT Spada Media Informatika
 */
class UserSession
{

    /**
     * Property to store all the right for current page.
     *
     * @var array
     */
    private $Data;

    /**
     * Property to store all the system settings data
     *
     * @var SystemSettingSession $Settings
     */
    public $Settings;


    /**
     * Property to store all the relation data
     *
     * @var RelationSession $Relation
     */
    public $Relation;

    /**
     * Base model constructor.
     *
     * @param array $user To store the user data.
     *
     */
    public function __construct(array $user = [])
    {
        $this->Data = $user;
        if (empty($this->Data) === true && session()->exists('user') === true) {
            $this->Data = session('user', []);
        }
        $this->Settings = new SystemSettingSession($this->Data);
        $this->Relation = new RelationSession($this->Data);
    }

    /**
     * Function to get user id
     *
     * @return bool
     */
    public function isSet(): bool
    {
        return !empty($this->Data);
    }

    /**
     * Function to get user id
     *
     * @param array $data to store the data.
     *
     * @return void
     */
    public function setData(array $data): void
    {
        $this->Data = $data;
        $this->Settings->setData($this->Data);
        $this->Relation->setData($this->Data);
    }

    /**
     * Function to get user id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->getStringValue('us_id');
    }

    /**
     * Function to get user id
     *
     * @return string
     */
    public function getSsId(): string
    {
        return $this->Settings->getId();
    }

    /**
     * Function to get user id
     *
     * @return string
     */
    public function getRelId(): string
    {
        return $this->Relation->getId();
    }

    /**
     * Function to get user name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getStringValue('us_name');
    }

    /**
     * Function to get user userName
     *
     * @return string
     */
    public function getUserName(): string
    {
        return $this->getStringValue('us_username');
    }

    /**
     * Function to get user System
     *
     * @return bool
     */
    public function isUserSystem(): bool
    {
        $val = $this->getStringValue('us_system');
        return ($val === 'Y');
    }

    /**
     * Function to check is relation user own system or not
     *
     * @return bool
     */
    public function isOwnSystem(): bool
    {
        return $this->getRelId() === $this->Settings->getOwnerId();
    }

    /**
     * Function to get language uid
     *
     * @return string
     */
    public function getLanguageId(): string
    {
        return $this->getStringValue('us_lg_id');
    }

    /**
     * Function to get language
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->getStringValue('us_lg_locale');
    }

    /**
     * Function to get language ISO
     *
     * @return string
     */
    public function getLanguageIso(): string
    {
        return $this->getStringValue('us_lg_iso');
    }

    /**
     * Function to get User Menu Style
     *
     * @return string
     */
    public function getMenuStyle(): string
    {
        return $this->getStringValue('us_menu_style');
    }

    /**
     * Function to get User mapping id
     *
     * @return string
     */
    public function getMappingId(): string
    {
        return $this->getStringValue('ump_id');
    }

    /**
     * Function to get User login token
     *
     * @return string
     */
    public function getAuthToken(): string
    {
        return $this->getStringValue('ut_token');
    }

    /**
     * Function to get all user data
     *
     * @return array
     */
    public function getAllData(): array
    {
        return $this->Data;
    }

    /**
     * Function to get all user mapping
     *
     * @return array
     */
    public function getMapping(): array
    {
        if (array_key_exists('systems', $this->Data) === true && $this->Data['systems'] !== null) {
            return $this->Data['systems'];
        }
        return [];
    }


    /**
     * Function to check is user allow e-mail
     *
     * @return bool
     */
    public function isMappingEnabled(): bool
    {
        return !empty($this->getMapping());
    }

    /**
     * Function to get user id
     *
     * @param string $keyWord To store the keyword.
     *
     * @return string
     */
    private function getStringValue(string $keyWord): string
    {
        if (array_key_exists($keyWord, $this->Data) === true && $this->Data[$keyWord] !== null) {
            return $this->Data[$keyWord];
        }
        return '';
    }

}
