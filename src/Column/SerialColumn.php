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

/**
 * SerialColumn displays a column of row numbers (1-based).
 *
 * To add a SerialColumn to the [[GridView]] or to the [[GridViewComponent]], add it to the columns configuration as follows:
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
class SerialColumn extends Column
{
    /** @var string|null The header cell content. */
    public $header = "#";

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->grid->dataProvider->hasPages()) {
            $pageSize = $this->grid->dataProvider->perPage();
            $offset = $pageSize < 1 ? 0 : ($this->grid->dataProvider->currentPage() - 1) * $pageSize;

            return $offset + $index + 1;
        }
        return $index + 1;
    }
}
