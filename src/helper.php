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

use neoacevedo\gridview\GridView;
use neoacevedo\gridview\GridViewAsset;

if (!function_exists("register_css_asset")) {
    /**
     * Registra el archivo CSS desde datatables.net
     */
    function register_css_asset()
    {
        return GridViewAsset::registerCss();
    }
}

if (!function_exists("register_js_asset")) {
    /**
     * Registra el archivo JS desde datatables.net
     */
    function register_js_asset()
    {
        return GridViewAsset::registerJs();
    }
}

if (!function_exists("gridview")) {
    /**
     * Crea la instancia del GridView
     */
    function gridview()
    {
        return new GridView();
    }
}

if (!function_exists("init")) {
    /**
     * Registra el código JS de inicialización del dataTable.
     * @param string $id id de la tabla.
     * Si se tiene más de una tabla y se quiere activar el datatable en todas las tablas, entonces
     * se pasa como `table.className`.
     * @return HtmlString
     */
    function init($tableId)
    {
        return GridViewAsset::init($tableId);
    }
}
