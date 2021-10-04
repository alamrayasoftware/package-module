<?php

namespace Arsoft\Module\Commands;

use Illuminate\Console\Command;

class makeModuleBackendCommand extends Command
{
    protected $signature = 'armodule:make-module-backend {name : The name of the class}';
    protected $name = 'armodule:make-module-backend';
    protected $description = 'Membuat Module Untuk Project Arsoft';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $path = app_path('ModuleBackend');

        // validate module initialized
        if (!file_exists($path)) {
            $this->info(" Modul belum terinisialisasi");
            $this->info(" Gunakan perintah \"php artisan armodule:init\" untuk melakukan inisialisasi");
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

        // validate is module exist
        $parentName = ucfirst($arguments[0]);
        $childName = ucfirst($arguments[1]);
        if (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR . $parentName)) {
            $this->info('Modul ' . $parentName . ' tidak ditemukan');
            return false;
        } elseif (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR . $parentName . DIRECTORY_SEPARATOR . $childName)) {
            $this->info('Modul ' . $parentName . DIRECTORY_SEPARATOR . $childName . ' tidak ditemukan');
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
                    $this->info('\"' . $fullPath . '\" sudah digunakan, gunakan struktur modul yang berbeda');
                    return false;
                }
            }
            $pathCreated .= DIRECTORY_SEPARATOR;
        }
        $pathCreated = rtrim($pathCreated, DIRECTORY_SEPARATOR);

        $this->info('Inisialisasi modul ' . $pathCreated . "\r\n");

        // stub path
        $stubPath = __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR . $pathCreated . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        // module path
        $modulePath = $path . DIRECTORY_SEPARATOR . $pathCreated . DIRECTORY_SEPARATOR;

        // copy route-service-provider
        if (!is_dir($modulePath . DIRECTORY_SEPARATOR . 'Providers')) {
            mkdir($modulePath . DIRECTORY_SEPARATOR . 'Providers');
        }
        copy(
            $stubPath . 'Providers' . DIRECTORY_SEPARATOR . 'routeServiceProvider.stub',
            $modulePath . 'Providers' . DIRECTORY_SEPARATOR . 'routeServiceProvider.php'
        );
        $this->info('service-providers copied ' . $pathCreated . "\r\n");
        // copy route-api
        if (!is_dir($modulePath . DIRECTORY_SEPARATOR . 'Routes')) {
            mkdir($modulePath . DIRECTORY_SEPARATOR . 'Routes');
        }
        copy(
            $stubPath . 'Routes' . DIRECTORY_SEPARATOR . 'api.stub',
            $modulePath . 'Routes' . DIRECTORY_SEPARATOR . 'api.php'
        );
        $this->info('routes copied ' . $pathCreated . "\r\n");

        // copy controllers
        if (!is_dir($modulePath . DIRECTORY_SEPARATOR . 'Controllers')) {
            mkdir($modulePath . DIRECTORY_SEPARATOR . 'Controllers');
        }
        $controllerName = $childName . 'Controller';
        copy(
            $stubPath . 'Controllers' . DIRECTORY_SEPARATOR . $controllerName . '.stub',
            $modulePath . 'Controllers' . DIRECTORY_SEPARATOR . $controllerName . '.php'
        );
        $this->info('controllers copied ' . $pathCreated . "\r\n");

        // copy models
        if (!is_dir($modulePath . DIRECTORY_SEPARATOR . 'Models')) {
            mkdir($modulePath . DIRECTORY_SEPARATOR . 'Models');
        }
        $modelStubPath = $stubPath . 'Models';
        if (is_dir($modelStubPath)) {
            $modelDirectory = opendir($modelStubPath);
            while (($file = readdir($modelDirectory)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $modelName = str_replace('.stub', '.php', $file);
                copy(
                    $modelStubPath . DIRECTORY_SEPARATOR . $file,
                    $modulePath . 'Models' . DIRECTORY_SEPARATOR . $modelName
                );
            }
            closedir($modelDirectory);
        }
        $this->info('models copied ' . $pathCreated . "\r\n");

        $this->info("\nModul berhasil dibuat => url => " . $pathCreated . "\n");
    }
}
