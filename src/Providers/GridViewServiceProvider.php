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

namespace neoacevedo\gridview\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use neoacevedo\gridview\View\Components\GridViewComponent;

class GridViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishViews();
            $this->publishAssets();
        }

        if (substr(app()->version(), 0, 1) >= 7) {
            Blade::component('package-gridview', GridViewComponent::class);
        }

        $this->loadViewsFrom(dirname(__DIR__) . '/resources/views', 'gridview');

        Paginator::useBootstrap();

    }

    /**
     * Publishes component/vendor views.
     */
    protected function publishViews(): void
    {
        $this->publishes([
            dirname(__DIR__) . '/resources/views' => base_path('/resources/views/vendor/gridview'),
        ], 'gridview-view');

        $this->publishes([
            dirname(__DIR__) . '/resources/views' => base_path('/resources/views/components'),
        ], 'gridview-component-view');
    }

    /**
     * Publishes component/vendor assets.
     * @return void
     */
    protected function publishAssets(): void
    {
        $this->publishes([
            dirname(__DIR__) . '/assets/js' => public_path('vendor/gridview/assets/js')
        ], 'gridview-assets');

        $this->publishes([
            dirname(__DIR__) . '/assets/css' => public_path('vendor/gridview/assets/css')
        ], 'gridview-assets');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind('neoacevedo\gridview\GridView', function () {
            return $this->app->make(\neoacevedo\gridview\GridView::class);
        });
    }
}
