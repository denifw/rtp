<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 29/08/2018 C-Book
 */

namespace App\Frame\Gui\Html\Buttons;

use App\Frame\Exceptions\Message;
use App\Frame\Gui\Html\ButtonInterface;
use App\Frame\Gui\Html\Html;

/**
 * Class to handle html Button
 *
 * @package    app
 * @subpackage Util\Gui\Buttons
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  29/08/2018 C-Book
 */
class Button extends Html implements ButtonInterface
{
    /**
     * Property to store size style
     *
     * @var string $DefaultClass
     */
    protected $DefaultClass = 'btn';
    /**
     * Property to store size style
     *
     * @var string $SizeStyle
     */
    protected $SizeStyle = 'btn-sm';
    /**
     * Property to store color style
     *
     * @var string $ColorStyle
     */
    private $ColorStyle = '';

    /**
     * Property to store position style
     *
     * @var string $PositionStyle
     */
    private $PositionStyle = '';

    /**
     * Property to store view style
     *
     * @var string $ViewStyle
     */
    private $ViewStyle = '';

    /**
     * Property to store icon button
     *
     * @var string $Icon
     */
    private $Icon = '';

    /**
     * Property to store trigger to disable class attribute.
     *
     * @var $bool $DisableClassAttribute
     */
    private $DisableClassAttribute = false;

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id The id of the element.
     * @param string|integer|float $value The value of the element.
     * @param string $type The type of the element.
     */
    public function __construct($id, $value, $type = 'button')
    {
        $this->setTag('button');
        $this->addAttribute('type', $type);
        $this->addAttribute('name', $id);
        $this->addAttribute('id', $id);
        $this->addAttribute('value', $value);
        $this->setContent($value);
    }
    /**
     * Magic function to create string value.
     *
     * @return String
     */
    public function __toString()
    {
        $content = $this->generateIcon() . $this->getContent();
        $this->setContent(trim($content));
        if ($this->DisableClassAttribute === false) {
            $class = [];
            if (empty($this->DefaultClass) === false) {
                $class[] = $this->DefaultClass;
            }
            if (empty($this->SizeStyle) === false) {
                $class[] = $this->SizeStyle;
            }
            if (empty($this->ColorStyle) === false) {
                $class[] = $this->ColorStyle;
            }
            if (empty($this->ViewStyle) === false) {
                $class[] = $this->ViewStyle;
                $this->setContent($this->generateIcon());
            }
            if (empty($this->PositionStyle) === false) {
                $class[] = $this->PositionStyle;
            }
            $this->addAttribute('class', implode(' ', $class));
        }
        return parent::__toString();
    }

    /**
     * Set popup when button click.
     *
     * @param string $routeName To store the route of the page.
     * @param array $params To store the parameter.
     *
     * @return void
     */
    public function setPopup(string $routeName, array $params = []): void
    {
        if (empty($routeName) === false) {
            $paramsUrl = [];
            if (empty($params) === false) {
                foreach ($params as $key => $value) {
                    $paramsUrl[] = $key . '=' . $value;
                }
            }
            $paramsUrl[] = 'pv=1';
            $url = $routeName . '?' . implode('&', $paramsUrl);
            $result = 'App.popup(\'' . url($url) . '\')';
            $this->addAttribute('onclick', $result);
        } else {
            Message::throwMessage('Invalid parameter to set action row click');
        }
    }

    /**
     * Function generate the icon of the button.
     *
     * @return string
     */
    private function generateIcon(): string
    {
        if (empty($this->Icon) === false) {
            return '<i class="' . $this->Icon . '"></i> ';
        }

        return '';
    }

    /**
     * Function to get the icon of the button.
     *
     * @return string
     */
    public function getIcon(): string
    {
        if (empty($this->Icon) === false) {
            return $this->Icon;
        }
        return '';
    }

    /**
     * Function to disable class attribute
     *
     * @param bool $disable To store the trigger.
     * @return void
     */
    public function disableClassAttribute(bool $disable = true): void
    {
        $this->DisableClassAttribute = $disable;
    }

    /**
     * Function to show button with icon only.
     *
     * @param bool $trigger To trigger if its only show icon or not.
     *
     * @return self
     */
    public function viewIconOnly(bool $trigger = true): self
    {
        $this->ViewStyle = '';
        if ($trigger === true) {
            $this->ViewStyle = 'btn-icon-only';
        }
        return $this;
    }

    /**
     * Function to set button as large size.
     *
     * @return self
     */
    public function btnLarge(): self
    {
        $this->SizeStyle = 'btn-lg';
        return $this;
    }

    /**
     * Function to set button as medium size.
     *
     * @return self
     */
    public function btnMedium(): self
    {
        $this->SizeStyle = 'btn-sm';
        return $this;
    }

    /**
     * Function to set button as small size.
     *
     * @return self
     */
    public function btnSmall(): self
    {
        $this->SizeStyle = 'btn-xs';
        return $this;
    }

    /**
     * Function to pull position button on right side.
     *
     * @return self
     */
    public function pullRight(): self
    {
        $this->PositionStyle = 'pull-right';
        return $this;
    }

    /**
     * Function to pull position button on center side.
     *
     * @return self
     */
    public function center(): self
    {
        $this->PositionStyle = 'center';
        return $this;
    }

    /**
     * Function to pull position button on left side.
     *
     * @return self
     */
    public function pullLeft(): self
    {
        $this->PositionStyle = 'pull-left';
        return $this;
    }

    /**
     * Function to set button color to danger.
     *
     * @return self
     */
    public function btnDanger(): self
    {
        $this->ColorStyle = 'btn-danger';
        return $this;
    }

    /**
     * Function to set button color to primary.
     *
     * @return self
     */
    public function btnPrimary(): self
    {
        $this->ColorStyle = 'btn-primary';
        return $this;
    }

    /**
     * Function to set button color to success.
     *
     * @return self
     */
    public function btnSuccess(): self
    {
        $this->ColorStyle = 'btn-success';
        return $this;
    }

    /**
     * Function to set button color to info.
     *
     * @return self
     */
    public function btnInfo(): self
    {
        $this->ColorStyle = 'btn-info';
        return $this;
    }

    /**
     * Function to set button color to dark.
     *
     * @return self
     */
    public function btnDark(): self
    {
        $this->ColorStyle = 'btn-dark';
        return $this;
    }

    /**
     * Function to set button color to warning.
     *
     * @return self
     */
    public function btnWarning(): self
    {
        $this->ColorStyle = 'btn-warning';
        return $this;
    }

    /**
     * Function to set button color to aqua.
     *
     * @return self
     */
    public function btnAqua(): self
    {
        $this->ColorStyle = 'btn-aqua';
        return $this;
    }

    /**
     * Function to set button color to aqua.
     *
     * @return self
     */
    public function btnDefault(): self
    {
        $this->ColorStyle = 'btn-default';
        return $this;
    }

    /**
     * Set the icon of the button.
     *
     * @param string $icon to store the icon name.
     *
     * @return self
     */
    public function setIcon($icon): self
    {
        $this->Icon = $icon;
        return $this;
    }

}
