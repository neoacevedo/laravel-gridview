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

use Illuminate\Support\Facades\Blade;
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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'gridview');

        if ($this->app->runningInConsole()) {
            $this->publishAssets();
        }

        if (substr(app()->version(), 0, 1) >= 7) {
            Blade::component('package-gridview', GridViewComponent::class);
        }
    }

    /**
     * Publish datatables assets.
     */
    protected function publishAssets(): void
    {
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('/resources/views/vendor/gridview'),
        ], 'gridview');

        $this->publishes([
            __DIR__.'/../resources/views/components' => base_path('/resources/views/components'),
        ]);
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
