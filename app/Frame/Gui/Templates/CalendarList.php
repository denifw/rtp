<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 2018 C-Book
 */

namespace App\Frame\Gui\Templates;

/**
 * Class to generate daily list system.
 *
 * @package    app
 * @subpackage Frame\Gui\Templates
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  2018 C-Book
 */
class CalendarList extends AbstractTemplate
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
        foreach ($items AS $row) {
            $result .= '<article class="media event">';
            $result .= '<a class="pull-left date ' . $row['tile_style'] . '" href="javascript:;" onclick = "App.popup(\'' . $row['url'] . '\')">';
            $result .= '<p class="month">' . $row['month'] . '</p>';
            $result .= '<p class="day">' . $row['day'] . '</p>';
            $result .= '</a>';
            $result .= '<div class="media-body">';
            $result .= '<a class="title" href="javascript:;" onclick="App.popup(\'' . $row['url'] . '\')">' . $row['title'] . '</a>';
            $result .= '<p>' . $row['description'] . '</p>';
            $result .= '</div>';
            $result .= '</article>';
        }
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
