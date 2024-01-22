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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class DataColumn extends Column
{
    /** @var string */
    public $attribute;

    /** @var string|null */
    public $label;

    /** @var string|Closure|null */
    public $value;

    /**
     * Constructor.
     *
     * The default implementation initializes the object with the given configuration `$config`.
     *
     * If this method is overridden in a child class, it is recommended that the last parameter of the constructor
     * is a configuration array, like `$config` here.
     *
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->attribute = $config['attribute'] ?? null;
        $this->label = $config['label'] ?? null;
        $this->value = $config['value'] ?? null;

    }

    /**
     * Returns the data cell value.
     * @param mixed $model the data model
     * @param mixed $key the key associated with the data model
     * @param int $index the zero-based index of the data model among the models array returned by [[GridView::dataProvider]].
     * @return string the data cell value
     */
    public function getDataCellValue($model, $key, $index)
    {
        if ($this->value !== null) {
            if (is_string($this->value)) {
                return Arr::get((array) $model, $this->value);
            }
            return call_user_func($this->value, $model, $key, $index, $this);
        } elseif ($this->attribute !== null) {
            return Arr::get((array) $model, $this->attribute);
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function getHeaderCellLabel()
    {
        /** @var array|\Illuminate\Database\Eloquent\Collection */
        $provider = $this->grid->dataProvider;
        if ($this->label === null) {
            if ($this->attribute === null) {
                $label = '';
            } elseif ($provider instanceof Model) {
                if (method_exists(Str::class, "headline")) {
                    $label = Str::headline($this->attribute);
                } else {
                    $label = str_replace(['-', '_'], " ", $this->attribute);
                    $label = ucwords($label);
                }
            } elseif (is_array($provider)) {
                if (method_exists(Str::class, "headline")) {
                    $label = Str::headline($this->attribute);
                } else {
                    $label = str_replace(['-', '_'], " ", $this->attribute);
                    $label = ucwords($label);
                }
            } else {
                $models = $provider;
                if (($model = reset($models)) instanceof Model) {
                    /** @var Model $model */
                    if (method_exists(Str::class, "headline")) {
                        $label = Str::headline($this->attribute);
                    } else {
                        $label = str_replace(['-', '_'], " ", $this->attribute);
                        $label = ucwords($label);
                    }
                } else {
                    if (method_exists(Str::class, "headline")) {
                        $label = Str::headline($this->attribute);
                    } else {
                        $label = str_replace(['-', '_'], " ", $this->attribute);
                        $label = ucwords($label);
                    }
                }
            }
        } else {
            $label = $this->label;
        }
        return $label;
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content === null) {
            return $this->getDataCellValue($model, $key, $index);
        }
        return parent::renderDataCellContent($model, $key, $index);
    }
}