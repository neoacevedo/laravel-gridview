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

namespace neoacevedo\gridview;

use Closure;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use neoacevedo\gridview\Column\DataColumn;
use Illuminate\Support\Str;

/**
 * The GridView object is used to display data in a grid.
 *
 * A basic usage looks like the following:
 * ```php
 * <?= GridView::widget([
 *   'dataProvider' => $dataProvider,
 *   'columns' => [
 *       'id',
 *       'name',
 *       'created_at:datetime',
 *       // ...
 *   ],
 * ]) ?>
 * ```
 */
class GridView
{
    /**
     * @var string the caption of the grid table
     * @see captionOptions
     */
    public $caption;

    /**
     * @var array the HTML attributes for the caption element.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     * @see caption
     */
    public $captionOptions = [];

    /**
     * @var array grid column configuration. Each array element represents the configuration
     * for one particular grid column. For example,
     *
     * ```php
     * [
     *     ['class' => SerialColumn::class],
     *     [
     *         'class' => DataColumn::class, // this line is optional
     *         'attribute' => 'name',
     *         'format' => 'text',
     *         'label' => 'Name',
     *     ],
     *     ['class' => CheckboxColumn::class],
     * ]
     * ```
     *
     * If a column is of class [[DataColumn]], the "class" element can be omitted.
     *
     * As a shortcut format, a string may be used to specify the configuration of a data column
     * which only contains [[DataColumn::attribute|attribute]] and/or [[DataColumn::label|label]]
     * options: `"attribute:label"`.
     * For example, the above "name" column can also be specified as: `"name:Name"`.
     * "label" is optional. It will take default value if absent.
     *
     * Using the shortcut format the configuration for columns in simple cases would look like this:
     *
     * ```php
     * [
     *     'id',
     *     'amount:Total Amount',
     *     'created_at:Created at',
     * ]
     * ```
     *
     * When using a [[dataProvider]] with nested records, you can also display values from related records,
     * e.g. the `name` attribute of the `author` relation:
     *
     * ```php
     * // shortcut syntax
     * 'author.name',
     * // full syntax
     * [
     *     'attribute' => 'author.name',
     *     // ...
     * ]
     * ```
     */
    public $columns = [];

    /**
     * The data provider for the view.
     * @var array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public $dataProvider = [];

    /**
     * The default data column class if the class name is not explicitly specified when configuring a data column.
     * @var string
     */
    public $dataColumnClass = null;

    /**
     * The HTML display when the content of a cell is empty.
     * @var string
     */
    public string $emptyCell = '&nbsp;';

    /**
     * The HTML content to be displayed when [[dataProvider]] does not have any data.
     * @var string|false
     */
    public $emptyText;

    /**
     * The HTML attributes for the emptyText of the list.
     * @var array
     */
    public array $emptyTextOptions = [
        'class' => 'empty',
    ];

    /**
     * The HTML attributes for the table header row.
     * @var array
     */
    public array $headerRowOptions = [];

    /**
     * The formatter used to format model attribute values into displayable texts.
     * @var array|null
     */
    public $formatter;

    /** @var array|Closure */
    public $rowOptions = [];

    /** @var array */
    public $tableOptions = ['class' => 'table table-striped table-bordered'];

    /**
     * Constructor
     */
    public function __construct()
    {
        if ($this->emptyText === null) {
            $this->emptyText = 'No results found.';
        }
    }

    /**
     * Get the view / contents that represent the [[GridView]].
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function widget($config = [])
    {
        $this->dataProvider = $config['dataProvider'];

        $this->columns = $config['columns'];

        if (isset($config['tableOptions'])) {
            $this->tableOptions = $config['tableOptions'];
        }

        $tableOptions = implode(' ', array_map(
            function ($v, $k) {
                return sprintf("%s=\"%s\"", $k, $v);
            },
            $this->tableOptions,
            array_keys($this->tableOptions)
        ));

        $this->initColumns();

        $columnGroup = $this->renderColumnGroup();
        $tableHeader = $this->renderTableHeader();
        $tableBody = $this->renderTableBody();

        return view("gridview::gridview", [
            'tableOptions' => $tableOptions,
            'tableHeader' => $tableHeader,
            'columnGroup' => $columnGroup,
            'tableBody' => $tableBody
        ]);
    }

    /**
     * Returns the data models.
     * @return array|\Illuminate\Database\Eloquent\Collection
     */
    public function getModels()
    {
        if (is_array($this->dataProvider)) {
            return $this->dataProvider;
        } elseif ($this->dataProvider instanceof Collection) {
            return $this->dataProvider->toArray();
        } else {
            return $this->dataProvider->getCollection()->toArray();
        }
    }

    /**
     * Renders the HTML content indicating that the list view has no data.
     * @return HtmlString
     */
    public function renderEmpty()
    {
        if ($this->emptyText === false) {
            return '';
        }
        $options = implode(' ', array_map(
            function ($v, $k) {
                return sprintf("%s=\"%s\"", $k, $v);
            },
            $this->emptyTextOptions,
            array_keys($this->emptyTextOptions)
        ));

        $tag = Arr::forget($this->emptyTextOptions, 'tag');
        if (!$tag) {
            $tag = "div";
        }

        return new HtmlString("<$tag $options>{$this->emptyText}</$tag>");
    }

