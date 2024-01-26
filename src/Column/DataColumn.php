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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use neoacevedo\gridview\Support\Html;

class DataColumn extends Column
{
    /** @var string */
    public $attribute;

    /** @var string|null */
    public $label;

    /**
     * Whether the header lable should be HTML-encoded.
     * @var bool
     */
    public $encodeLabel = true;

    /** @var string|Closure|null */
    public $value;

    /**
     * @var string|array|Closure in which format should the value of each data model be displayed as (e.g. `"raw"`, `"text"`, `"html"`,
     * `date`, `datetime`). Supported formats are determined by the [[GridView::formatter|formatter]] used by
     * the [[GridView]]. Default format is "text" which will format the value as an HTML-encoded plain text.
     */
    public $format = 'text';

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

        if (isset($config['encodeLabel'])) {
            $this->encodeLabel = $config['encodeLabel'];
        }

        if (isset($config['format'])) {
            $this->format = $config['format'];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function renderHeaderCellContent()
    {
        if ($this->header !== null || $this->label === null && $this->attribute === null) {
            return parent::renderHeaderCellContent();
        }

        $label = $this->getHeaderCellLabel();
        if ($this->encodeLabel) {
            $label = Html::encode($label);
        }

        return $label;
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
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content === null) {
            $dataCellValue = $type = $format = '';
            if (is_string($this->format)) {
                $type = $this->format;
                $format = 'default';
            } elseif (is_array($this->format)) {
                $type = $this->format[0];
                $format = $this->format[1];
            }
            switch ($type) {
                case 'datetime':
                    $value = $this->getDataCellValue($model, $key, $index);
                    if ($format == 'default') {
                        if (is_numeric($value)) {
                            $dataCellValue = Carbon::createFromTimestamp($value)->toDateTimeString();
                        } else {
                            $dataCellValue = Carbon::createFromTimeString($value)->toDateTimeString();
                        }
                    } else {
                        if (is_numeric($value)) {
                            $dataCellValue = Carbon::createFromTimestamp($value)->toDateTime()->format($format);
                        } else {
                            $dataCellValue = Carbon::createFromFormat($format, $value)->format($format);
                        }
                    }
                    break;
                case 'date':
                    $value = $this->getDataCellValue($model, $key, $index);
                    if ($format == 'default') {
                        $dataCellValue = Carbon::createFromTimestamp($this->getDataCellValue($model, $key, $index))->toDateString();
                    } else {
                        $value = Carbon::createFromTimestamp($this->getDataCellValue($model, $key, $index))->toDate();
                        $dataCellValue = $value->format($format);
                    }
                    break;
                case 'email':
                    $value = $this->getDataCellValue($model, $key, $index);
                    $dataCellValue = new HtmlString("<a href=\"mailto:$value\">$value</a>");
                    break;
                default:
                    $dataCellValue = $this->getDataCellValue($model, $key, $index);
            }

            return $dataCellValue;
        }
        return parent::renderDataCellContent($model, $key, $index);
    }
}