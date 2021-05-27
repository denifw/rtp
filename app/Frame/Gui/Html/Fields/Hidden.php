<?php
/**
 * Created by PhpStorm.
 * User: nosurino
 * Date: 2/23/2017
 * Time: 9:47 PM
 */

namespace App\Frame\Gui\Html\Fields;


use App\Frame\Gui\Html\FieldsInterface;
use App\Frame\Gui\Html\Html;

class Hidden extends Html implements FieldsInterface
{
    /**
     * Constructor to load when there is a new object created.
     *
     * @param string $id    The id of the element.
     * @param string $value The value of the element.
     */
    public function __construct($id, $value)
    {
        $this->setTag('input');
        $this->addAttribute('type', 'hidden');
        $this->addAttribute('name', $id);
        $this->addAttribute('id', $id);
        $this->addAttribute('value', $value);
    }

}