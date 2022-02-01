<?php

namespace Arsoft\Module\Commands;

use Illuminate\Console\Command;

class initModuleBackendCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'armodule:init-backend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inisialisasi Modul Backend';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = app_path('ModuleBackend');

        // validate directory
        if (file_exists($path)) {
            $this->info('Direktori ModuleBackend sudah ada. Gagal melakukan inisialisasi');
            return false;
        }

        // make parent directory
        mkdir($path);
        // copy module service-provider
        copy(
            __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR . 'Backend' . DIRECTORY_SEPARATOR . 'moduleBackendServiceProvider.php', 
            $path . DIRECTORY_SEPARATOR . 'moduleBackendServiceProvider.php'
        );

        // check and init helpers directory
        $helperPath = app_path('Helpers');
        if (!is_dir($helperPath)) {
            mkdir($helperPath);
        }

        // copy response formatter helper
        $stubHelperPath = __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR . 'Backend' . DIRECTORY_SEPARATOR . 'Helpers' . DIRECTORY_SEPARATOR;
        copy(
            $stubHelperPath . 'ResponseFormatter.php',
            $helperPath . DIRECTORY_SEPARATOR . 'ResponseFormatter.php'
        );

        // copy response logger helper
        $stubHelperPath = __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR . 'Backend' . DIRECTORY_SEPARATOR . 'Helpers' . DIRECTORY_SEPARATOR;
        copy(
            $stubHelperPath . 'LoggerHelper.php',
            $helperPath . DIRECTORY_SEPARATOR . 'LoggerHelper.php'
        );

        $this->info('Modul backend berhasil diinisialisasi');
        $this->info('Selanjutnya Tambahkan \'App\ModuleBackend\moduleBackendServiceProvider::class\' pada file config/app.php');
        return false;
    }
}
