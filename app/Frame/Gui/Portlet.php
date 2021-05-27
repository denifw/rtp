<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 12/03/2017 C-Book
 */

namespace App\Frame\Gui;

use App\Frame\Exceptions\Message;
use App\Frame\Gui\Html\ButtonInterface;
use App\Frame\System\Pagination;

/**
 * Class to generate the portlet.
 *
 * @package    app
 * @subpackage Util\Gui
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  12/03/2017 C-Book
 */
class Portlet
{
    /**
     * Attribute title of the portlet
     *
     * @var string $Title
     */
    public $Title = '';

    /**
     * Attribute to store the body of the portlet.
     *
     * @var array $Body
     */
    public $Body = [];

    /**
     * Attribute to store icon of the portlet
     *
     * @var string $Icon
     */
    private $Icon = '';

    /**
     * Attribute to store the header attribute of the portlet.
     *
     * @var array $HeaderAttributes
     */
    private $HeaderAttributes = [];

    /**
     * Attribute to store the body attribute of the portlet.
     *
     * @var array $BodyAttributes
     */
    private $BodyAttributes = [];

    /**
     * Attribute to store the portlet attribute of the portlet.
     *
     * @var array $PortletAttributes
     */
    private $PortletAttributes = [];

    /**
     * Attribute to store button action for portlet.
     *
     * @var array $Buttons
     */
    private $Buttons = [];

    /**
     * Attribute to store button id action for portlet.
     *
     * @var array $ButtonIds
     */
    private $ButtonIds = [];

    /**
     * Attribute to store the body of the portlet.
     *
     * @var string $ColumnGridClass
     */
    private $ColumnGridClass = 'col-xs-12';

    /**
     * Attribute to store the pagination
     *
     * @var \App\Frame\System\Pagination $Pagination
     */
    private $Pagination;

    /**
     * Attribute to store chart title container
     *
     * @var string $ChartTitleContainer
     */
    private $ChartTitleContainer = '';

    /**
     * Constructor for the portlet  class.
     *
     * @param string $portletId    To store the portlet id.
     * @param string $portletTitle To store the title of the table.
     */
    public function __construct(string $portletId, string $portletTitle = '')
    {
        if (empty($portletId) === false) {
            $this->addPortletAttribute('id', $portletId);
        } else {
            Message::throwMessage('Invalid id for portlet.');
        }
        $this->addPortletAttribute('class', 'x_panel');
        if (empty($portletTitle) === false) {
            $this->Title = $portletTitle;
        }
        $this->addBodyAttribute('id', $portletId . '_content');
        $this->addBodyAttribute('class', 'x_content');

    }

    /**
     * Function to add the portlet attribute.
     *
     * @param string $attributeName  To set the attribute name.
     * @param string $attributeValue To set the attribute value.
     *
     * @return void
     */
    public function addPortletAttribute(string $attributeName, string $attributeValue): void
    {
        $this->PortletAttributes[$attributeName] = $attributeValue;
    }

    /**
     * Function to add the portlet attribute.
     *
     * @param integer $large      To set the grid amount for a large screen.
     * @param integer $medium     To set the grid amount for a medium screen.
     * @param integer $small      To set the grid amount for a small screen.
     * @param integer $extraSmall To set the grid amount for a extra small screen.
     *
     * @return void
     */
    public function setGridDimension(int $large = 3, int $medium = 4, int $small = 6, $extraSmall = 12): void
    {
        $this->ColumnGridClass = 'col-lg-' . $large . ' col-md-' . $medium . ' col-sm-' . $small . ' col-xs-' . $extraSmall;
    }

    /**
     * Function to set title
     *
     * @param string $title To set title portlet.
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        if (empty($title) === false) {
            $this->Title = $title;
        }
    }

    /**
     * Function to set pagination
     *
     * @param \App\Frame\System\Pagination $pagination To set pagination object.
     *
     * @return void
     */
    public function setPagination(Pagination $pagination): void
    {
        $this->Pagination = $pagination;
    }

