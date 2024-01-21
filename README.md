Laravel GridView
============

Laravel GridView es un paquete para generar una tabla con datos.

Instalación
------------

Descomprima el zip en el directorio `vendor` de su proyecto Laravel, luego agrege como última sección dentro de su archivo `composer.json`:

```
    "repositories": [
        {
            "type": "path",
            "url": "./vendor/neoacevedo/laravel-gridview"
        }
    ]
```

Y luego ejecute el siguiente comando:

```
php composer.phar update
```

Uso
-----

Una vez que la extensión está instalada, puede crear la tabla de la siguiente forma:

Para Laravel 6+:

```php
{{ gridview()->widget([
	'dataProvider' => [['texto' => 'texto 1']],
	'tableOptions' => [
	'id' => 'datatable',
	'class' => 'dataTable'
	],
	'columns' => [
	[
	'class' => '\neoacevedo\gridview\Column\CheckboxColumn'
	],
	[
	'attribute' => 'texto',
	'label' => 'Columna 1',
	'value' => function($model) {
	return $model["texto"];
	}
	],
	[
	'class' => '\neoacevedo\gridview\Column\ActionColumn'
	]
	]
	])
}}
```

Para Laravel 7+, se puede de la forma anterior, o como componente:

```php
<x-package-gridview id="table" class="dataTable"
	:dataProvider="[
		['texto' => 'texto 1'],
		['texto' => 'texto 2'],
		['texto' => 'texto 3'],
		['texto' => 'texto 4'],
		['texto' => 'texto 5'],
		['texto' => 'texto 6'],
		['texto' => 'texto 7'],
		['texto' => 'texto 8'],
		['texto' => 'texto 9'],
		['texto' => 'texto 10'],
		['texto' => 'texto 11'],
		['texto' => 'texto 12'],
	]" :columns="[
	'texto:Columna',
	[
		'class' => '\neoacevedo\gridview\Column\ActionColumn'
	]
    ]"
/>
```

Para la propiedad `dataProvider` puede pasarse un array o una colección. Para esta última desde el controlador puede pasarla desde la base de datos.
