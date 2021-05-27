<?php
/**
 * /**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Document;

/**
 * Class to generate field base on the table set.
 *
 * @package    app
 * @subpackage Util\Gui
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2017 C-Book
 */
class ExcelProperties
{
    /**
     * Property to store the creator of the file.
     *
     * @var string $Creator
     */
    private $Creator = 'MBS System';

    /**
     * Property to store the modified by of the file.
     *
     * @var string $ModifiedBy
     */
    private $ModifiedBy = 'MBS System';

    /**
     * Property to store the title of the file.
     *
     * @var string $Title
     */
    private $Title = 'MBS Document';

    /**
     * Property to store the subject of the file.
     *
     * @var string $Subject
     */
    private $Subject = 'MBS Report';

    /**
     * Property to store the description of the file.
     *
     * @var string $Description
     */
    private $Description = 'This Document generated using MBS system.';

    /**
     * Property to store the keyword of the file.
     *
     * @var string $Keyword
     */
    private $Keyword = 'MBS System.';

    /**
     * Property to store the category of the file.
     *
     * @var string $Category
     */
    private $Category = 'MBS';

    /**
     * Function to get the creator of the file
     *
     * @return string
     */
    public function getCreator(): string
    {
        return $this->Creator;
    }

    /**
     * Function to set the creator of the file.
     *
     * @param string $Creator
     *
     * @return void
     */
    public function setCreator(string $Creator): void
    {
        $this->Creator = $Creator;
    }

    /**
     * Function to get the modified file.
     *
     * @return string
     */
    public function getModifiedBy(): string
    {
        return $this->ModifiedBy;
    }

    /**
     * Function to set the modified file.
     *
     * @param string $ModifiedBy
     *
     * @return void
     */
    public function setModifiedBy(string $ModifiedBy): void
    {
        $this->ModifiedBy = $ModifiedBy;
    }

    /**
     * Function to get the title of the file.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->Title;
    }

    /**
     * Function to set the title of the file.
     *
     * @param string $Title
     *
     * @return void
     */
    public function setTitle(string $Title): void
    {
        $this->Title = $Title;
    }

    /**
     * Function to get the subject of the file.
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->Subject;
    }

    /**
     * Function to set the subject of the file.
     *
     * @param string $Subject
     *
     * @return void
     */
    public function setSubject(string $Subject): void
    {
        $this->Subject = $Subject;
    }

    /**
     * Function to get the description file.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->Description;
    }

    /**
     * Function to set the description file.
     *
     * @param string $Description
     *
     * @return void
     */
    public function setDescription(string $Description): void
    {
        $this->Description = $Description;
    }

    /**
     * Function to get the keyword of the file.
     *
     * @return string
     */
    public function getKeyword(): string
    {
        return $this->Keyword;
    }

    /**
     * Function to set the keyword of the file.
     *
     * @param string $Keyword
     *
     * @return void
     */
    public function setKeyword(string $Keyword): void
    {
        $this->Keyword = $Keyword;
    }

    /**
     * Function to get the category of the file.
     *
     * @return string
     */
    public function getCategory(): string
    {
        return $this->Category;
    }

    /**
     * Function to set the category of the file.
     *
     * @param string $Category
     *
     * @return void
     */
    public function setCategory(string $Category): void
    {
        $this->Category = $Category;
    }


}
