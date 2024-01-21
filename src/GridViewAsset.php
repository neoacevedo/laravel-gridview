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

use Illuminate\Support\HtmlString;

/**
 * GridViewAsset registra los assets CSS y JS de datatable para [[GridView]] o [[GridViewComponent]].
 *
 * Si se quiere usar datatables con las tablas, hay que agregar los siguientes códigos en la vista donde se requiera hacerlo:
 *
 * - Para registrar los assets CSS, en la sección CSS de la vista:
 * ```php
 * {{register_css_asset()}}
 * ...
 * ```
 *
 * - Para registrar los assets JS, en la sección JS de la vista:
 * ```php
 * {{register_js_asset()}}
 * ...
 * ```
 *
 * Cuando se haya registrado el JS asset, se inicia el datatable de esta manera:
 * ```php
 * <!-- datatable para una sola tabla -->
 * {{init('#tabla1')}}
 *
 * <!-- datatable para tablas específicas -->
 * {{init('#tabla1, #tabla3')}}
 *
 * <!-- datatable para todas las tablas en la vista con una clase en común -->
 * {{init('table.<className>')}}
 * ```
 */
class GridViewAsset
{
    /**
     * Registra el archivo CSS desde datatables.net
     * @return HtmlString
     * @static
     */
    public static function registerCss()
    {
        // return new HtmlString('<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" type="text/css" />');
        return new HtmlString('<link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.22.2/dist/bootstrap-table.min.css">');
    }

    /**
     * Registra el archivo JS desde datatables.net
     * @return HtmlString
     * @static
     */
    public static function registerJs()
    {
        $js = <<<HTML
            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
            <script src="https://unpkg.com/bootstrap-table@1.22.2/dist/bootstrap-table.min.js"></script>
            <script>
                document.querySelectorAll('a').forEach(function(item) {
                    if(item.hasAttribute('data-confirm')) {
                        item.addEventListener('click', function(e) {   
                            return confirm(item.getAttribute('data-confirm')) || e.preventDefault();
                        })
                    }
                });
            </script>
        HTML;
        return new HtmlString($js);
    }

    /**
     * Inicia la configuración de la tabla.
     * @param string $tableId ID de la tabla.
     * Si se tiene más de una tabla y se quiere activar el datatable en todas las tablas, entonces
     * se pasa como `table.className`.
     * @return HtmlString
     * @static
     */
    public static function init($tableId)
    {
        $js = <<<HTML
            <script>
                document.addEventListener('DOMContentLoaded', function () {                   
                    $("$tableId").DataTable({
                        orderCellsTop: true
                    });
                });
            </script>
        HTML;
        return new HtmlString($js);
    }
}