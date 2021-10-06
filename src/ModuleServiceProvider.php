<?php

namespace Arsoft\Module;

use Illuminate\Support\ServiceProvider;
use Arsoft\Module\Commands\initCommand;
use Arsoft\Module\Commands\initModuleBackendCommand;
use Arsoft\Module\Commands\initModuleFrontendCommand;
use Arsoft\Module\Commands\makeCommand;
use Arsoft\Module\Commands\makeModuleBackendCommand;
use Arsoft\Module\Commands\makeModuleFrontendCommand;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // publish config file
        
            $this->commands([
                initModuleFrontendCommand::class,
                makeModuleFrontendCommand::class,
                initModuleBackendCommand::class,
                makeModuleBackendCommand::class,
            ]);
        }
    }
}
