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

use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

/**
 * ActionColumn es una columna para [[GridView]] o [[GridViewComponent]] que muestra botones para ver y manipular los elementos.
 *
 * Para agregar una columna ActionColumn al [[GridView]] o [[GridViewComponent]], agréguelo a la configuración de columnas de la siguiente manera:
 */
class ActionColumn extends Column
{
    /** @var array */
    public array $buttonOptions = [];

    public array $buttons = [];

    /** @var string|null */
    public $controller;

    /** @var array */
    public array $headerOptions = [
        'class' => 'action-column',
    ];

    /** @var array */
    public array $icons = [
        'eye-open' => '<svg aria-hidden="true" style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:1.125em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M573 241C518 136 411 64 288 64S58 136 3 241a32 32 0 000 30c55 105 162 177 285 177s230-72 285-177a32 32 0 000-30zM288 400a144 144 0 11144-144 144 144 0 01-144 144zm0-240a95 95 0 00-25 4 48 48 0 01-67 67 96 96 0 1092-71z"/></svg>',
        'pencil' => '<svg aria-hidden="true" style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:1em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M498 142l-46 46c-5 5-13 5-17 0L324 77c-5-5-5-12 0-17l46-46c19-19 49-19 68 0l60 60c19 19 19 49 0 68zm-214-42L22 362 0 484c-3 16 12 30 28 28l122-22 262-262c5-5 5-13 0-17L301 100c-4-5-12-5-17 0zM124 340c-5-6-5-14 0-20l154-154c6-5 14-5 20 0s5 14 0 20L144 340c-6 5-14 5-20 0zm-36 84h48v36l-64 12-32-31 12-65h36v48z"/></svg>',
        'trash' => '<svg aria-hidden="true" style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:.875em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M32 464a48 48 0 0048 48h288a48 48 0 0048-48V128H32zm272-256a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zM432 32H312l-9-19a24 24 0 00-22-13H167a24 24 0 00-22 13l-9 19H16A16 16 0 000 48v32a16 16 0 0016 16h416a16 16 0 0016-16V48a16 16 0 00-16-16z"/></svg>',
    ];

    /** @var string */
    public string $template = '{view} {edit} {delete}';

    /** @var callable|null */
    public $urlCreator;

    /**
     * Visibility conditions for each button.
     * @var array
     */
    public array $visibleButtons = [];

    /**
     * Constructor
     */
    public function __construct($config = [])
    {
        $this->buttonOptions = $config['buttonOptions'] ?? [];
        $this->buttons = $config['buttons'] ?? [];
        $this->controller = @$config['controller'];
        $this->header = $config['header'] ?? trans("messages.actions");
        $this->headerOptions = $config['headerOptions'] ?? ['class' => 'action-column'];
        $this->icons = $config['icons'] ?? [
            'eye-open' => '<svg aria-hidden="true" style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:1.125em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M573 241C518 136 411 64 288 64S58 136 3 241a32 32 0 000 30c55 105 162 177 285 177s230-72 285-177a32 32 0 000-30zM288 400a144 144 0 11144-144 144 144 0 01-144 144zm0-240a95 95 0 00-25 4 48 48 0 01-67 67 96 96 0 1092-71z"/></svg>',
            'pencil' => '<svg aria-hidden="true" style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:1em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M498 142l-46 46c-5 5-13 5-17 0L324 77c-5-5-5-12 0-17l46-46c19-19 49-19 68 0l60 60c19 19 19 49 0 68zm-214-42L22 362 0 484c-3 16 12 30 28 28l122-22 262-262c5-5 5-13 0-17L301 100c-4-5-12-5-17 0zM124 340c-5-6-5-14 0-20l154-154c6-5 14-5 20 0s5 14 0 20L144 340c-6 5-14 5-20 0zm-36 84h48v36l-64 12-32-31 12-65h36v48z"/></svg>',
            'trash' => '<svg aria-hidden="true" style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:.875em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M32 464a48 48 0 0048 48h288a48 48 0 0048-48V128H32zm272-256a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zM432 32H312l-9-19a24 24 0 00-22-13H167a24 24 0 00-22 13l-9 19H16A16 16 0 000 48v32a16 16 0 0016 16h416a16 16 0 0016-16V48a16 16 0 00-16-16z"/></svg>',
        ];

        if (isset($config['urlCreator'])) {
            $this->urlCreator = $config['urlCreator'];
        }

        if (isset($config['template'])) {
            $this->template = $config['template'];
        }

        if (isset($config['visibleButtons'])) {
            $this->visibleButtons = $config['visibleButtons'];
        }

        $this->initDefaultButtons();
    }

    /**
     * @param string $action The button name (the action ID)
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param mixed $key
     * @param int $index
     * @return string
     */
    public function createUrl($action, $model, $key, $index)
    {
        if (is_callable($this->urlCreator)) {
            return call_user_func($this->urlCreator, $action, $model, $key, $index, $this);
        }
        $params = is_array($key) ? $key : ['id' => (string) $key];
        $params[0] = $this->controller ? $this->controller . '/' . $action : '';

        return $this->controller ? action([$this->controller, $action]) : route($action, $params);
    }

    /**
     * Initializes the default button rendering callback for single button.
     * @return void
     */
    protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {
                switch ($name) {
                    case 'view':
                        $title = trans('messages.view');
                        break;
                    case 'edit':
                        $title = trans('messages.edit');
                        break;
                    case 'delete':
                        $title = trans('messages.delete');
                        break;
                    default:
                        $title = ucfirst($name);
                }

                $arrayOptions = array_merge([
                    'title' => $title,
                    'aria-label' => $title,
                ], $additionalOptions, $this->buttonOptions);

                $options = implode(' ', array_map(
                    function ($v, $k) {
                        return sprintf("%s=\"%s\"", $k, $v);
                    },
                    $arrayOptions,
                    array_keys($arrayOptions)
                ));

                $icon = isset($this->icons[$iconName])
                    ? $this->icons[$iconName]
                    : new HtmlString('<span class="glyphicon glyphicon-$iconName"></span>');
                return new HtmlString('<a href="' . $url . '" ' . $options . '>' . $icon . '</a>');
            };
        }
    }

    /**
     * Initializes the default button rendering callbacks.
     * @return void
     */
    protected function initDefaultButtons()
    {
        $this->initDefaultButton('view', 'eye-open');
        $this->initDefaultButton('edit', 'pencil');
        $this->initDefaultButton('delete', 'trash', [
            'data-confirm' => trans('messages.delete_confirm'),
            'data-method' => 'post',
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) use ($model, $key, $index) {
            $name = $matches[1];
            if (isset($this->visibleButtons[$name])) {
                $isVisible = $this->visibleButtons[$name] instanceof \Closure
                    ? call_user_func($this->visibleButtons[$name], $model, $key, $index)
                    : $this->visibleButtons[$name];
            } else {
                $isVisible = true;
            }
            if ($isVisible && isset($this->buttons[$name])) {
                $url = $this->createUrl($name, $model, $key, $index);
                return call_user_func($this->buttons[$name], $url, $model, $key);
            }
            return '';
        }, $this->template);
    }
}
