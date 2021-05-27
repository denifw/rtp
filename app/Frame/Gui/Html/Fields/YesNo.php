<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 15/03/2017 C-Book
 */

namespace App\Frame\Gui\Html\Fields;

use App\Frame\Gui\Html\FieldsInterface;
use App\Frame\Gui\Html\Html;

/**
 * Class to manage creation of yes no field.
 *
 * @package    app
 * @subpackage Util\Gui\Html\Field
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  15/03/2017 C-Book
 */
class YesNo extends Html implements FieldsInterface
{
    /**
     * The yes radio object.
     *
     * @var \App\Frame\Gui\Html\Fields\Radio $Yes
     */
    private $Yes;

    /**
     * The no radio object.
     *
     * @var \App\Frame\Gui\Html\Fields\Radio $No
     */
    private $No;

    /**
     * Selected value.
     *
     * @var string Selected value.
     */
    private $Selected;

    /**
     * Selected value.
     *
     * @var string Selected value.
     */
    private $Id;

    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id The unique identifier of the field.
     * @param string $selected The selected radio button.
     */
    public function __construct($id, $selected)
    {
        $this->Id = $id;
        # Store the selected value
        $this->Selected = $selected;
        # Create radio yes selections
        $yes = new Radio($id, 'Y');
        if ($selected === 'Y') {
            $yes->addAttribute('checked', 'checked');
        }
        $this->Yes = $yes;
        # Create radio no selections
        $no = new Radio($id, 'N');
        if ($selected === 'N') {
            $no->addAttribute('checked', 'checked');
        }
        $this->No = $no;
        $this->addAttribute('id', $id);
    }

    /**
     * Return the yes no  as string.
     *
     * @return string
     */
    public function __toString()
    {
        # Combine elements
        $result = '<div style = "line-height: normal" class="form-check-input" id="' . $this->Id . '">';
        $result .= $this->Yes;
        $result .= '<label class="check-label" for="' . $this->Id . '_Y">' . trans('global.yes') . '</label>';
        $result .= $this->No;
        $result .= '<label class="check-label" for="' . $this->Id . '_N">' . trans('global.no') . '</label>';
        $result .= '</div>';

        return $result;
    }

    /**
     * Disable the radio system.
     *
     * @return void
     */
    public function setDisabled(): void
    {
        $this->Yes->addAttribute('disabled', 'disabled');
        $this->No->addAttribute('disabled', 'disabled');
    }

    /**
     * Set readonly for radio system.
     * @param bool $readOnly To store the trigger
     * @return void
     */
    public function setReadOnly(bool $readOnly = true): void
    {
        if ($readOnly === true) {
            if ($this->Selected === 'Y') {
                $this->No->addAttribute('disabled', 'disabled');
            } else {
                $this->Yes->addAttribute('disabled', 'disabled');
            }
        }
    }

}
