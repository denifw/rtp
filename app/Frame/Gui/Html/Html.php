<?php
/**
 * Contains code written by the C-Book Software.
 * Any other use of this code is in violation of copy rights.
 *
 * @package   C-Book
 * @author    Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright 29/08/2018 C-Book
 */

namespace App\Frame\Gui\Html;

use App\Frame\Exceptions\Message;

/**
 * Class to create the html.
 *
 * @package    app
 * @subpackage Util\Gui\Html
 * @author     Deni Firdaus Waruwu <deni.firdaus.w@gmail.com>
 * @copyright  29/08/2018 C-Book
 */
class Html implements HtmlInterface
{

    /**
     * Property with all possible html element that you can create.
     *
     * @var array $Elements
     */
    private static $Elements = [
        'a',
        'area',
        'b',
        'big',
        'blink',
        'body',
        'br',
        'button',
        'caption',
        'center',
        'cite',
        'code',
        'dd',
        'del',
        'dfn',
        'dir',
        'div',
        'dl',
        'dt',
        'em',
        'embed',
        'fieldset',
        'font',
        'form',
        'frame',
        'h3',
        'h4',
        'h5',
        'h6',
        'head',
        'hr',
        'html',
        'i',
        'img',
        'input',
        'ins',
        'isindex',
        'kbd',
        'label',
        'legend',
        'li',
        'link',
        'listing',
        'map',
        'marquee',
        'menu',
        'meta',
        'ol',
        'option',
        'p',
        'param',
        'plaintext',
        'pre',
        's',
        'script',
        'select',
        'small',
        'spacer',
        'span',
        'strike',
        'strong',
        'style',
        'sub',
        'sup',
        'table',
        'tbody',
        'td',
        'textarea',
        'tfoot',
        'th',
        'thead',
        'title',
        'tr',
        'tt',
        'u',
        'ul'
    ];
    /**
     * Property to store all attributes from an element.
     *
     * @var array $Attributes Default is empty array.
     */
    protected $Attributes = [];
    /**
     * Property to store the inner html from an element.
     *
     * @var array $Content String Default is null.
     */
    private $Content = [];
    /**
     * Property to store the element as string.
     *
     * @var string $Element Contains the complete element as string
     */
    private $Element = '';
    /**
     * Property to store the tag as string.
     *
     * @var string $TagName Contains the tag name of the element.
     */
    private $TagName;

    /**
     * List of all the unique ids that have been used. if double ids are added throw exception.
     *
     * @var array UniqueId List of unique ids on the form
     */
    private $UniqueId = [];

    /**
     * Returns the converted tag as a string if we call it with echo.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            if ($this->Element === '') {
                $this->createElement();
            }
            # store the element to string
            $element = $this->Element;
            # Empty the element
            $this->Element = '';

            # return the string variable;
            return $element;
        } catch (\Throwable $throwable) {
            # the __toString method isn't allowed to throw exceptions,so we turn them into an error instead
            trigger_error($throwable->getMessage() . "\n" . $throwable->getTraceAsString(), E_USER_ERROR);

            return '';
        }
    }

    /**
     * Method to create the complete html tag with attributes and content.
     *
     * @param string $tagName    The name of the tag you cant to create.
     * @param array  $attributes Array from all the attributes that the tag will use.
     * @param string $content    The content from the element.
     *
     * @return string
     */
    public function createElement(string $tagName = '', array $attributes = [], string $content = ''): string
    {
        # Pass the Tag name to the class
        if ($tagName !== '') {
            $this->setTag($tagName);
        }
        # Add the attributes to the element
        if (empty($attributes) === false) {
            $this->addAttributes($attributes);
        }
        # Set the content of the element
        if ($content !== '') {
            $this->addContent($content);
        }
        if (empty($this->Content) === false || $this->Content !== '' || $this->TagName === 'script') {
            $this->Element = '<' . $this->TagName . $this->getAttributes() . '>' . $this->getContent() . '</' . $this->TagName . '>' . "\n";
        } else {
            $this->Element = '<' . $this->TagName . $this->getAttributes() . ' />' . "\n";
        }

        return $this->Element;
    }

