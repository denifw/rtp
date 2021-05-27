<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Frame\Gui;

/**
 *
 *
 * @package    app
 * @subpackage Frame\Gui
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class TableDatas extends Table
{
    /**
     * Attribute to store the row per page.
     *
     * @var integer $RowsPerPage
     */
    private $RowsPerPage = 10;

    /**
     * Attribute to enable/disable data table's ordering function
     *
     * @var bool $Ordering
     */
    private $Ordering = true;

    /**
     * Constructor for the table class.
     *
     * @param string $tableId To store the id of the table.
     */
    public function __construct(string $tableId)
    {
        parent::__construct($tableId);
        $this->addTableAttribute('width', '100%');
    }

    /**
     * Will create the complete html table and return the result.
     *
     * All checks that are necessary to make a correct table will be set done here
     *
     * @return string The complete html table.
     */
    public function createTable(): string
    {
        $table = parent::createTable();
        $table .= $this->getJavascript();

        return $table;
    }

    /**
     * Function to set number of rows per page.
     *
     * @param int $number
     *
     * @return void
     */
    public function setRowsPerPage(int $number): void
    {
        $this->RowsPerPage = $number;
    }

    /**
     * Function to disable ordering function
     *
     * @return void
     */
    public function setDisableOrdering(): void
    {
        $this->Ordering = false;
    }

    /**
     * Returns a string if the javascript must be loaded.
     *
     * @return string
     */
    private function getJavascript(): string
    {
        $javascript = '<script type="text/javascript">';
        $javascript .= '$(document).ready( function () { $(\'#' . $this->TableId . '\').DataTable({
        "pageLength": ' . $this->RowsPerPage . ',
         "ordering": \'' . $this->Ordering. '\',
        }); } );';
        $javascript .= '</script>';

        return $javascript;
    }
}
