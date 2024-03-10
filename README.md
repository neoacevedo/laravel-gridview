Laravel GridView
============

Laravel GridView es un paquete para generar una tabla con datos. Permite generar de manera rápida una tabla a partir de un array de modelos Eloquent o Collections, usando los atributos como columnas y cada fila es un modelo con sus datos.

## Instalación

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


Luego ejecute el siguiente comando _artisan_ para copiar los assets en el directorio _public/vendor/gridview/assets_:

```bash
php artisan vendor:publish --provider="neoacevedo\\gridview\\Providers\\GridViewServiceProvider" --tag=gridview-assets
```

## Uso

Una vez que la extensión está instalada, puede crear la tabla de la siguiente forma:

Registre el servico en el archivo de configuración _app.php_ en la sección `providers`:

```php
...
/*
 * Package Service Providers...
 */
neoacevedo\gridview\Providers\GridViewServiceProvider::class,
...
```

Para Laravel 6.x o uso como clase:

```php
{{ gridview()->widget([
	'dataProvider' => [
		[
			'nombre' => 'Andres',
			'fecha' => 1706200888,
			'email' => 'andres@localhost.com'
		],
		[
			'nombre' => 'Jorge',
			'fecha' => 1706200890,
			'email' => 'jorge@localhost.com'
		],
		[
			'nombre' => 'Nilson',
			'fecha' => 1706200990,
			'email' => 'nilson@localhost.com'
		],
		[
			'nombre' => 'Juan',
			'fecha' => 1706201000,
			'email' => 'juan@localhost.com'
		],
		[
			'nombre' => 'Pedro',
			'fecha' => 1706201010,
			'email' => 'pedro@localhost.com'
		],
		[
			'nombre' => 'Felipe',
			'fecha' => 1706201020,
			'email' => 'felipe@localhost.com'
		],
		[
			'nombre' => 'Fredy',
			'fecha' => 1706201030,
			'email' => 'fredy@localhost.com'
		]
	],
	'tableOptions' => [
	' id' => 'datatable',
	    'class' => 'dataTable'
	],
	'columns' => [
		[
			'attribute' => 'nombre',
			'headerOptions' => ['data-sortable' => 'true']
		],
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

Para Laravel >=7.x, se puede de la forma anterior, o como componente:

```php
<x-package-gridview id="table" class="dataTable"
	:dataProvider="[
		[
			'nombre' => 'Andres',
			'fecha' => 1706200888,
			'email' => 'andres@localhost.com'
		],
		[
			'nombre' => 'Jorge',
			'fecha' => 1706200890,
			'email' => 'jorge@localhost.com'
		],
		[
			'nombre' => 'Nilson',
			'fecha' => 1706200990,
			'email' => 'nilson@localhost.com'
		],
		[
			'nombre' => 'Juan',
			'fecha' => 1706201000,
			'email' => 'juan@localhost.com'
		],
		[
			'nombre' => 'Pedro',
			'fecha' => 1706201010,
			'email' => 'pedro@localhost.com'
		],
		[
			'nombre' => 'Felipe',
			'fecha' => 1706201020,
			'email' => 'felipe@localhost.com'
		],
		[
			'nombre' => 'Fredy',
			'fecha' => 1706201030,
			'email' => 'fredy@localhost.com'
		],
	]" :columns="[  
			[
				'attribute' => 'nombre',
				'headerOptions' => ['data-sortable' => 'true']
			],
			'fecha:datetime', // Formato corto del ejemplo anterior de la columna de fecha. 
			'email:email:Email',
			[
				'class' => '\neoacevedo\gridview\Column\ActionColumn',
				'header' => 'Actions'
			]  
	]" />