    /**
     * Function to add the header attribute.
     *
     * @param string $attributeName  To set the attribute name.
     * @param string $attributeValue To set the attribute value.
     *
     * @return void
     */
    public function addHeaderAttribute(string $attributeName, string $attributeValue): void
    {
        $this->HeaderAttributes[$attributeName] = $attributeValue;
    }

    /**
     * Function to add the body attribute.
     *
     * @param string $attributeName  To set the attribute name.
     * @param string $attributeValue To set the attribute value.
     *
     * @return void
     */
    public function addBodyAttribute(string $attributeName, string $attributeValue): void
    {
        $this->BodyAttributes[$attributeName] = $attributeValue;
    }

    /**
     * Function to set the icon.
     *
     * @param string $icon To set the icon name.
     *
     * @return void
     */
    public function setIcon($icon): void
    {
        if (empty($icon) === false) {
            $this->Icon = '<i class="' . $icon . '"></i>';
        }
    }

    /**
     * Function to add the table to the body.
     *
     * @param \App\Frame\Gui\Table $table    To set the table data.
     * @param integer              $position To set the data position.
     *
     * @return void
     */
    public function addTable(Table $table, $position = 0): void
    {
        if ($table !== null) {
            if (empty($position) === false && is_numeric($position) === true) {
                if (array_key_exists($position, $this->Body) === true) {
                    $newBody = [];
                    foreach ($this->Body as $key => $data) {
                        if ($position === $key) {
                            $newBody[] = $table;
                            $newBody[] = $data;
                        } else {
                            $newBody[] = $data;
                        }
                    }
                    $this->Body = $newBody;
                } else {
                    $this->Body[$position] = $table;
                }
            } else {
                $this->Body[] = $table;
            }
        }
    }

    /**
     * Function to add field set to the form
     *
     * @param \App\Frame\Gui\FieldSet $fieldSet To set the field set data data.
     * @param integer                 $position To set the data position.
     *
     * @return void
     */
    public function addFieldSet(FieldSet $fieldSet, $position = 0): void
    {
        if ($fieldSet !== null) {
            if (empty($position) === false && is_numeric($position) === true) {
                if (array_key_exists($position, $this->Body) === true) {
                    $newBody = [];
                    foreach ($this->Body as $key => $data) {
                        if ($position === $key) {
                            $newBody[] = $fieldSet;
                            $newBody[] = $data;
                        } else {
                            $newBody[] = $data;
                        }
                    }
                    $this->Body = $newBody;
                } else {
                    $this->Body[$position] = $fieldSet;
                }
            } else {
                $this->Body[] = $fieldSet;
            }
        }
    }


    /**
     * Function to add the string to the body.
     *
     * @param string  $text     To set the table data.
     * @param integer $position To set the data position.
     *
     * @return void
     */
    public function addText(string $text, $position = 0): void
    {
        if (empty($text) === false) {
            if (empty($position) === false && is_numeric($position) === true) {
                if (array_key_exists($position, $this->Body) === true) {
                    $newBody = [];
                    foreach ($this->Body as $key => $data) {
                        if ($position === $key) {
                            $newBody[] = $text;
                            $newBody[] = $data;
                        } else {
                            $newBody[] = $data;
                        }
                    }
                    $this->Body = $newBody;
                } else {
                    $this->Body[$position] = $text;
                }
            } else {
                $this->Body[] = $text;
            }
        }
    }

    /**
     * Function to add chart container into the portlet.
     *
     * @param string  $chartId  To set the id of the chart.
     * @param integer $position To set the data position.
     *
     * @return void
     */
    public function addChartContainer(string $chartId, $position = 0): void
    {
        if (empty($chartId) === false) {
            $content = '<div id="' . $chartId . '">';
            $content .= '</div>';
            if (empty($position) === false && is_numeric($position) === true) {
                if (array_key_exists($position, $this->Body) === true) {
                    $newBody = [];
                    foreach ($this->Body as $key => $data) {
                        if ($position === $key) {
                            $newBody[] = $content;
                            $newBody[] = $data;
                        } else {
                            $newBody[] = $data;
                        }
                    }
                    $this->Body = $newBody;
                } else {
                    $this->Body[$position] = $content;
                }
            } else {
                $this->Body[] = $content;
            }
        }
    }

