<?php

namespace Fomvasss\Blocks;

use Symfony\Component\Finder\Finder;
use Fomvasss\Blocks\BlockService;
use Fomvasss\Blocks\Contracts\BlockHandlerInterface;
use Illuminate\Support\Facades\Route;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/blocks.php' => config_path('blocks.php'),
        ]);

        if (! class_exists('CreateBlocksTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_blocks_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_blocks_table.php'),
            ], 'laravel-blocks-migrations');
        }

        $this->registerRoutes();

        $this->autoRegisterHandlers();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/blocks.php', 'blocks');

        $this->app->singleton(BlockService::class, function () {
            return new BlockService();
        });
    }

    protected function registerRoutes()
    {
        Route::namespace('Fomvasss\Blocks\Http\Controllers')
            ->as('blocks.')
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
            });
    }

    /**
     * Register handlers dynamic blocks.
     * 
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function autoRegisterHandlers(): void
    {
        $paths = [
            __DIR__ . '/Handlers',
            app_path('Blocks'),
        ];

        $namespaceMap = [
            __DIR__ . '/Handlers' => 'Fomvasss\\Blocks\\Handlers',
            app_path('Blocks') => 'App\\Blocks',
        ];

        $blockService = $this->app->make(BlockService::class);

        foreach ($paths as $path) {
            
            if (!is_dir($path)) continue;
        
            $finder = new Finder();
            $finder->files()->in($path)->name('*.php');
          
            foreach ($finder as $file) {
                $relativePath = str_replace([$path . '/', '.php'], '', $file->getRelativePathname());
                $class = $namespaceMap[$path] . '\\' . str_replace('/', '\\', $relativePath);

                if (class_exists($class) && in_array(BlockHandlerInterface::class, class_implements($class))) {
                    $blockService->register($class::getType(), $class);
                }
            }
        }
    }
}
