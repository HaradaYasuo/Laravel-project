<?php namespace Spatie\MediaLibrary;

use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\Helpers\GlideImageManipulator;
use Spatie\MediaLibrary\Helpers\LocalFileSystem;
use Spatie\MediaLibrary\Interfaces\FileSystemInterface;
use Spatie\MediaLibrary\Interfaces\ImageManipulatorInterface;
use Spatie\MediaLibrary\Repositories\MediaLibraryRepository;

class MediaLibraryServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        // Publish the config file
        $this->publishes([
            __DIR__ . '/config/laravel-medialibrary.php' => config_path('laravel-medialibrary.php')
        ], 'config');

        // Publish the migration
        $timestamp = date('Y_m_d_His', time());

        $this->publishes([
            __DIR__ . '/migrations/create_media_table.php' => base_path('database/migrations/'.$timestamp.'_create_media_table.php')
        ], 'migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('mediaLibrary', MediaLibraryRepository::class);
        $this->app->bind(FileSystemInterface::class, LocalFileSystem::class);
        $this->app->bind(ImageManipulatorInterface::class, GlideImageManipulator::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {

    }
}
