<?php

namespace Dcpl\Ui;

use Dcpl\Ui\Console\Commands\InstallCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class UiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ── Views ────────────────────────────────────────────────────────────
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'dcpl');

        // Anonymous components under the x-dcpl:: namespace.
        // e.g. x-dcpl::logo, x-dcpl::badge, x-dcpl::card, etc.
        Blade::anonymousComponentNamespace('dcpl::components', 'dcpl');

        // ── CSS route ────────────────────────────────────────────────────────
        $this->registerCssRoute();

        // ── Publishable assets ───────────────────────────────────────────────
        // php artisan vendor:publish --tag=dcpl-ui-views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/dcpl'),
        ], 'dcpl-ui-views');

        // php artisan vendor:publish --tag=dcpl-ui-assets
        $this->publishes([
            __DIR__ . '/../resources/dist/dcpl.css'          => public_path('vendor/dcpl/dcpl.css'),
            __DIR__ . '/../resources/img/favicon.ico'         => public_path('favicon.ico'),
            __DIR__ . '/../resources/img/apple-touch-icon.png' => public_path('apple-touch-icon.png'),
        ], 'dcpl-ui-assets');

        // ── Artisan commands ─────────────────────────────────────────────────
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Serve compiled CSS directly from the package with MD5 cache-busting.
     *
     * Include in any layout:
     *
     *   <link rel="stylesheet" href="{{ route('dcpl.ui.css') }}?v={{ $dcplUiCssVersion ?? 'dev' }}">
     *
     * The consuming package's ServiceProvider can share $dcplUiCssVersion to
     * all views:
     *
     *   View::share('dcplUiCssVersion', md5_file(
     *       base_path('vendor/dcplibrary/ui/resources/dist/dcpl.css')
     *   ));
     */
    protected function registerCssRoute(): void
    {
        $css = __DIR__ . '/../resources/dist/dcpl.css';

        Route::get('dcpl/ui/css', static function () use ($css) {
            return response()->file($css, [
                'Content-Type'  => 'text/css',
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        })->name('dcpl.ui.css');
    }
}
