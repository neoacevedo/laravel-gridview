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
use neoacevedo\gridview\Support\Html;

/**
 * Column is the base class of all GridView [1] or GridViewComponent [2] column classes.
 * 
 * @see \neoacevedo\gridview\GridView [1]
 * @see \neoacevedo\gridview\View\Components\GridViewComponent [2]
 */
class Column
{
    /** @var callable This is a callable that will be used to generate the content of each cell. */
    public $content;

    /** @var array|Closure The HTML attributes for the data cell tag. */
    public $contentOptions = [];

    /**
     * The HTML attributes for the filter cell tag.
     * See also {@see \neoacevedo\gridview\Support\Html::renderTagAttributes()} for details on how attributes are being rendered.
     * @var array
     */
    public $filterOptions = [];

    /** @var string|null The header cell content. */
    public $header;

    /** @var array The HTML attributes for the header cell tag. */
    public array $headerOptions = [];

    /** @var array The HTML attribute for the column group tag. */
    public $options = [];

    /** @var boolean Whether this column is visible. */
    public $visible = true;

    /** @var \neoacevedo\gridview\GridView The gridview object that owns this column */
    public $grid;

    /**
     * Constructor
     */
    public function __construct($config = [])
    {
        $this->options = $config['options'] ?? [];

        $this->header = @$config['header'];

        $this->headerOptions = $config['headerOptions'] ?? [];

        $this->contentOptions = $config['contentOptions'] ?? [];

        if (isset($config['visible'])) {
            $this->visible = $config['visible'];
        }

        $this->grid = $config['grid'] ?? null;
    }


    /**
     * Renders the data cell content.
     * @param mixed $model the data model
     * @param mixed $key the key associated with the data model
     * @param int $index the zero-based index of the data model among the models array returned by [[GridView::dataProvider]].
     * @return string the rendering result
     */
    public function renderDataCell($model, $key, $index)
    {
        if ($this->contentOptions instanceof Closure) {
            $options = call_user_func($this->contentOptions, $model, $key, $index, $this);
        } else {
            $options = "";

            $options = Html::renderTagAttributes($this->contentOptions);
        }
        return new HtmlString("<td $options>" . $this->renderDataCellContent($model, $key, $index) . '</td>');
    }

    /**
     * Renders the header cell.
     * @return HtmlString
     */
    public function renderHeaderCell()
    {
        $options = Html::renderTagAttributes($this->headerOptions);

        return new HtmlString("<th $options>" . $this->renderHeaderCellContent() . "</th>");
    }

    /**
     * Renders the filter cell.
     * @return HtmlString
     */
    public function renderFilterCell()
    {
        $options = Html::renderTagAttributes($this->filterOptions);
        return str("<td $options>" . $this->renderFilterCellContent() . "</td>")->toHtmlString();
    }

    /**
     * Returns header cell label.
     * This method may be overridden to customize the label of the header cell.
     * @return string
     */
    protected function getHeaderCellLabel()
    {
        return $this->grid->emptyCell;
    }

    /**
     * Renders the data cell content.
     * @param mixed $model The data model
     * @param mixed $key The key associated with the data model
     * @param int $index The zero-based index of the data model among the models array returned by  [[GridView::dataProvider]]
     * @return string The rendering result
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content !== null) {
            return call_user_func($this->content, $model, $key, $index, $this);
        }
        return $this->grid->emptyCell;
    }

    /**
     * Renders the filter cell content.
     * The default implementation simply renders a space. This method may be overridden to customize the rendering of the filter cell (if any).
     * @return string
     */
    protected function renderFilterCellContent()
    {
        return $this->grid->emptyCell;
    }

    /**
     * Renders the header cell content.
     * The default implementation simply renders [[header]].
     * This method may be overridden to customize the rendering of the header cell.
     * @return string the rendering result
     */
    protected function renderHeaderCellContent()
    {
        return $this->header !== null && trim($this->header) !== '' ? $this->header : $this->getHeaderCellLabel();
    }
}