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
use Illuminate\Support\Number;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use neoacevedo\gridview\Support\Html;

class DataColumn extends Column
{
    /** @var string */
    public $attribute;

    /**
     * The HTML code representing a filter input (e.g. a text field, a dropdown list) that is used for this data column.
     * @var string|array|null|false
     */
    public $filter;

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
        /** @var \Illuminate\Pagination\LengthAwarePaginator */
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
                $models = $provider instanceof Collection ? $provider->toArray() : $provider->items();
                if (($model = reset($models)) instanceof Model) {
                    /** @var Model $model */
                    if (method_exists(Str::class, "headline")) {
                        $label = $this->attribute;
                        $label = Str::headline($label);
                    } else {
                        $label = $this->attribute;
                        $label = str_replace(['-', '_'], " ", $label);
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
            return is_array($model) ? Arr::get($model, $this->attribute) : $model->getAttribute($this->attribute);
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content === null) {
            $dataCellValue = $type = '';
            if (is_string($this->format)) {
                $type = $this->format;
                $format = 'default';
            } elseif (is_array($this->format)) {
                $type = $this->format[0];
                $format = $this->format[1];
            }
            switch ($type) {
                case 'datetime':
                    /**
                     * @var Carbon|int
                     */
                    $value = $this->getDataCellValue($model, $key, $index);
                    if ($value instanceof Carbon) {
                        $dataCellValue = $value->toDateTimeString();
                    } else {
                        if ($format == 'default') {
                            $dataCellValue = Carbon::createFromTimestamp($value, config('app.timezone'))->toDateTimeString();
                        } else {
                            $value = Carbon::createFromTimestamp($value)->toDate();
                            $dataCellValue = $value->format($format);
                        }
                    }
                    break;
                case 'date':
                    /**
                     * @var Carbon|int
                     */
                    $value = $this->getDataCellValue($model, $key, $index);
                    if ($value instanceof Carbon) {
                        $dataCellValue = $value->toDateString();
                    } else {
                        if ($format == 'default') {
                            $value = Carbon::createFromTimestamp($value, config('app.timezone'))->toDate();
                            $dataCellValue = $value->format('d M y');
                        } else {
                            $value = Carbon::createFromTimestamp($value)->toDate();
                            $dataCellValue = $value->format($format);
                        }
                    }
                    break;
                case 'email':
                    $value = $this->getDataCellValue($model, $key, $index);
                    $dataCellValue = Str::of("<a href=\"mailto:$value\">$value</a>")->toHtmlString();
                    break;
                case 'currency':
                    $value = $this->getDataCellValue($model, $key, $index);
                    if (substr(app()->version(), 0, 1) < 10) {
                        $formatter = new \NumberFormatter(config('app.locale'), \NumberFormatter::CURRENCY);

                        $dataCellValue = $formatter->formatCurrency($value, config('app.currency_code', 'USD'));
                    } else {
                        $dataCellValue = Number::curency($value, config('app.locale'));
                    }
                    break;
                case 'html':
                    $value = $this->getDataCellValue($model, $key, $index);
                    $dataCellValue = Str::of($value)->toHtmlString();
                    break;
                case 'raw':
                default:
                    $dataCellValue = $this->getDataCellValue($model, $key, $index);
            }

            return $dataCellValue;
        }
        return parent::renderDataCellContent($model, $key, $index);
    }

    /**
     * @inheritDoc
     */
    protected function renderFilterCellContent()
    {
        if (is_string($this->filter)) {
            return $this->filter;
        }


    }
}