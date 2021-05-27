<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   Spada
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2018 spada
 */

namespace App\Frame\Gui\Templates;

/**
 * Layout profile invoice
 *
 * @package    app
 * @subpackage Frame\Gui\Templates
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2018 Spada
 */
class ProfileInvoice extends AbstractTemplate
{
    /**
     * Function to create the view.
     *
     * @return string
     */
    protected function createTemplate(): string
    {
        $title = $this->getData('title');
        $items = $this->getData('items');
        $result = '<div class="x_panel" style="height: inherit;">';
        $result .= '<div class="x_title">';
        $result .= '<h2>' . $title . '</h2>';
        $result .= '<div class="clearfix"></div>';
        $result .= '</div>';
        # Content
        $result .= '<div class="x_content" style="overflow : auto; ' . $this->getHeightStyle() . '">';
        $result .= '<ul class="list-unstyled top_profiles scroll-view">';
        foreach ($items AS $row) {
            $result .= '<li class="media event" style="cursor:pointer" onclick="App.popup(\'' . $row['url'] . '\')">';
            $result .= '<div class="media-body">';
            $result .= '<p> ' . $row['description'] . ' </p>';
            $result .= '</div>';
            $result .= '</li>';
        }
        $result .= '</ul>';
        $result .= '</div>';
        # Content
        $result .= '</div>';

        return $result;
    }

    /**
     * Function to get content body of widget,load by ajax.
     *
     * @return string
     */
    public function getContentBody(): string
    {
        return '';
    }
}
