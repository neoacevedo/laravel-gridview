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

namespace neoacevedo\gridview\View\Components;

use Closure;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\View\Component;
use neoacevedo\gridview\Column\DataColumn;
use neoacevedo\gridview\Support\Html;

/**
 * This is the Laravel 7+ component for the [[GridView]].
 *
 * A basic usage looks like the following:
 * ```html
 * <x-package-gridview id="table" class="dataTable" :dataProvider="$dataProvider"
 *   :columns="[
 *       'id',
 *       'name',
 *       'created_at',
 *       // ...
 *     ]" />
 * ```
 */
class GridViewComponent extends Component
{
    const FILTER_POS_HEADER = 'header';
    const FILTER_POS_FOOTER = 'footer';
    const FILTER_POS_BODY = 'body';

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
    public $captionOptions;

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
     * which only contains [[DataColumn::attribute|attribute]], [[DataColumn::format|format]],
     * and/or [[DataColumn::label|label]] options: `"attribute:format:label"`.
     * For example, the above "name" column can also be specified as: `"name:text:Name"`.
     * Both "format" and "label" are optional. They will take default values if absent.
     *
     * Using the shortcut format the configuration for columns in simple cases would look like this:
     *
     * ```php
     * [
     *     'id',
     *     'amount:currency:Total Amount',
     *     'created_at:datetime',
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
    public $columns;

    /**
     * The data provider for the view.
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public $dataProvider;

    /**
     * The default data column class if the class name is not explicitly specified when configuring a data column.
     * @var string
     */
    public $dataColumnClass;

    /**
     * The HTML display when the content of a cell is empty. This property is used to render cells taht have not defined 
     * content, e.g. empty footer or filter cells.
     * 
     * Note that this is not use by the {@see \neoacevedo\gridview\Column\DataColumn} if a data item is null. In that case a 
     * null value will be used to indicate an ampty data value.
     * @var string
     */
    public string $emptyCell;

    /**
     * The HTML content to be displayed when [[dataProvider]] does not have any data.
     * @var string|false
     */
    public $emptyText;

    /**
     * The HTML attributes for the emptyText of the list.
     * @var array
     */
    public array $emptyTextOptions;

    /**
     * Whether the filters should be displayed in the grid view. Valid values include:
     * - @see GridViewComponent::FILTER_POS_HEADER: the filters will be displayed on top of each column's header cell.
     * - @see GridViewComponent::FILTER_POS_BODY: the filters will be displayed right below each column's header cell.
     * - @see GridViewComponent::FILTER_POS_FOOTER: the filters will be displayed below each column's footer cell.
     * @var string
     */
    public string $filterPosition;

    /**
     * The HTML attributes for the filter row element.
     * 
     * See also {@link neoacevedo\Support\Html::renderTagAttributes()} for details on how attributes are being rendered.
     * @var array
     */
    public array $filterRowOptions;

    /**
     * The HTML attributes for the table footer row.
     * See also {@see \neoacevedo\gridview\Support\Html::renderTagAttributes()} for details on how attributes are being rendered.
     * @var array
     */
    public array $footerRowOptions;

    /**
     * The HTML attributes for the table header row.
     * @var array
     */
    public array $headerRowOptions;

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
    public $layout;

    /** @var array|Closure */
    public $rowOptions;

    /**
     * Whether to show the footer section of the grid table.
     * @var bool
     */
    public $showFooter;

    /**
     * Whether to show the header section of the grid table.
     * @var boolean
     */
    public $showHeader;

    /**
     * The HTML content to be displayed as the summary of the grid view.
     * @var string
     */
    public $summary;

    /**
     * The HTML attributes for the summary of the grid view.
     * @var array
     */
    public $summaryOptions;

    /**
     * Constructor.
     *
     * The default implementation initializes the object with the given parameters.
     *
     * @param string|null $caption
     * @param array $captionOptions
     * @param array $columns
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $dataProvider The data provider for the view.
     * @param array $columns grid column configuration
     * @param array|null $headerRowOptions The HTML attributes for the table header row.
     * @param array|null $formatter The formatter used to format model attribute values into displayable texts.
     * @param array|Closure $rowOptions
     */
    public function __construct(
        mixed $dataProvider = null,
        mixed $caption = null,
        array $captionOptions = [],
        array $columns = [],
        mixed $dataColumnClass = null,
        string $emptyCell = '&nbsp;',
        mixed $emptyText = null,
        array $emptyTextOptions = ['class' => 'empty'],
        mixed $formatter = null,
        string $filterPosition = self::FILTER_POS_BODY,
        array $filterRowOptions = ['class' => 'filters',],
        array $footerRowOptions = [],
        array $headerRowOptions = [],
        string $layout = "{summary}\n{items}\n{pager}",
        mixed $rowOptions = null,
        bool $showFooter = false,
        bool $showHeader = true,
        string $summary = null,
        array $summaryOptions = ['class' => 'summary'],
        // array $tableOptions = ['class' => 'table table-striped table-bordered']
    ) {
        $this->dataProvider = $dataProvider;

        $this->caption = $caption;

        $this->captionOptions = $captionOptions;

        $this->columns = $columns;

        $this->dataColumnClass = $dataColumnClass;

        $this->emptyCell = $emptyCell;

        $this->emptyText = $emptyText;

        $this->emptyTextOptions = $emptyTextOptions;

        $this->filterPosition = $filterPosition;

        $this->filterRowOptions = $filterRowOptions;

        $this->formatter = $formatter;

        $this->footerRowOptions = $footerRowOptions;

        $this->headerRowOptions = $headerRowOptions;

        $this->layout = $layout;

        $this->rowOptions = $rowOptions;

        $this->showFooter = $showFooter;

        $this->showHeader = $showHeader;

        $this->summary = $summary;

        $this->summaryOptions = $summaryOptions;

        // $this->tableOptions = $tableOptions;

        if ($this->dataProvider === null) {
            throw new Exception('The "dataProvider" property must be set.', 500);
        }

        if ($this->emptyText === null) {
            $this->emptyText = 'No results found.';
        }

        $this->initColumns();
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
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        $summary = $content = $pager = '';
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
        } else {
            $content = $this->renderEmpty();
        }

        return view('gridview::components.table', [
            'content' => $content,
            'pager' => $pager,
            'resumen' => $summary,
        ]);
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
                    $options = Html::renderTagAttributes($col->options);
                    $cols[] = "<col $options></col>";
                }
                return new HtmlString("<colgroup>" . implode("\n", $cols) . "</colgroup>");
            }
        }
        return false;
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
     * Renders the filter.
     * @return HtmlString
     */
    public function renderFilters()
    {
        $cells = [];
        /** @var mixed $column */
        foreach ($this->columns as $column) {
            $cells[] = $column->renderFilterCell();
        }

        $options = Html::renderTagAttributes($this->filterRowOptions);

        $content = "<tr $options>" . implode('', $cells) . "</tr>";

        return new HtmlString("<thead>\n" . $content . "\n</thead>");
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
        $tableFooter = false;
        if ($this->showFooter) {
            $tableFooter = $this->renderTableFooter();
        }
        $content = array_filter([
            // $caption,
            $columnGroup,
            $tableHeader,
            $tableBody,
            $tableFooter,
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
     * Renders the table body.
     * @return HtmlString
     */
    public function renderTableBody(): string
    {
        $models = array_values($this->getModels());

        $keys = array_keys($this->dataProvider->items());


        $rows = [];

        foreach ($models as $index => $model) {
            $key = $keys[$index];

            $rows[] = $this->renderTableRow($model, $key, $index);
        }

        if (empty($rows) && $this->emptyText !== false) {
            $colspan = count($this->columns);
            return str("<tbody>\n<tr><td colspan=\"$colspan\">" . $this->renderEmpty() . "</td></tr>\n</tbody>")->toHtmlString();
        }
        return str("<tbody>\n" . implode("\n", $rows) . "\n</tbody>")->toHtmlString();
    }

    /**
     * Renders the table footer.
     * @return HtmlString
     */
    public function renderTableFooter()
    {
        $cells = [];
        foreach ($this->columns as $column) {
            /* @var $column Column */
            $cells[] = $column->renderFooterCell();
        }
        $options = Html::renderTagAttributes($this->footerRowOptions);
        $content = "<tr $options>" . implode('', $cells) . "</tr>";
        if ($this->filterPosition === self::FILTER_POS_FOOTER) {
            $content .= $this->renderFilters();
        }
        return str("<tfoot>\n" . $content . "\n</tfoot>")->toHtmlString();
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

    #region protected

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

    #endregion
}