    /**
     * Function to add chart title container into the portlet.
     *
     * @param string $chartId To set the id of the chart.
     *
     * @return void
     */
    public function setChartTitleContainer(string $chartId): void
    {
        if (empty($chartId) === false) {
            $this->ChartTitleContainer = '<div class="x_title">';
            $this->ChartTitleContainer .= '<p id = "' . $chartId . 'title" class="title pull-left">' . $this->Icon . ' ' . $this->Title . '</p>';
            if ($this->Pagination !== null) {
                $this->ChartTitleContainer .= $this->Pagination->createPaging();
            }
            $this->ChartTitleContainer .= $this->getPortletAction();
            $this->ChartTitleContainer .= '<div class="clearfix"></div>';
            $this->ChartTitleContainer .= '</div>';
        }
    }

    /**
     * Function to get chart title container.
     *
     * @return string
     */
    public function getChartTitleContainer(): string
    {
        return $this->ChartTitleContainer;
    }

    /**
     * Function to convert the portlet data to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->createPortlet();
    }

    /**
     * Function to create the portlet.
     *
     * @return string
     */
    public function createPortlet(): string
    {
        if (empty($this->Title) === false) {
            $this->addHeaderAttribute('class', 'x_title');
        }
        $result = '<div id="' . $this->getPortletId() . 'grid" class="' . $this->ColumnGridClass . '">';
        $result .= '<div ' . $this->getPortletAttribute() . '>';
        $result .= $this->getPortletHeader();
        $result .= $this->getPortletBody();
        $result .= '</div>';
        $result .= '</div>';

        return $result;
    }

    /**
     * Function to get the id of the portlet.
     *
     * @return string
     */
    public function getPortletId(): string
    {
        return $this->PortletAttributes['id'];
    }

    /**
     * Function to get the id of the portlet body.
     *
     * @return string
     */
    public function getPortletBodyId(): string
    {
        return $this->BodyAttributes['id'];
    }

    /**
     * Function to add button to the portlet.
     *
     * @param \App\Frame\Gui\Html\ButtonInterface $button To set the button field.
     *
     * @return void
     */
    public function addButton($button): void
    {
        if ($button instanceof ButtonInterface) {
            $buttonId = $button->getAttribute('id');
            if (array_key_exists($buttonId, $this->Buttons) === false) {
                $this->Buttons[$buttonId] = $button;
                $this->ButtonIds[] = $buttonId;
            } else {
                Message::throwMessage('Button with id ' . $buttonId . ' already added to the portlet.');
            }
        } else {
            Message::throwMessage('Button must be instance of Button Interface');
        }
    }

    /**
     * Function to add button after existing button to the portlet.
     *
     * @param \App\Frame\Gui\Html\ButtonInterface $button           To set the button field.
     * @param string                              $existingButtonId To set the existing id of the field.
     *
     * @return void
     */
    public function addButtonAfter($button, $existingButtonId): void
    {
        if (array_key_exists($existingButtonId, $this->Buttons) === false) {
            Message::throwMessage('Not found button with id ' . $existingButtonId . ' in the portlet');
        }
        if ($button instanceof ButtonInterface) {
            $buttonId = $button->getAttribute('id');
            if (array_key_exists($buttonId, $this->Buttons) === false) {
                $newButtons = [];
                $this->ButtonIds = [];
                foreach ($this->Buttons as $key => $oldButton) {
                    $newButtons[$key] = $oldButton;
                    $this->ButtonIds[] = $key;
                    if ($key === $existingButtonId) {
                        $newButtons[$buttonId] = $button;
                        $this->ButtonIds[] = $buttonId;
                    }
                }
                $this->Buttons = $newButtons;
            } else {
                Message::throwMessage('Duplicate Field with id ' . $buttonId . ' inside the portlet.');
            }
        } else {
            Message::throwMessage('Button must be instance of Button Interface');
        }
    }

