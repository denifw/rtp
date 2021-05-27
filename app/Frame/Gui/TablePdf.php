<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Spada
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2018 spada
 */

namespace App\Frame\Gui;

use App\Frame\Exceptions\Message;

/**
 * Class to build layout like card
 *
 * @package    app
 * @subpackage App\Frame\Gui
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2018 Spada
 */
class TablePdf extends Table
{
    /**
     * Constructor for the table class.
     *
     * @param string $tableId To store the id of the table.
     */
    public function __construct(string $tableId)
    {
        parent::__construct($tableId);
        $this->addTableAttribute('class', 'content-table');
        $this->addTableAttribute('style', 'font-weight: bold');
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
        foreach ($this->ColumnIds as $column) {
            if (array_key_exists($column, $this->HeaderAttributes) === false) {
                $this->HeaderAttributes[$column]['style'] = 'font-weight: bold;';
            } else {
                if (array_key_exists('style', $this->HeaderAttributes[$column]) === false) {
                    $this->HeaderAttributes[$column]['style'] = 'font-weight: bold;';
                } else {
                    $oldStyle = $this->HeaderAttributes[$column]['style'];
                    $this->HeaderAttributes[$column]['style'] = 'font-weight: bold; ' . $oldStyle;
                }
            }
        }

        return parent::createTable();
    }

}
