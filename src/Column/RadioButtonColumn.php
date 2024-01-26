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
 * RadioButtonColumn displays a column of radio buttons in a grid view.
 *
 * To add a RadioButtonColumn to the [[GridView]] or to the [[GridViewComponent]], add it to the columns configuration as follows:
 * ```php
 * 'columns' => [
 *   // ...
 *   [
 *       'class' => 'neoacevedo\gridview\Column\RadioButtonColumn',
 *       // you may configure additional properties here
 *   ],
 * ]
 * ```
 */
class RadioButtonColumn extends Column
{
    /** @var string The name of the input radio button input fields. */
    public string $name = 'radioButtonSelection';

    /** @var array|Closure The HTML attributes for the radio buttons. */
    public $radioOptions = [];

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content !== null) {
            return parent::renderDataCellContent($model, $key, $index);
        }
        if ($this->radioOptions instanceof Closure) {
            $options = call_user_func($this->radioOptions, $model, $key, $index, $this);
        } else {
            $options = $this->radioOptions;
        }
        if (!isset($options['value'])) {
            $options['value'] = is_array($key) ? json_encode($key) : $key;
        }

        $options['checked'] = isset($options['checked']) ? $options['checked'] : false;

        $options = Html::renderTagAttributes($options);

        return new HtmlString('<input type="radio" ' . $options . ' />');
    }
}