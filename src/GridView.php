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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use neoacevedo\gridview\Column\DataColumn;
use neoacevedo\gridview\Support\Html;

/**
 * GridView is used to display data in a table.
 * 
 * A basic usage looks like:
 * ```php
 * <?= GridView::widget([
 *    'dataProvider' => $dataProvider.
 *     'columns' => [
 *        'id',
 *        'name',
 *        'created_at:datetime',
 *        // ...
 *    ],
 * ]) ?>
 * ```
 * 
 *  The `dataProvider` property maybe the an array with nested arrays each one of type `key => value` or you can pass also a `Collection`.
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
     * @see \neoacevedo\gridview\Support\Html::renderTagAttributes() for details on how attributes are being rendered.
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
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public $dataProvider = null;

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
    public $emptyTextOptions = [
        'class' => 'empty',
    ];

    /**
     * The HTML attributes for the table header row.
     * @var array
     */
    public $headerRowOptions = [];

    /**
     * The formatter used to format model attribute values into displayable texts.
     * @var array|null
     */
    public $formatter;

    /**
     * The layout that determines how different sections of the grid view should be organized. The following tokens will be replaced with the corresponding section contents:
     * - `{summary}`: the summary section.
     * - `{errors}`: the filter model error summary.
     * - `{items}`: the list items.
     * - `{sorter}`: the sorter.
     * - `{pager}`: the pager.
     *  
     * @var string
     */
    public $layout = "{summary}\n{items}\n{pager}";

    /** @var array|Closure */
    public $rowOptions = [];

    /**
     * Whether to show the header section of the grid table.
     * @var boolean
     */
    public $showHeader = true;

    /**
     * The HTML content to be displayed as the summary of the grid view.
     * @var string
     */
    public $summary;

    /**
     * The HTML attributes for the summary of the grid view.
     * @var array
     */
    public $summaryOptions = ['class' => 'summary'];

    /** @var array */
    public $tableOptions = ['class' => 'table table-striped table-bordered'];

    /**
     * Get the view / contents that represent the [[GridView]].
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function widget($config = [])
    {
        if (isset($config['dataProvider'])) {
            $this->dataProvider = $config['dataProvider'];
        }

        $this->emptyText = $config['emptyText'] ?? null;

        if ($this->dataProvider === null) {
            throw new Exception('The "dataProvider" property must be set.', 500);
        }

        if ($this->emptyText === null) {
            $this->emptyText = 'No results found.';
        }

        $this->columns = $config['columns'];

        if (isset($config['tableOptions'])) {
            $this->tableOptions = $config['tableOptions'];
        }

        $this->initColumns();

        $summary = $tableOptions = $content = $pager = '';
        if (count($this->dataProvider) > 0) {
            preg_match_all('/{\\w+}/', $this->layout, $matches);

            for ($index = 0; $index < count($matches[0]); $index++) {
                if ($matches[0][$index] === '{summary}') {
                    $summary = $this->renderSummary();
                } elseif ($matches[0][$index] === '{pager}') {
                    $pager = $this->renderPager();
                } else {
                    $content = $this->renderSection($matches[0][$index]);
                }
            }
            // $content = preg_replace_callback('/{\\w+}/', function ($matches) use ($summary) {
            //     $content = $this->renderSection($matches[0]);
            //     return $content === false ? $matches[0] : $content;
            // }, $this->layout);
        } else {
            $content = $this->renderEmpty();
        }

        $tableOptions = Html::renderTagAttributes($this->tableOptions);

        return view('gridview::table', [
            'summary' => $summary,
            'tableOptions' => $tableOptions,
            'content' => $content,
            'pager' => $pager
        ]);
    }

    /**
     * Returns the data models.
     * @return array
     */
    public function getModels()
    {
        return $this->dataProvider->items();
    }

    /**
     * Renders the HTML content indicating that the list view has no data.
     * @return string|HtmlString
     */
    public function renderEmpty()
    {
        if ($this->emptyText === false) {
            return '';
        }

        $options = Html::renderTagAttributes($this->emptyTextOptions);

        Arr::forget($this->emptyTextOptions, 'tag');
        if (!$this->emptyTextOptions['tag']) {
            $tag = "div";
        }

        return new HtmlString("<$tag $options>{$this->emptyText}</$tag>");
    }


    /**
     * Renders dtje data ,pdeñs fpr tje grod voew-
     * @return string The HTML code for the table.
     */
    public function renderItems(): string
    {
        // $caption = $this->renderCaption();
        $columnGroup = $this->renderColumnGroup();
        $tableHeader = $this->showHeader ? $this->renderTableHeader() : false;
        $tableBody = $this->renderTableBody();
        // $tableFooter = false;
        // $tableFooterAfterBody = false;
        // if ($this->showFooter) {
        //     if ($this->placeFooterAfterBody) {
        //         $tableFooterAfterBody = $this->renderTableFooter();
        //     } else {
        //         $tableFooter = $this->renderTableFooter();
        //     }
        // }
        $content = array_filter([
            // $caption,
            $columnGroup,
            $tableHeader,
            // $tableFooter,
            $tableBody,
            // $tableFooterAfterBody,
        ]);

        return implode("\n", $content);
    }

    /**
     * Renders the pager.
     * @return string The rendering result.
     */
    public function renderPager(): string
    {
        $pagination = $this->dataProvider->hasPages();

        if ($pagination === false) {
            return '';
        }

        return $this->dataProvider->links();
    }

    /**
     * Renders validator error of filter model.
     * @return string
     */
    public function renderErrors()
    {
        return '';
    }

    /**
     * Renders the column group HTML.
     * @return HtmlString|false
     */
    public function renderColumnGroup()
    {
        /** @var \neoacevedo\gridview\Column\Column $column */
        foreach ($this->columns as $column) {
            if (!empty($column->options)) {
                $cols = [];
                /** @var \neoacevedo\gridview\Column\Column $col */
                foreach ($this->columns as $col) {
                    $options = Html::renderTagAttributes($col->options);
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

        $options = Html::renderTagAttributes($this->headerRowOptions);

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

        if (is_array($this->dataProvider)) {
            $keys = array_keys($this->dataProvider);
        } elseif ($this->dataProvider instanceof Collection) {
            $keys = $this->dataProvider->keys();
        } else {
            $keys = array_keys($this->dataProvider->items());
        }

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

        $options = Html::renderTagAttributes($options);

        return new HtmlString("<tr $options>" . implode("", $cells) . '</tr>');
    }

    /**
     * Renders a section of the specified name.
     * 
     * If the named section is not supported, false will be returned.
     * @param string $name The section name, e.g., {summary}, {items}.
     * @return string|boolean The rendering result of the section, or false if the named section is not supported.
     */
    public function renderSection(string $name): mixed
    {
        switch ($name) {
            case '{summary}':
                return $this->renderSummary();
            case '{items}':
                return $this->renderItems();
            case '{pager}':
                return $this->renderPager();
            // case '{sorter}':
            //     return $this->renderSorter();
            default:
                return false;
        }
    }

    /**
     * Renders the summary text
     * @return string
     */
    public function renderSummary()
    {
        $count = $this->dataProvider->count();
        if ($count <= 0) {
            return '';
        }
        $summaryOptions = $this->summaryOptions;
        Arr::forget($summaryOptions, 'tag');
        $htmlOptions = Html::renderTagAttributes($summaryOptions);
        /** @var \Illuminate\Pagination\LengthAwarePaginator $pagination */
        if ($this->dataProvider->hasPages() === true) {
            $totalCount = $this->dataProvider->total();
            $begin = ($this->dataProvider->currentPage() - 1) * $this->dataProvider->perPage() + 1;
            $end = $begin + $count - 1;

            if ($begin > $end) {
                $begin = $end;
            }

            if (($summaryContent = $this->summary) === null) {
                return new HtmlString("<div $htmlOptions>Showing <b>$begin-$end</b> of <b>$totalCount</b>.</div>");
            }
        } else {
            $begin = 1;
            $end = $totalCount = $count;
            if (($summaryContent = $this->summary) === null) {
                return new HtmlString("<div $htmlOptions>Showing <b>$begin-$end</b> of <b>$totalCount</b>.</div>");
            }
        }
        if ($summaryContent === '') {
            return '';
        }
        return new HtmlString("<div $htmlOptions>Showing <b>$begin-$end</b> of <b>$totalCount</b>.</div>");
    }

    /**
     * Creates a [[DataColumn]] object based on a string in the format of "attribute" or "attribute:label".
     * @param string $text the column specification string
     * @return DataColumn the column instance
     * @throws Exception if the column specification is invalid
     */
    protected function createDataColumn($text)
    {
        if (!preg_match('/^([^:]+)(:(\w*))?(:(.*))?$/', $text, $matches)) {
            throw new Exception('The column must be specified in the format of "attribute", "attribute:format" or "attribute:format:label"');
        }

        return new DataColumn([
            'grid' => $this,
            'attribute' => $matches[1],
            'format' => isset($matches[3]) ? $matches[3] : 'text',
            'label' => isset($matches[5]) ? $matches[5] : null,
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