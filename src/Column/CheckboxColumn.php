<?php

/**
 * Copyright (C) 2022 Néstor Acevedo <clientes at neoacevedo.co>
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

namespace neoacevedo\gridview\Column;

use Closure;
use Illuminate\Support\HtmlString;

/**
 * CheckboxColumn muestra una columna de casillas de verificación en un GridView
 *
 * To add a CheckbosColumn to the [[GridView]] or to the [[GridViewComponent]], add it to the columns configuration as follows:
 * ```php
 * 'columns' => [
 *   // ...
 *   [
 *       'class' => 'neoacevedo\gridview\Column\SerialColumn',
 *       // you may configure additional properties here
 *   ],
 * ]
 * ```
 */
class CheckboxColumn extends Column
{
    /** @var string */
    public $name = 'selection';

    /** @var array|Closure */
    public $checkboxOptions = [];

    /** @var bool */
    public $multiple = true;

    /** @var string */
    public $cssClass;

    /**
     * Constructor
     */
    public function __construct($config = [])
    {
        $this->attribute = $config['attribute'] ?? null;
        $this->format = $config['format'] ?? null;
        $this->label = $config['label'] ?? null;
        $this->value = $config['value'] ?? null;
        $this->options = $config['options'] ?? [];
        $this->checkboxOptions = $config['checkboxOptions'] ?? [];
        $this->visible = $config['visible'] ?? true;
        $this->grid = $config['grid'] ?? null;
        $this->cssClass = $config['cssClass'] ?? null;
        $this->name = $config['name'] ?? "selection[]";
    }

    /**
     * Returns header checkbox name.
     * @return string
     */
    protected function getHeaderCheckBoxName()
    {
        $name = $this->name;
        if (substr_compare($name, '[]', -2, 2) === 0) {
            $name = substr($name, 0, -2);
        }
        if (substr_compare($name, ']', -1, 1) === 0) {
            $name = substr($name, 0, -1) . '_all]';
        } else {
            $name .= '_all';
        }
        return $name;
    }

    /**
     * Renders the data cell content.
     * @return HtmlString
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content !== null) {
            return parent::renderDataCellContent($model, $key, $index);
        }
        if ($this->checkboxOptions instanceof Closure) {
            $options = call_user_func($this->checkboxOptions, $model, $key, $index, $this);
        } else {
            $options = $this->checkboxOptions;
        }
        if (!isset($options['value'])) {
            $options['value'] = is_array($key) ? json_encode($key) : $key;
        }
        if ($this->cssClass !== null) {
            $options['class'] = $this->cssClass;
        }

        $options['name'] = $this->name;

        $options = implode(' ', array_map(
            function ($v, $k) {
                return sprintf("%s=\"%s\"", $k, $v);
            },
            $options,
            array_keys($options)
        ));

        return new HtmlString('<input type="checkbox" ' . $options . ' />');
    }

    /**
     * @inheritdoc
     */
    protected function renderHeaderCellContent()
    {
        if ($this->header !== null || !$this->multiple) {
            return parent::renderHeaderCellContent();
        }

        return new HtmlString('<input type="checkbox" name="' . $this->getHeaderCheckBoxName() . '" class="select-on-check-all" />');
    }
}
