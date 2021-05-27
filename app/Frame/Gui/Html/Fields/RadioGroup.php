<?php
/**
 * Contains code written by the Spada Informatika Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   app
 * @author    Ano Surino<bong.anosurino@gmail.com>
 * @copyright 2019 spada-informatika.com
 */

namespace App\Frame\Gui\Html\Fields;

use App\Frame\Exceptions\Message;
use App\Frame\Gui\Html\FieldsInterface;
use App\Frame\Gui\Html\Html;

/**
 *
 *
 * @package    app
 * @subpackage Frame\Gui\Html\Fields
 * @author     Ano Surino<bong.anosurino@gmail.com>
 * @copyright  2019 Spada
 */
class RadioGroup extends Html implements FieldsInterface
{
    /**
     * Property to store all radio data.
     *
     * @var array $ListRadio
     */
    private $ListRadio = [];

    /**
     * Proerty to store the id.
     *
     * @var string $Id
     */
    private $Id;

    /**
     * Property to store radio checked value.
     *
     * @var string $CheckedValue
     */
    private $CheckedValue;

    public function __construct(string $id, $checkedValue)
    {
        $this->Id = $id;
        $this->CheckedValue = $checkedValue;
        $this->addAttribute('id', $id);
    }

    /**
     * Return the radio group  as string.
     *
     * @return string
     */
    public function __toString()
    {
        $result = '';
        if (empty($this->ListRadio) === false) {
            $result .= '<div style = "line-height: normal" class="form-check-input" id="' . $this->Id . '">';
            foreach ($this->ListRadio AS $value => $text) {
                $radio = new Radio($this->Id, $value);
                if ($this->CheckedValue === $value) {
                    $radio->addAttribute('checked', 'checked');
                }
                $result .= $radio;
                $result .= '<label class="check-label" for="' . $this->Id . '_' . $value . '">' . $text . '</label>';
            }
            $result .= '</div>';
        } else {
            Message::throwMessage('Empty list radio data.');
        }

        return $result;
    }

    /**
     * Function to add single radio.
     *
     * @param string $text
     * @param string $value
     */
    public function addRadio(string $text, string $value)
    {
        if (empty($value) === false && array_key_exists($value, $this->ListRadio) === false) {
            $this->ListRadio[$value] = $text;
        } else {
            Message::throwMessage('Duplicate value  ' . $value . '.');
        }
    }

    /**
     * Function ddd list of radio.
     *
     * @param array $data
     */
    public function addRadios(array $data)
    {
        foreach ($data AS $value => $text) {
            if (empty($value) === false && array_key_exists($value, $this->ListRadio) === false) {
                $this->ListRadio[$value] = $text;
            } else {
                Message::throwMessage('Duplicate value  ' . $value . '.');
            }
        }
    }

    /**
     * The checked radio.
     *
     * @param string $checkedValue
     *
     * @return void
     */
    public function setChecked(string $checkedValue): void
    {
        $this->CheckedValue = $checkedValue;
    }

}
