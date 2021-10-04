<?php

namespace App\ModuleBackend;

use Illuminate\Support\ServiceProvider;

class moduleBackendServiceProvider extends ServiceProvider
{

    protected $path = '';

    /**
     * Will make sure that the required modules have been fully loaded
     * @return void
     */
    public function boot()
    {
        // For each of the registered modules, include their routes and Views
    }

    public function register()
    {

        $this->initiateProvider('ModuleBackend');
    }

    private function initiateProvider(String $path)
    {
        $filesystem = $this->app->make('files')->directories(app_path($path));

        foreach ($filesystem as $modules) {
            $moduleName = last(explode(DIRECTORY_SEPARATOR, $modules));

            if (is_dir(app_path() . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'Providers')) {
                $path = str_replace(DIRECTORY_SEPARATOR, '\\', $path);
                $this->app->register("App\\{$path}\\{$moduleName}\\Providers\\routeServiceProvider");
            } else {
                $this->initiateProvider($path . DIRECTORY_SEPARATOR . $moduleName);
            }
        }
    }
}
