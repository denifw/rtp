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
class RelationSession
{

    /**
     * Property to store all the right for current page.
     *
     * @var array
     */
    private $Data;

    /**
     * Base model constructor.
     *
     * @param array $data to store the data.
     */
    public function __construct(array $data)
    {
        $this->Data = $data;
    }

    /**
     * Function to get user id
     *
     * @param array $data to store the data.
     * @return void
     */
    public function setData(array $data): void
    {
        $this->Data = $data;
    }

    /**
     * Function to get user id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->getIntValue('rel_id');
    }

    /**
     * Function to get user name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getStringValue('rel_name');
    }

    /**
     * Function to get user Short Name
     *
     * @return string
     */
    public function getShortName(): string
    {
        return $this->getStringValue('rel_short_name');
    }

    /**
     * Function to get contact person id
     *
     * @return int
     */
    public function getPersonId(): int
    {
        return $this->getIntValue('cp_id');
    }

    /**
     * Function to get contact person name
     *
     * @return string
     */
    public function getPersonName(): string
    {
        return $this->getStringValue('cp_name');
    }

    /**
     * Function to get office id
     *
     * @return int
     */
    public function getOfficeId(): int
    {
        return $this->getIntValue('of_id');
    }

    /**
     * Function to get office name
     *
     * @return string
     */
    public function getOfficeName(): string
    {
        return $this->getStringValue('of_name');
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

    /**
     * Function to get user id
     *
     * @param string $keyWord To store the keyword.
     *
     * @return int
     */
    private function getIntValue(string $keyWord): int
    {
        if (array_key_exists($keyWord, $this->Data) === true && $this->Data[$keyWord] !== null && is_numeric($this->Data[$keyWord]) === true) {
            return (int)$this->Data[$keyWord];
        }
        return 0;
    }

}
