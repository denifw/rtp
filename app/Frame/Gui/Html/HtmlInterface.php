<?php

namespace App\Frame\Gui\Html;

interface HtmlInterface
{

    /**
     * Method to create the complete html tag with attributes and content.
     *
     * @param string $tagName    The name of the tag you cant to create.
     * @param array  $attributes Array from all the attributes that the tag will use.
     * @param string $content    The content from the element.
     *
     * @return string
     */
    public function createElement(string $tagName = '', array $attributes = [], string $content = ''): string;

    /**
     * Set the tag name of the element.
     *
     * @param string $tagName The name of the element.
     *
     * @throws \Exception When incorrect tag.
     * @return Html
     */
    public function setTag(string $tagName);

    /**
     * Advanced array that direct can add multiple values to the attribute list for the html element.
     *
     * @param array $attributes Key value list from attributes.
     *
     * @return void
     */
    public function addAttributes(array $attributes): void;

    /**
     * Add content to the inner element. Will overwrite all the content that is already provided.
     *
     * @param string $content The content that needs to be placed inside the element.
     *
     * @return \App\Frame\Gui\Html\Html
     */
    public function setContent($content);

    /**
     * Add content to the inner element.
     *
     * @param string $content The content that needs to be placed inside the element.
     *
     * @return \App\Frame\Gui\Html\Html
     */
    public function addContent($content);

    /**
     * Returns the complete content as a string.
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Method to add key values to the array from all the attributes for one element.
     *
     * @param string $key   The attribute key.
     * @param string $value The value that the attribute has.
     *
     * @throws \Exception Id is already used.
     * @return \App\Frame\Gui\Html\Html
     */
    public function addAttribute(string $key, $value);

    /**
     * Remove a single attribute out of the element array.
     *
     * @param string $key The name of the key.
     *
     * @return self
     */
    public function removeAttribute(string $key);

    /**
     * Return the value of the requested attribute.
     *
     * @param string $attributeId The name of the attribute of the element to retrieve the value.
     *
     * @return string|boolean
     */
    public function getAttribute(string $attributeId);

    /**
     * Does the element has a specific attribute.
     *
     * @param string $key The name of the key.
     *
     * @return boolean
     */
    public function hasAttribute(string $key): bool;
}
