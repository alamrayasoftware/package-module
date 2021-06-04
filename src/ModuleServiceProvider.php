<?php

namespace Arsoft\Module;

use Illuminate\Support\ServiceProvider;
use Arsoft\Module\Commands\initCommand;
use Arsoft\Module\Commands\testCommand;
use Arsoft\Module\Commands\makeCommand;

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
                initCommand::class,
                makeCommand::class,
            ]);
        }
    }
}
