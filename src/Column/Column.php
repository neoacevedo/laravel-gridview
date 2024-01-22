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
 * Column es la clase base de todas las clases de columnas de [[GridView]] o [[GridViewComponent]].
 */
class Column
{
    /** @var Callable */
    public $content;

    /** @var array|Closure */
    public $contentOptions = [];

    /** @var string|null */
    public $header;

    /** @var array */
    public array $headerOptions = [];

    /** @var array */
    public array $options = [];

    /** @var boolean */
    public $visible = true;

    /** @var \neoacevedo\gridview\GridView */
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
        $options = " ";
        $options .= implode(
            ' ',
            array_map(
                function ($v, $k) {
                    return sprintf("%s=\"%s\"", $k, $v);
                },
                $this->headerOptions,
                array_keys($this->headerOptions)
            )
        );

        return new HtmlString("<th$options>" . $this->renderHeaderCellContent() . "</th>");
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
            debug(call_user_func($this->content, $model, $key, $index, $this));
            return call_user_func($this->content, $model, $key, $index, $this);
        }
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