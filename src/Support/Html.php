<?php

/**
 * Copyright (C) 2024 Néstor Acevedo <clientes at neoacevedo.co>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace neoacevedo\gridview\Support;

use Illuminate\Support\Arr;

/**
 * Based on (Yii2 BaseHtml)[https://github.com/yiisoft/yii2/blob/master/framework/helpers/BaseHtml.php]
 */
class Html
{
    /**
     * Converts a CSS style array into a string representation.
     *
     * For example,
     *
     * ```php
     * print_r(Html::cssStyleFromArray(['width' => '100px', 'height' => '200px']));
     * // will display: 'width: 100px; height: 200px;'
     * ```
     *
     * @param array $style the CSS style array. The array keys are the CSS property names,
     * and the array values are the corresponding CSS property values.
     * @return string the CSS style string. If the CSS style is empty, a null will be returned.
     */
    public static function cssStyleFromArray(array $style)
    {
        $result = '';
        foreach ($style as $name => $value) {
            $result .= "$name: $value; ";
        }
        // return null if empty to avoid rendering the "style" attribute
        return $result === '' ? null : rtrim($result);
    }

    /**
     * Encodes special characters into HTML entities.
     * @param string $content the content to be encoded
     * @param bool $doubleEncode whether to encode HTML entities in `$content`. If false,
     * HTML entities in `$content` will not be further encoded.
     * @return string the encoded content
     * @see decode()
     * @see https://www.php.net/manual/en/function.htmlspecialchars.php
     */
    public static function encode($content, $doubleEncode = true)
    {
        return htmlspecialchars((string) $content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
    }

    /**
     * Encodes the given value into a JSON string HTML-escaping entities so it is safe to be embedded in HTML code.
     *
     * The method enhances `json_encode()` by supporting JavaScript expressions.
     * 
     * Note that data encoded as JSON must be UTF-8 encoded according to the JSON specification.
     * You must ensure strings passed to this method have proper encoding before passing them.
     *
     * @param mixed $value the data to be encoded
     * @return string the encoding result
     * @throws false if there is any encoding error
     */
    public static function htmlEncode($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }

    /**
     * Renders the HTML tag attributes.
     *
     * Attributes whose values are of boolean type will be treated as
     * [boolean attributes](https://html.spec.whatwg.org/multipage/common-microsyntaxes.html#boolean-attributes).
     *
     * Attributes whose values are null will not be rendered.
     *
     * The values of attributes will be HTML-encoded using [[encode()]].
     *
     * `aria` and `data` attributes get special handling when they are set to an array value. In these cases,
     * the array will be "expanded" and a list of ARIA/data attributes will be rendered. For example,
     * `'aria' => ['role' => 'checkbox', 'value' => 'true']` would be rendered as
     * `aria-role="checkbox" aria-value="true"`.
     *
     * If a nested `data` value is set to an array, it will be JSON-encoded. For example,
     * `'data' => ['params' => ['id' => 1, 'name' => 'yii']]` would be rendered as
     * `data-params='{"id":1,"name":"yii"}'`.
     *
     * @param array $attributes attributes to be rendered. The attribute values will be HTML-encoded using [[encode()]].
     * @return string the rendering result. If the attributes are not empty, they will be rendered
     * into a string with a leading white space (so that it can be directly appended to the tag name
     * in a tag). If there is no attribute, an empty string will be returned.
     * @see addCssClass()
     */
    public static function renderTagAttributes($attributes)
    {

        $html = '';
        foreach ($attributes as $name => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= " $name";
                }
            } elseif (is_array($value)) {
                if (in_array($name, static::$dataAttributes)) {
                    foreach ($value as $n => $v) {
                        if (is_array($v)) {
                            $html .= " $name-$n='" . static::htmlEncode($v) . "'";
                        } elseif (is_bool($v)) {
                            if ($v) {
                                $html .= " $name-$n";
                            }
                        } elseif ($v !== null) {
                            $html .= " $name-$n=\"" . static::encode($v) . '"';
                        }
                    }
                } elseif ($name === 'class') {
                    if (empty($value)) {
                        continue;
                    }
                    if (count($value) > 1) {
                        // removes duplicate classes
                        $value = explode(' ', implode(' ', $value));
                        $value = array_unique($value);
                    }
                    $html .= " $name=\"" . static::encode(implode(' ', array_filter($value))) . '"';
                } elseif ($name === 'style') {
                    if (empty($value)) {
                        continue;
                    }
                    $html .= " $name=\"" . static::encode(static::cssStyleFromArray($value)) . '"';
                } else {
                    $html .= " $name='" . static::htmlEncode($value) . "'";
                }
            } elseif ($value !== null) {
                $html .= " $name=\"" . static::encode($value) . '"';
            }
        }
        return $html;
    }

    /**
     * Renders the option tags that can be used by dropDownList() and listBox().
     * @param string|array|bool|null $selection the selected value(s). String/boolean for single or array for multiple
     * selection(s).
     * @param array $items the option data items. The array keys are option values, and the array values
     * are the corresponding option labels. The array can also be nested (i.e. some array values are arrays too).
     * For each sub-array, an option group will be generated whose label is the key associated with the sub-array.
     *
     * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in
     * the labels will also be HTML-encoded.
     * @param array $tagOptions the $options parameter that is passed to the [[dropDownList()]] or [[listBox()]] call.
     * This method will take out these elements, if any: "prompt", "options" and "groups". See more details
     * in [[dropDownList()]] for the explanation of these elements.
     *
     * @return string the generated list options
     */
    public static function renderSelectOptions($selection, array $items, array &$tagOptions = [])
    {
        $lines = [];
        $encodeSpaces = Arr::get($tagOptions, 'encodeSpaces', false);
        $encode = Arr::get($tagOptions, 'encode', true);
        $strict = Arr::get($tagOptions, 'strict', false);

        Arr::forget($tagOptions, ['encodeSpaces', 'encode', 'strict']);

        if (isset($tagOptions['prompt'])) {
            $promptOptions = ['value' => ''];
            if (is_string($tagOptions['prompt'])) {
                $promptText = $tagOptions['prompt'];
            } else {
                $promptText = $tagOptions['prompt']['text'];
                $promptOptions = array_merge($promptOptions, $tagOptions['prompt']['options']);
            }
            $promptText = $encode ? static::encode($promptText) : $promptText;
            if ($encodeSpaces) {
                $promptText = str_replace(' ', '&nbsp;', $promptText);
            }
            $promptOptions = static::renderTagAttributes($promptOptions);

            $lines[] = "<option $promptOptions>$promptText</option>";
        }
        $options = isset($tagOptions['options']) ? $tagOptions['options'] : [];
        $groups = isset($tagOptions['groups']) ? $tagOptions['groups'] : [];
        unset($tagOptions['prompt'], $tagOptions['options'], $tagOptions['groups']);
        $options['encodeSpaces'] = Arr::get($options, 'encodeSpaces', $encodeSpaces);
        $options['encode'] = Arr::get($options, 'encode', $encode);

        foreach ($items as $key => $value) {
            if (is_array($value)) {
                $groupAttrs = isset($groups[$key]) ? $groups[$key] : [];
                if (!isset($groupAttrs['label'])) {
                    $groupAttrs['label'] = $key;
                }
                $groupAttrs = static::renderTagAttributes($groupAttrs);
                $content = static::renderSelectOptions($selection, $value, $attrs);
                $lines[] = "<optgroup $groupAttrs>\n$content</optgroup>";
            } else {
                $attrs = isset($options[$key]) ? $options[$key] : [];
                $attrs['value'] = (string) $key;
                if (!array_key_exists('selected', $attrs)) {
                    $selected = false;
                    if ($selection !== null) {
                        if (Arr::accessible($selection)) {
                            $selected = Arr::has($selection, (string) $key);
                        } elseif ($key === '' || $selection === '') {
                            $selected = $selection === $key;
                        } elseif ($strict) {
                            $selected = !strcmp((string) $key, (string) $selection);
                        } else {
                            $selected = $selection == $key;
                        }
                    }
                    $attrs['selected'] = $selected;
                }
                $text = $encode ? static::encode($value) : $value;
                if ($encodeSpaces) {
                    $text = str_replace(' ', '&nbsp;', $text);
                }
                $attrs = static::renderTagAttributes($attrs);
                $lines[] = "<option $attrs>$text</option>";
            }
        }
        return implode("\n", $lines);
    }
}