    /**
     * Set the tag name of the element.
     *
     * @param string $tagName The name of the element.
     *
     * @return self
     */
    public function setTag(string $tagName): self
    {
        # Convert to small case
        $tagName = mb_strtolower($tagName);
        # Check to see if tag name exists in the list of tags
        if (\is_string($tagName) === false || \in_array($tagName, self::$Elements, true) === false) {
            Message::throwMessage('Incorrect tag name:' . $tagName);
        } else {
            $this->TagName = $tagName;
        }

        return $this;
    }

    /**
     * Advanced array that direct can add multiple values to the attribute list for the html element.
     *
     * @param array $attributes Key value list from attributes.
     *
     * @return void
     */
    public function addAttributes(array $attributes): void
    {
        if (empty($attributes) === false) {
            $this->Attributes = array_merge($attributes, $this->Attributes);
        }
    }

    /**
     * Add content to the inner element.
     *
     * @param string $content The content that needs to be placed inside the element.
     *
     * @return self
     */
    public function addContent($content = ''): self
    {
        $this->Content[] = $content;

        return $this;
    }

    /**
     * Builder for the attributes, once the element is assembled this method will cover the provided attributes.
     *
     * @return string
     */
    private function getAttributes(): string
    {
        $attr = '';
        foreach ($this->Attributes as $key => $value) {
            $attr .= ' ' . $key . '="' . $value . '"';
        }

        return $attr;
    }

    /**
     * Returns the complete content as a string.
     *
     * @return string
     */
    public function getContent(): string
    {
        $content = '';
        if (empty($this->Content) === false && \is_array($this->Content) === true) {
            $content = implode($this->Content);
        }

        return $content;
    }

    /**
     * Add content to the inner element. Will overwrite all the content that is already provided.
     *
     * @param string $content The content that needs to be placed inside the element.
     *
     * @return self
     */
    public function setContent($content): self
    {
        $this->Content = [$content];

        return $this;
    }

    /**
     * Method to add key values to the array from all the attributes for one element.
     *
     * @param string               $key   The attribute key.
     * @param string|integer|float $value The value that the attribute has.
     *
     * @return void
     */
    public function addAttribute(string $key, $value): void
    {
        if ($key === 'class') {
            $oldAttributes = $this->getAttribute('class');
            $arrOld = explode(' ', $oldAttributes);
            $arrNew = explode(' ', $value);
            $attributes = array_merge($arrOld, $arrNew);
            $attributes = array_unique($attributes);
            $this->Attributes[$key] = implode(' ', $attributes);
        } else {
            if ($key === 'id') {
                if (\in_array($value, $this->UniqueId, true) === false) {
                    $this->UniqueId[] = $value;
                } else {
                    Message::throwMessage('Id is already used, is not following standard:' . $value);
                }
            }
            $this->Attributes[$key] = $value;
        }
    }

    /**
     * Remove a single attribute out of the element array.
     *
     * @param string $key The name of the key.
     *
     * @return self
     */
    public function removeAttribute(string $key): self
    {
        if (array_key_exists($key, $this->Attributes) === true) {
            unset($this->Attributes[$key]);
        }

        return $this;
    }

    /**
     * Remove a id out of the unique list
     *
     * @param string $elementIdName The name of the id to be removed.
     *
     * @return self
     */
    public function removeElementId(string $elementIdName): self
    {
        $key = array_search($elementIdName, $this->UniqueId, true);
        unset($this->UniqueId[$key]);

        return $this;
    }

    /**
     * Return the value of the requested attribute.
     *
     * @param string $attributeId The name of the attribute of the element to retrieve the value.
     *
     * @return string|boolean
     */
    public function getAttribute(string $attributeId)
    {
        $result = false;
        if ($this->hasAttribute($attributeId) === true) {
            $result = $this->Attributes[$attributeId];
        }

        return $result;
    }

    /**
     * Does the element has a specific attribute.
     *
     * @param string $key The name of the key.
     *
     * @return boolean
     */
    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->Attributes);
    }
}

