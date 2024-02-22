Laravel GridView
============

Laravel GridView es un paquete para generar una tabla con datos, inspirado en el [widget yii2 GridView](https://www.yiiframework.com/doc/guide/2.0/es/output-data-widgets#grid-view). Permite generar de manera rápida una tabla a partir de modelos Eloquent o Collections paginados o también de arrays anidados paginados, usando los atributos como columnas y cada fila es un modelo con sus datos.

Instalación
------------

La forma preferida de instalar esta extensión es a través de [composer](http://getcomposer.org/download/).

Luego ejecute

```
php composer.phar require --prefer-dist neoacevedo/laravel-gridview "*"
```

o agregue

```
"neoacevedo/laravel-gridview": "*"
```

a la sección require de su archivo `composer.json`.

Uso
-----

Una vez que la extensión está instalada, puede crear la tabla de la siguiente forma:

Para Laravel 6+:

```php
{{ gridview()->widget([
 'dataProvider' => $dataProvider,
 'tableOptions' => [
     'class' => 'table table-bordered'
 ],
 'columns' => [
  'nombre',
  [
   'attribute' => 'fecha',
   'format' => ['datetime', 'd/m/Y H:i:s']
  ],
  'email:email:Email',
  [
   'class' => '\neoacevedo\gridview\Column\ActionColumn',
   'header' => 'Actions'
  ]
 ]
]) }}
```

Para Laravel 7+, se puede de la forma anterior, o como componente:

```php
<x-package-gridview id="table" class="table table-bordered"
 :dataProvider="$dataProvider" :columns="[  
   'nombre',
   'fecha:datetime', // Formato corto del ejemplo anterior de la columna de fecha. 
   'email:email:Email',
   [
    'class' => '\neoacevedo\gridview\Column\ActionColumn',
    'header' => 'Actions'
   ]  
 ]" />
```

Desde el controlador (para un array anidado):

```php
$data = [
            [
                'nombre' => 'Andres',
                'fecha' => 1706200888,
                'email' => 'andres@localhost.com',
            ],
            [
                'nombre' => 'Jorge',
                'fecha' => 1706200890,
                'email' => 'jorge@localhost.com',
            ],
            [
                'nombre' => 'Nelson',
                'fecha' => 1706200990,
                'email' => 'nilson@localhost.com',
            ],
            [
                'nombre' => 'Juan',
                'fecha' => 1706201000,
                'email' => 'juan@localhost.com',
            ],
            [
                'nombre' => 'Pedro',
                'fecha' => 1706201010,
                'email' => 'pedro@localhost.com',
            ],
            [
                'nombre' => 'Felipe',
                'fecha' => 1706201020,
                'email' => 'felipe@localhost.com',
            ],
            [
                'nombre' => 'Fredy',
                'fecha' => 1706201030,
                'email' => 'fredy@localhost.com',
            ],
            [
                'nombre' => 'Richard',
                'fecha' => 1706201040,
                'email' => 'richard@localhost.com',
            ],
        ];

        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new LengthAwarePaginator($this->forPage($page, $perPage), $total ?: $this->count(), $perPage, $page, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]);
        });

        $dataProvider = collect($data)->paginate(5);
```

O con un modelo de datos:

```php
$dataProvider = CollectionDm::paginate(5);
```

Luego enviar el dataProvider a la vista:

```php
return response()->view('index', compact('dataProvider'));
```

En el array de columnas, puede especificar la clase que se encargará de renderizar el contenido de toda la columna a través de la propiedad `class`. Las clases de columna que soporta GridView son:

- `\neoacevedo\gridview\Column\ActionColumn`: Columna que se encarga de renderizar botones para diferentes acciones sobre la fila.
- `\neoacevedo\gridview\Column\CheckboxColumn`: Columna que se encarga de renderizar checkboxes.
- `\neoacevedo\gridview\Column\DataColumn`: Columna que se encarga de renderizar la mayoría de los datos. Es la columna predefinida si se omite la propiedad `class`.
- `\neoacevedo\gridview\Column\RadioButtonColumn`: Columna que se encarga de renderizar inputs de radio
- `\neoacevedo\gridview\Column\SerialColumn`: Columna que renderiza el número de cada fila.

Con cualquiera de las dos opciones, se obtendrá una tabla como la siguiente:

```html
<div class="summary">Showing <b>1-5</b> of <b>13</b>.</div>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Descripción</th>
            <th>Estado predefinido</th>
            <th>Retraso</th>
            <th>Cargo x Día</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <tr data-key="0">
            <td>1</td>
            <td>Ficción para adultos</td>
            <td>No</td>
            <td>21</td>
            <td>$&nbsp;0.05</td>
            <td><a href="http://localhost:8090/site/class-delete/0"><span class="bi bi-trash"></span></a></td>
        </tr>
        <tr data-key="1">
            <td><input type="checkbox" value="1" class="form-check-input" name="selection[]"></td>
            <td>2</td>
            <td>No ficción para adultos</td>
            <td>Sí</td>
            <td>21</td>
            <td>$&nbsp;0.05</td>
            <td><a href="http://localhost:8090/site/class-delete/1"><span class="bi bi-trash"></span></a></td>
        </tr>
        <tr data-key="2">
            <td><input type="checkbox" value="2" class="form-check-input" name="selection[]"></td>
            <td>3</td>
            <td>Casetes</td>
            <td>No</td>
            <td>7</td>
            <td>$&nbsp;0.05</td>
            <td><a href="http://localhost:8090/site/class-delete/2"><span class="bi bi-trash"></span></a></td>
        </tr>
        <tr data-key="3">
            <td><input type="checkbox" value="3" class="form-check-input" name="selection[]"></td>
            <td>4</td>
            <td>Discos compactos</td>
            <td>No</td>
            <td>7</td>
            <td>$&nbsp;0.15</td>
            <td><a href="http://localhost:8090/site/class-delete/3"><span class="bi bi-trash"></span></a></td>
        </tr>
        <tr data-key="4">
            <td><input type="checkbox" value="4" class="form-check-input" name="selection[]"></td>
            <td>5</td>
            <td>Software de Computadora</td>
            <td>No</td>
            <td>7</td>
            <td>$&nbsp;0.15</td>
            <td><a href="http://localhost:8090/site/class-delete/4"><span class="bi bi-trash"></span></a></td>
        </tr>
    </tbody>
</table>
<nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
    <div class="flex justify-between flex-1 sm:hidden">
        <span
            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
            « Previous
        </span>

        <a href="http://localhost:8090/site/component?page=2"
            class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
            Next »
        </a>
    </div>

    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700 leading-5">
                Showing
                <span class="font-medium">1</span>
                to
                <span class="font-medium">5</span>
                of
                <span class="font-medium">13</span>
                results
            </p>
        </div>

        <div>
            <span class="relative z-0 inline-flex shadow-sm rounded-md">
                <span aria-disabled="true" aria-label="&amp;laquo; Previous">
                    <span
                        class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5"
                        aria-hidden="true">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </span>
                </span>
                <span aria-current="page">
                    <span
                        class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5">1</span>
                </span>
                <a href="http://localhost:8090/site/component?page=2"
                    class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150"
                    aria-label="Go to page 2">
                    2
                </a>
                <a href="http://localhost:8090/site/component?page=3"
                    class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150"
                    aria-label="Go to page 3">
                    3
                </a>


                <a href="http://localhost:8090/site/component?page=2" rel="next"
                    class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150"
                    aria-label="Next &amp;raquo;">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                </a>
            </span>
        </div>
    </div>
</nav>
```
