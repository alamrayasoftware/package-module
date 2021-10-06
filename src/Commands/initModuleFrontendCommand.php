<?php

namespace Arsoft\Module\Commands;

use Illuminate\Console\Command;
use Arsoft\Module\config;

class initModuleFrontendCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'armodule:init-frontend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inisialisasi Modul Frontend';

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
        $path = app_path('ModuleFrontend');

        if (file_exists($path)) {
            $this->info('Folder ModuleFrontend sudah ada. Gagal melakukan inisialisasi');
            return false;
        }

        // make parent directory
        mkdir($path);
        // copy module service-provider
        copy(
            __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR . 'moduleFrontendServiceProvider.php',
            $path . DIRECTORY_SEPARATOR . 'moduleFrontendServiceProvider.php'
        );

        $this->info('Modul frontend berhasil diinisialisasi');
        $this->info('Selanjutnya Tambahkan \'App\ModuleFrontend\moduleFrontendServiceProvider::class\' pada file config/app.php');
        return false;
    }
}
