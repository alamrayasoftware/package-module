<?php

namespace Arsoft\Module\Commands;

use Illuminate\Console\Command;

class makeModuleFrontendCommand extends Command
{
    protected $signature = 'armodule:make-frontend {name : The name of the class}';
    protected $name = 'armodule:make-frontend';
    protected $description = 'Membuat modul frontend';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $path = app_path('ModuleFrontend');
        $nameSpace = 'App\ModuleFrontend';

        // validate module initialized
        if (!file_exists($path)) {
            $this->info(" Modul frontend belum terinisialisasi");
            $this->info(" Gunakan perintah \"php artisan armodule:init-frontend\" untuk melakukan inisialisasi");
            return false;
        }

        // validate duplicate module name
        $tempArgument = str_replace('/', DIRECTORY_SEPARATOR, $this->argument('name'));
        if (file_exists($path . DIRECTORY_SEPARATOR . $tempArgument)) {
            $this->info("Modul \"" . $this->argument('name') . "\" sudah ada, gunakan nama yang berbeda \n");
            return false;
        }

        $arguments = explode(DIRECTORY_SEPARATOR, $tempArgument);
        // validate argument
        if (count($arguments) != 2) {
            $this->info('Argumen kurang sesuai, gunakan format : ParentName/ChildName');
            return false;
        }

        // inisialisasi path
        $pathCreated = '';
        foreach ($arguments as $key => $argument) {
            $pathCreated .= ucfirst($argument);
            $fullPath = $path . DIRECTORY_SEPARATOR . $pathCreated;

            if (!file_exists($fullPath)) {
                mkdir($fullPath);
            } else {
                if (is_dir($fullPath . '/Providers')) {
                    $this->info('\"' . $fullPath . "\" sudah digunakan, gunakan struktur modul yang berbeda");
                    return false;
                }
            }
            $pathCreated .= DIRECTORY_SEPARATOR;
        }
        $pathCreated = rtrim($pathCreated, DIRECTORY_SEPARATOR);

        $this->info('Inisialisasi modul ' . $pathCreated . "\r\n");

        // stub path
        $stubPath = __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR;
        // module path
        $modulePath = $path . DIRECTORY_SEPARATOR . $pathCreated . DIRECTORY_SEPARATOR;

        // copy route-service-provider
        if (!is_dir($modulePath . 'Providers')) {
            mkdir($modulePath . 'Providers');
        }
        $moduleRouteServiceProviderPath = $modulePath . 'Providers' . DIRECTORY_SEPARATOR . 'routeServiceProvider.php';
        copy(
            $stubPath . 'routeServiceProvider.php',
            $moduleRouteServiceProviderPath
        );
        $tempContent = file_get_contents($moduleRouteServiceProviderPath);
        $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
        $tempPath = "app_path('ModuleFrontend" . DIRECTORY_SEPARATOR . $pathCreated . DIRECTORY_SEPARATOR . "Routes" . DIRECTORY_SEPARATOR . "web.php')";
        $tempContent = str_replace('__defaultModulePath__', $tempPath, $tempContent);
        file_put_contents($moduleRouteServiceProviderPath, $tempContent);
        $this->info('route-service-providers copied ' . $pathCreated . "\n");

        // copy route-web
        if (!is_dir($modulePath . 'Routes')) {
            mkdir($modulePath . 'Routes');
        }
        $moduleRoutePath = $modulePath . 'Routes' . DIRECTORY_SEPARATOR . 'web.php';
        copy(
            $stubPath . 'web.php',
            $moduleRoutePath
        );
        $this->info('routes copied ' . $pathCreated . "\n");

        // copy index.blade
        $moduleBladePath = $modulePath . 'Views' . DIRECTORY_SEPARATOR . 'index.blade.php';
        copy(
            $stubPath . 'index.blade.php', 
            $moduleBladePath
        );
        $this->info('index.blade copied ' . $pathCreated . "\n");

        $this->info("\nModul berhasil dibuat => url => " . $pathCreated . "\n");
    }
}