    /**
     * Renders the column group HTML.
     * @return HtmlString|false
     */
    public function renderColumnGroup()
    {
        foreach ($this->columns as $column) {
            /* @var $column Column */
            if (!empty($column->options)) {
                $cols = [];
                foreach ($this->columns as $col) {
                    $options = implode(' ', array_map(
                        function ($v, $k) {
                            return sprintf("%s=\"%s\"", $k, $v);
                        },
                        $col->options,
                        array_keys($col->options)
                    ));
                    $cols[] = "<col $options></col>";
                }
                return new HtmlString("<colgroup>" . implode("\n", $cols) . "</colgroup>");
            }
        }
        return false;
    }

    /**
     * Renders the table header.
     * @return HtmlString
     */
    public function renderTableHeader()
    {
        $cells = [];
        /** @var \neoacevedo\gridview\Column\Column $column */
        foreach ($this->columns as $column) {
            $cells[] = $column->renderHeaderCell();
        }

        $options = implode(' ', array_map(
            function ($v, $k) {
                return sprintf("%s=\"%s\"", $k, $v);
            },
            $this->headerRowOptions,
            array_keys($this->headerRowOptions)
        ));

        $content = "<tr $options>" . implode('', $cells) . "</tr>";

        return new HtmlString("<thead>\n" . $content . "\n</thead>");
    }

    /**
     * Renders the table body.
     * @return string
     */
    public function renderTableBody()
    {
        $models = array_values($this->getModels());
        $keys = is_array($this->dataProvider) ? array_keys($this->dataProvider) : $this->dataProvider->keys();
        $rows = [];

        foreach ($models as $index => $model) {
            $key = $keys[$index];

            $rows[] = $this->renderTableRow($model, $key, $index);
        }
        if (empty($rows) && $this->emptyText !== false) {
            $colspan = count($this->columns);
            return "<tbody>\n<tr><td colspan=\"$colspan\">" . $this->renderEmpty() . "</td></tr>\n</tbody>";
        }
        return "<tbody>\n" . implode("\n", $rows) . "\n</tbody>";
    }

    /**
     * Renders the table row.
     * @return HtmlString
     */
    public function renderTableRow($model, $key, $index)
    {
        $cells = [];

        /** @var DataColumn $column */
        foreach ($this->columns as $column) {
            $cells[] = $column->renderDataCell($model, $key, $index);
        }

        if ($this->rowOptions instanceof Closure) {
            $options = call_user_func($this->rowOptions, $model, $key, $index, $this);
        } else {
            $options = $this->rowOptions;
        }

        $options['data-key'] = is_array($key) ? json_encode($key) : (string) $key;

        $trOptions = implode(' ', array_map(
            function ($v, $k) {
                return sprintf("%s=\"%s\"", $k, $v);
            },
            $options,
            array_keys($options)
        ));

        return new HtmlString("<tr $trOptions>" . implode("", $cells) . '</tr>');
    }

    /**
     * Creates a [[DataColumn]] object based on a string in the format of "attribute" or "attribute:label".
     * @param string $text the column specification string
     * @return DataColumn the column instance
     * @throws Exception if the column specification is invalid
     */
    protected function createDataColumn($text)
    {
        if (!preg_match('/^([^:]+)(:(.*))?$/', $text, $matches)) {
            throw new Exception('The column must be specified in the format of "attribute" or "attribute:label"');
        }

        return new DataColumn([
            'attribute' => $matches[1],
            'label' => isset($matches[3]) ? $matches[3] : null,
            'grid' => $this
        ]);
    }

    /**
     * This function tries to guess the columns to show from the given data
     * if [[columns]] are not explicitly specified.
     */
    protected function guessColumns()
    {
        $models = $this->getModels();
        $model = reset($models);

        if (is_array($model) || is_object($model)) {
            foreach ($model as $name => $value) {
                if ($value === null || is_scalar($value) || is_callable([$value, '__toString'])) {
                    $this->columns[] = (string) $name;
                }
            }
        }
    }

    /**
     * Creates column objects and initializes them.
     * @return void
     */
    protected function initColumns()
    {
        if (empty($this->columns)) {
            $this->guessColumns();
        }
        foreach ($this->columns as $i => $column) {
            if (is_string($column)) {
                $column = $this->createDataColumn($column);
            } else {
                if (isset($column['class'])) {
                    $column = new $column['class'](array_merge($column, ['grid' => $this]));
                } else {
                    $column = $this->dataColumnClass ? new $this->dataColumnClass(array_merge($column, ['grid' => $this]))
                        : new DataColumn(array_merge($column, ['grid' => $this]));
                }
            }
            if (!$column->visible) {
                unset($this->columns[$i]);
                continue;
            }
            $this->columns[$i] = $column;
        }
    }
}