```

La propiedad `dataProvider` tiene que ser de tipo `\Illuminate\Contracts\Pagination\LengthAwarePaginator` con el fin de generar el paginador.

En el array de columnas, puede especificar la clase que se encargará de renderizar el contenido de toda la columna a través de la propiedad `class`. Las clases de columna que soporta GridView son:

- `\neoacevedo\gridview\Column\ActionColumn`: Columna que se encarga de renderizar botones para diferentes acciones sobre la fila.
- `\neoacevedo\gridview\Column\CheckboxColumn`: Columna que se encarga de renderizar checkboxes.
- `\neoacevedo\gridview\Column\DataColumn`: Columna que se encarga de renderizar la mayoría de los datos. Es la columna predefinida si se omite la propiedad `class`.
- `\neoacevedo\gridview\Column\RadioButtonColumn`: Columna que se encarga de renderizar inputs de radio
- `\neoacevedo\gridview\Column\SerialColumn`: Columna que renderiza el número de cada fila.

Con cualquiera de las dos opciones, se obtendrá una tabla parecida a la siguiente:

```html
<table id="bootstrap-table" class="table table-bordered table-hover">
    <thead>
        <tr>
            <th data-field="0">
                <div class="th-inner "><input type="checkbox" name="selection_all" class="select-on-check-all"></div>
                <div class="fht-cell"></div>
            </th>
            <th data-field="1">
                <div class="th-inner sortable both">Nombre</div>
                <div class="fht-cell"></div>
            </th>
            <th data-field="2">
                <div class="th-inner ">Fecha</div>
                <div class="fht-cell"></div>
            </th>
            <th data-field="3">
                <div class="th-inner ">Email</div>
                <div class="fht-cell"></div>
            </th>
            <th class="text-center" data-field="4">
                <div class="th-inner ">Actions</div>
                <div class="fht-cell"></div>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr data-index="0" data-key="0">
            <td><input type="checkbox" value="0" class="form-check-input" name="selection[]"></td>
            <td>Andres</td>
            <td>2024-01-25 16:41:28</td>
            <td><a href="mailto:andres@localhost.com">andres@localhost.com</a></td>
            <td class="text-center"><a href="http://localhost:8090/controller/view?id=0" title="messages.view"
                    aria-label="messages.view"><svg aria-hidden="true"
                        style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:1.125em"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                        <path fill="currentColor"
                            d="M573 241C518 136 411 64 288 64S58 136 3 241a32 32 0 000 30c55 105 162 177 285 177s230-72 285-177a32 32 0 000-30zM288 400a144 144 0 11144-144 144 144 0 01-144 144zm0-240a95 95 0 00-25 4 48 48 0 01-67 67 96 96 0 1092-71z">
                        </path>
                    </svg></a> <a href="http://localhost:8090/controller/edit?id=0" title="messages.edit"
                    aria-label="messages.edit"><svg aria-hidden="true"
                        style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:1em"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="currentColor"
                            d="M498 142l-46 46c-5 5-13 5-17 0L324 77c-5-5-5-12 0-17l46-46c19-19 49-19 68 0l60 60c19 19 19 49 0 68zm-214-42L22 362 0 484c-3 16 12 30 28 28l122-22 262-262c5-5 5-13 0-17L301 100c-4-5-12-5-17 0zM124 340c-5-6-5-14 0-20l154-154c6-5 14-5 20 0s5 14 0 20L144 340c-6 5-14 5-20 0zm-36 84h48v36l-64 12-32-31 12-65h36v48z">
                        </path>
                    </svg></a> <a href="http://localhost:8090/controller/delete?id=0" title="messages.delete"
                    aria-label="messages.delete" data-confirm="messages.delete_confirm" data-method="post"><svg
                        aria-hidden="true"
                        style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:.875em"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path fill="currentColor"
                            d="M32 464a48 48 0 0048 48h288a48 48 0 0048-48V128H32zm272-256a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zM432 32H312l-9-19a24 24 0 00-22-13H167a24 24 0 00-22 13l-9 19H16A16 16 0 000 48v32a16 16 0 0016 16h416a16 16 0 0016-16V48a16 16 0 00-16-16z">
                        </path>
                    </svg></a></td>
        </tr>
        <tr data-index="1" data-key="1">
            <td><input type="checkbox" value="1" class="form-check-input" name="selection[]"></td>
            <td>Jorge</td>
            <td>2024-01-25 16:41:30</td>
            <td><a href="mailto:jorge@localhost.com">jorge@localhost.com</a></td>
            <td class="text-center"><a href="http://localhost:8090/controller/view?id=1" title="messages.view"
                    aria-label="messages.view"><svg aria-hidden="true"
                        style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:1.125em"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                        <path fill="currentColor"
                            d="M573 241C518 136 411 64 288 64S58 136 3 241a32 32 0 000 30c55 105 162 177 285 177s230-72 285-177a32 32 0 000-30zM288 400a144 144 0 11144-144 144 144 0 01-144 144zm0-240a95 95 0 00-25 4 48 48 0 01-67 67 96 96 0 1092-71z">
                        </path>
                    </svg></a> <a href="http://localhost:8090/controller/edit?id=1" title="messages.edit"
                    aria-label="messages.edit"><svg aria-hidden="true"
                        style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:1em"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="currentColor"
                            d="M498 142l-46 46c-5 5-13 5-17 0L324 77c-5-5-5-12 0-17l46-46c19-19 49-19 68 0l60 60c19 19 19 49 0 68zm-214-42L22 362 0 484c-3 16 12 30 28 28l122-22 262-262c5-5 5-13 0-17L301 100c-4-5-12-5-17 0zM124 340c-5-6-5-14 0-20l154-154c6-5 14-5 20 0s5 14 0 20L144 340c-6 5-14 5-20 0zm-36 84h48v36l-64 12-32-31 12-65h36v48z">
                        </path>
                    </svg></a> <a href="http://localhost:8090/controller/delete?id=1" title="messages.delete"
                    aria-label="messages.delete" data-confirm="messages.delete_confirm" data-method="post"><svg
                        aria-hidden="true"
                        style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:.875em"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path fill="currentColor"
                            d="M32 464a48 48 0 0048 48h288a48 48 0 0048-48V128H32zm272-256a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zM432 32H312l-9-19a24 24 0 00-22-13H167a24 24 0 00-22 13l-9 19H16A16 16 0 000 48v32a16 16 0 0016 16h416a16 16 0 0016-16V48a16 16 0 00-16-16z">
                        </path>
                    </svg></a></td>
        </tr>
    </tbody>
</table>
```

### Sobrescribir la plantilla `Blade`

Si desea sobrescribir la vista que genera la tabla, ejecute el siguiente comando para que copie los archivos de vista en 
_resources/views/vendor/gridview_:

```bash
php artisan vendor:publish --provider="neoacevedo\\gridview\\Providers\\GridViewServiceProvider" --tag=gridview-view
```

Si está usando GridView como componente, la vista será copiada en _resources/views/components_:

```bash
php artisan vendor:publish --provider="neoacevedo\\gridview\\Providers\\GridViewServiceProvider" --tag=gridview-component-view
```
