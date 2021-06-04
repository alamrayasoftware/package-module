<?php

namespace Arsoft\Module\Commands;

use Illuminate\Console\Command;
use Arsoft\Module\config;
use Illuminate\Support\Facades\Artisan;

class initCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'armodule:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inisialisasi Modul Arsoft';

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
        $path = app_path('Modules');

        if(file_exists($path)){
            $this->info('Folder modules sudah ada. Gagal melakukan inisialisasi..!');
            return 0;
        }

        if(mkdir($path)){
            foreach(config::getParrentModules() as $key => $parrent){
                mkdir($path.'/'.$parrent);
            }
        }

        copy(__DIR__.'/Stubs/moduleServiceProvider.stub', $path.'/moduleServiceProvider.php');
        
        $this->info('Arsoft Modul Berhasil Di inisialisasi..');
        $this->info('Selanjutnya Tambahkan \'App\Modules\moduleServiceProvider::class\' pada file config/app.php');
        return 0;
    }
}
