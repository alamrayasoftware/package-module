<?php

namespace Arsoft\Module\Commands;

use Illuminate\Console\Command;

class makeModuleBackendCommand extends Command
{
    protected $signature = 'armodule:make-backend {name : The name of the class}';
    protected $name = 'armodule:make-backend';
    protected $description = 'Membuat modul backend';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $path = app_path('ModuleBackend');
        $nameSpace = 'App\ModuleBackend';

        // validate module initialized
        if (!file_exists($path)) {
            $this->info(" Modul backend belum terinisialisasi");
            $this->info(" Gunakan perintah \"php artisan armodule:init-backend\" untuk melakukan inisialisasi");
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
        // namespace
        $nameSpace = $nameSpace . DIRECTORY_SEPARATOR . $pathCreated . DIRECTORY_SEPARATOR;

        // copy route-service-provider
        if (!is_dir($modulePath . 'Providers')) {
            mkdir($modulePath . 'Providers');
        }
        $moduleRouteServiceProviderPath = $modulePath . 'Providers' . DIRECTORY_SEPARATOR . 'routeServiceProvider.php';
        copy(
            $stubPath . 'Providers' . DIRECTORY_SEPARATOR . 'routeServiceProvider.stub',
            $moduleRouteServiceProviderPath
        );
        $tempContent = file_get_contents($moduleRouteServiceProviderPath);
        $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
        $tempPath = "app_path('ModuleBackend" . DIRECTORY_SEPARATOR . $pathCreated . DIRECTORY_SEPARATOR . "Routes" . DIRECTORY_SEPARATOR . "api.php')";
        $tempContent = str_replace('__defaultModulePath__', $tempPath, $tempContent);
        file_put_contents($moduleRouteServiceProviderPath, $tempContent);
        $this->info('service-providers copied ' . $pathCreated . "\r\n");

        // copy route-api
        if (!is_dir($modulePath . 'Routes')) {
            mkdir($modulePath . 'Routes');
        }
        $moduleRoutePath = $modulePath . 'Routes' . DIRECTORY_SEPARATOR . 'api.php';
        copy(
            $stubPath . 'Routes' . DIRECTORY_SEPARATOR . 'api.stub',
            $moduleRoutePath
        );
        $tempContent = file_get_contents($moduleRoutePath);
        $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
        file_put_contents($moduleRoutePath, $tempContent);
        $this->info('routes copied ' . $pathCreated . "\r\n");

        // copy controllers
        if (!is_dir($modulePath . 'Controllers')) {
            mkdir($modulePath . 'Controllers');
        }
        $controllerStubPath = $stubPath . 'Controllers';
        if (is_dir($controllerStubPath)) {
            $controllerDirectory = opendir($controllerStubPath);
            while (($file = readdir($controllerDirectory)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $controllerName = str_replace('.stub', '.php', $file);
                $moduleControllerPath = $modulePath . 'Controllers' . DIRECTORY_SEPARATOR . $controllerName;
                copy(
                    $controllerStubPath . DIRECTORY_SEPARATOR . $file,
                    $moduleControllerPath
                );
                $tempContent = file_get_contents($moduleControllerPath);
                $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
                file_put_contents($moduleControllerPath, $tempContent);
            }
            closedir($controllerDirectory);
        }
        $this->info('controllers copied ' . $pathCreated . "\r\n");

        // copy models
        if (!is_dir($modulePath . 'Models')) {
            mkdir($modulePath . 'Models');
        }
        $modelStubPath = $stubPath . 'Models';
        if (is_dir($modelStubPath)) {
            $modelDirectory = opendir($modelStubPath);
            while (($file = readdir($modelDirectory)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $modelName = str_replace('.stub', '.php', $file);
                $moduleModelPath = $modulePath . 'Models' . DIRECTORY_SEPARATOR . $modelName;
                copy(
                    $modelStubPath . DIRECTORY_SEPARATOR . $file,
                    $moduleModelPath
                );
                $tempContent = file_get_contents($moduleModelPath);
                $tempContent = str_replace('__defaultNamespace__', str_replace(DIRECTORY_SEPARATOR, '\\', $nameSpace), $tempContent);
                file_put_contents($moduleModelPath, $tempContent);
            }
            closedir($modelDirectory);
        }
        $this->info('models copied ' . $pathCreated . "\r\n");

        $this->info("\nModul berhasil dibuat => url => " . $pathCreated . "\n");
    }
}
