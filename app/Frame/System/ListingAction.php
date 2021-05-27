<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2017 C-Book
 */

namespace App\Frame\System;

use App\Frame\Exceptions\Message;
use App\Frame\Gui\Html\Fields\Select;

/**
 * Class to handle the creation of the action listing.
 *
 * @package    app
 * @subpackage Model\Listing
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2017 C-Book
 */
class ListingAction
{
    /**
     * Attribute to set the trigger to disable order by field.
     *
     * @var boolean $EnableOrderBy
     */
    private $EnableOrderBy = true;
    /**
     * Attribute to set object of pagination.
     *
     * @var \App\Frame\System\Pagination $Pagination
     */
    private $Pagination;
    /**
     * Attribute to set the order by field.
     *
     * @var array $OrderBy
     */
    private $OrderBy = [];

    /**
     * Attribute to set the selected order by.
     *
     * @var string $SelectedOrderBy
     */
    private $SelectedOrderBy = '';


    /**
     * Function to disable order by field.
     *
     * @param boolean $disable To set the the boolean value.
     *
     * @return void
     */
    public function setDisableOrderBy($disable = true): void
    {
        $this->EnableOrderBy = true;
        if ($disable === true) {
            $this->EnableOrderBy = false;
        }
    }

    /**
     * Function to set the pagination object.
     *
     * @param \App\Frame\System\Pagination $pagination To set the the pagination object.
     *
     * @return void
     */
    public function setPagination($pagination): void
    {
        if ($pagination instanceof Pagination === true) {
            $this->Pagination = $pagination;
        } else {
            Message::throwMessage('Parameter must be instance of the Pagination class.');
        }
    }

    /**
     * Get order by value.
     *
     * @return string
     */
    public function getSelectedOrderBy(): string
    {
        return $this->SelectedOrderBy;
    }

    /**
     * Function to set selected order by.
     *
     * @param string $value To set the value of order by.
     *
     * @return void
     */
    public function setSelectedOrderBy($value): void
    {
        $this->SelectedOrderBy = $value;
    }

    /**
     * Get order type value.
     *
     * @return string
     */
    public function getSelectedOrderType(): string
    {
        $result = 'asc';
        if (array_key_exists($this->SelectedOrderBy, $this->OrderBy) === true) {
            $result = $this->OrderBy[$this->SelectedOrderBy]['type'];
        }

        return $result;
    }

    /**
     * Function to add order by column.
     *
     * @param string $column To set the order by value.
     * @param string $text   To set the order by text.
     * @param string $type   To set the type of order.
     *
     * @return void
     */
    public function addOrderByColumn($column, $text, $type = 'asc'): void
    {
        if (empty($column) === false && empty($text) === false) {
            $this->OrderBy[$column] = [
                'value' => $column,
                'text' => $text,
                'type' => $type
            ];
        }
    }

    /**
     * Function to convert listing action to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->createListingAction();
    }

    /**
     * Function to create listing action
     *
     * @return string
     */
    public function createListingAction(): string
    {
        $result = '';
        # Create Pagination field.
        $paging = '';
        $pagingFieldId = '';
        if ($this->Pagination !== null) {
            $paging = $this->Pagination->createPaging();
            $pagingFieldId = $this->Pagination->getFieldId();
        }
        # Create order by field.
        $orderBy = $this->createOrderByField($pagingFieldId);
        if (empty($paging) === false || empty($orderBy) === false) {
            $result .= '<div class="row-fluid">';
            $result .= '<div class="span12 search-control">';
            $result .= $paging;
            $result .= $orderBy;
            $result .= '</div>';
            $result .= '</div>';
        }

        return $result;
    }


    /**
     * Function to create order by field.
     *
     * @param string $pagingFieldId To store the pagination field id.
     *
     * @return string
     */
    private function createOrderByField(string $pagingFieldId): string
    {
        $result = '';
        if ($this->EnableOrderBy === true && empty($this->OrderBy) === false) {
            $orderField = new Select('order_by', $this->SelectedOrderBy);
            $options = [];
            foreach ($this->OrderBy as $key => $value) {
                $options[] = $value;
            }
            $orderField->addOptions($options);
            $orderField->addAttribute('class', 'layout-option m-wrap');
            $orderField->addAttribute('title', 'Order by');
            $orderField->addAttribute('onchange', "Base.submitOrderBy('" . $pagingFieldId . "')");
            $result .= '<div class="order-by-field">';
            $result .= '<span>Order By &nbsp;</span>';
            $result .= $orderField;
            $result .= '</div>';
        }


        return $result;
    }

}
