<?php

namespace Arsoft\Module\Commands;

use Arsoft\Module\config;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class makeCommand extends Command
{
    protected $signature = 'armodule:make-module {name : The name of the class}';
    protected $name = 'armodule:make-module';
    protected $description = 'Membuat Module Untuk Project Arsoft';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $path = app_path('Modules');

        // validate module initialized
        if (!file_exists($path)) {
            $this->info(" Modul belum terinisialisasi");
            $this->info(" Gunakan perintah \"php artisan armodule:init\" untuk melakukan inisialisasi");
            return 0;
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
        if (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . $parentName)) {
            $this->info('Modul ' . $parentName . ' tidak ditemukan');
            return false;
        } elseif (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . $parentName . DIRECTORY_SEPARATOR . $childName)) {
            $this->info('Modul ' . $parentName . DIRECTORY_SEPARATOR . $childName . ' tidak ditemukan');
            return false;
        }

        // inisialisasi path
        $pathCreated = '';
        foreach ($arguments as $key => $argument) {
            $pathCreated .= ucfirst($argument . DIRECTORY_SEPARATOR);
            $pathCreated = rtrim($pathCreated, DIRECTORY_SEPARATOR);
            $fullPath = $path . DIRECTORY_SEPARATOR . $pathCreated;

            if (!file_exists($fullPath)) {
                mkdir($fullPath);
            } else {
                if (is_dir($fullPath . '/Providers')) {
                    $this->info('\"' . $fullPath . '\" sudah digunakan, gunakan struktur modul yang berbeda');
                    return false;
                }
            }
        }

        $this->info('Inisialisasi modul ' . $pathCreated . "\r\n");

        // stub path
        $stubPath = __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . $pathCreated . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
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
        // copy route-api
        if (!is_dir($modulePath . DIRECTORY_SEPARATOR . 'Routes')) {
            mkdir($modulePath . DIRECTORY_SEPARATOR . 'Routes');
        }
        copy(
            $stubPath . 'Routes' . DIRECTORY_SEPARATOR . 'api.stub',
            $modulePath . 'Routes' . DIRECTORY_SEPARATOR . 'api.php'
        );

        // copy controllers
        if (!is_dir($modulePath . DIRECTORY_SEPARATOR . 'Controllers')) {
            mkdir($modulePath . DIRECTORY_SEPARATOR . 'Controllers');
        }
        $controllerName = $childName . 'Controller';
        copy(
            $stubPath . 'Controllers' . DIRECTORY_SEPARATOR . $controllerName . '.stub',
            $modulePath . 'Controllers' . DIRECTORY_SEPARATOR . $controllerName . '.php'
        );

        // copy models
        if (!is_dir($modulePath . DIRECTORY_SEPARATOR . 'Models')) {
            mkdir($modulePath . DIRECTORY_SEPARATOR . 'Models');
        }
        $modelStubPath = $stubPath . 'Models';
        if (is_dir($modelStubPath)) {
            $modelDirectory = opendir($modelStubPath);
            while (($file = readdir($modelDirectory)) !== false) {
                $modelName = str_replace('.stub', '.php', $file);
                copy(
                    $modelStubPath . DIRECTORY_SEPARATOR . $file,
                    $modulePath . 'Models' . DIRECTORY_SEPARATOR . $modelName
                );
            }
            closedir($modelDirectory);
        }

        $this->info("\nModul berhasil dibuat => url => " . $arguments . "\n");
    }
}