    /**
     * Function to add button at the beginning.
     *
     * @param \App\Frame\Gui\Html\ButtonInterface $button To set the value of the attribute.
     *
     * @return void
     */
    public function addButtonAtTheBeginning($button): void
    {
        if ($button instanceof ButtonInterface) {
            $buttonId = $button->getAttribute('id');
            if (array_key_exists($buttonId, $this->Buttons) === false) {
                $newButtons = [];
                $this->ButtonIds = [];
                $newButtons[$buttonId] = $button;
                $this->ButtonIds[] = $buttonId;
                foreach ($this->Buttons as $key => $oldButton) {
                    $newButtons[$key] = $oldButton;
                    $this->ButtonIds[] = $key;
                }
                $this->Buttons = $newButtons;
            } else {
                Message::throwMessage('Duplicate button with id ' . $buttonId . ' inside the portlet.');
            }
        } else {
            Message::throwMessage('Button must be instance of Button Interface');
        }
    }

    /**
     * Function to remove button from the form.
     *
     * @param string $buttonId To set the id of the field.
     *
     * @return void
     */
    public function removeButton($buttonId): void
    {
        if (array_key_exists($buttonId, $this->Buttons) === true) {
            unset($this->Buttons[$buttonId]);
            $key = array_search($buttonId, $this->ButtonIds, true);
            unset($this->ButtonIds[$key]);
            # Reset the field ids to the correct values
            $this->ButtonIds = array_values($this->ButtonIds);
        } else {
            Message::throwMessage('Not found button with id ' . $buttonId . ' in the portlet.');
        }
    }

    /**
     * Function to get portlet attribute.
     *
     * @return string
     */
    private function getPortletAttribute(): string
    {
        $result = ' ';
        if (empty($this->PortletAttributes) === false) {
            foreach ($this->PortletAttributes as $key => $value) {
                $result .= $key . '="' . $value . '"';
            }
        }

        return $result;
    }

    /**
     * Function to get portlet header.
     *
     * @return string
     */
    private function getPortletHeader(): string
    {
        $result = '';
        if (empty($this->Title) === false) {
            $result = '<div ' . $this->getHeaderAttribute() . '>';
            $result .= '<p class="title pull-left">' . $this->Icon . ' ' . $this->Title . '</p>';
            if ($this->Pagination !== null) {
                $result .= $this->Pagination->createPaging();
            }
            $result .= $this->getPortletAction();
            $result .= '<div class="clearfix"></div>';
            $result .= '</div>';
        } elseif (empty($this->ChartTitleContainer) === false) {
            $result = $this->getChartTitleContainer();
        }

        return $result;
    }

    /**
     * Function to get header attribute.
     *
     * @return string
     */
    private function getHeaderAttribute(): string
    {
        $result = ' ';
        if (empty($this->HeaderAttributes) === false) {
            foreach ($this->HeaderAttributes as $key => $value) {
                $result .= $key . '="' . $value . '"';
            }
        }

        return $result;
    }

    /**
     * Function to get portlet body.
     *
     * @return string
     */
    private function getPortletBody(): string
    {
        $result = ' ';
        if (empty($this->Body) === false) {
            $result .= '<div ' . $this->getBodyAttribute() . '>';
            foreach ($this->Body as $key => $body) {
                if ($body instanceof FieldSet) {
                    $result .= $body->createFieldSet();
                } else {
                    $result .= $body;
                }
            }
            $result .= '</div>';

        }

        return $result;
    }

    /**
     * Function to get portlet action.
     *
     * @return string
     */
    private function getPortletAction(): string
    {
        $result = ' ';
        if (empty($this->ButtonIds) === false) {
            foreach ($this->ButtonIds as $buttonId) {
                if (array_key_exists($buttonId, $this->Buttons) === true) {
                    $result .= $this->Buttons[$buttonId];
                }
            }
        }

        return $result;
    }

    /**
     * Function to get body attribute.
     *
     * @return string
     */
    private function getBodyAttribute(): string
    {
        $result = ' ';
        if (empty($this->BodyAttributes) === false) {
            foreach ($this->BodyAttributes as $key => $value) {
                $result .= $key . '="' . $value . '"';
            }
        }

        return $result;
    }
}
