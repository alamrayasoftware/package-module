<?php

namespace Arsoft\Module\Commands;

use Arsoft\Module\config;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class makeCommand extends Command
{   
    protected $signature    = 'armodule:make {name : The name of the class}';
    protected $name         = 'armodule:make';
    protected $description  = 'Membuat Module Untuk Project Arsoft';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $path       = app_path().'\Modules';
        $str        = file_get_contents(__DIR__.'/Stubs/moduleServiceProvider.stub');
        $arguments   = explode('/', $this->argument('name'));

        if(!file_exists($path)){
            $this->info(" Modul Belum Terinisialisasi");
            $this->info(" Gunakan perintah \"php artisan armodule:init\" untuk melakukan inisialisasi.");
            return 0;
        }

        if(file_exists($path.'\\'.$this->argument('name'))){
            $this->info("Modul \"".$this->argument('name')."\" Sudah Ada.. \n");
            return false;
        }

        // inisialisasi path
            $pathCreated = '';
            foreach($arguments as $key => $argument){
                $pathCreated .= ucfirst($argument.'\\');
                $trimmer = rtrim($pathCreated, '\\ ');

                if(!file_exists($path.'\\'.$trimmer)){
                    mkdir($path.'/'.str_replace('\\', '/', $trimmer));
                }else{
                    if(is_dir(str_replace('\\', '/', $path.'\\'.$trimmer).'/Providers')){
                        $this->info('"'.$pathCreated."\" Adalah Modul Aktif, Tidak Bisa Dijadikan Parrent Module, Coba Pilih Folder Lain \r\n");

                        return false;
                    }
                }

            }

            $pathCreated = rtrim($pathCreated, '\\ ');
            
            $this->info('Inisialisasi Modul '.$pathCreated."\r\n");

        // Copy Files
            foreach(config::getModulStructure() as $key => $parrent){
                $this->info('Generating "'.$parrent.'" -> Module/'.str_replace('\\', '/', $pathCreated).'/'.$parrent.' Complete ...');
                mkdir($path.'/'.str_replace('\\', '/', $pathCreated).'/'.$parrent);
            }

            // routeService Provider
                copy(__DIR__.'/Stubs/routeServiceProvider.stub', $path.'/'.str_replace('\\', '/', $pathCreated).'/Providers/routeServiceProvider.php');
                $str = file_get_contents($path.'/'.str_replace('\\', '/', $pathCreated).'/Providers/routeServiceProvider.php');
                $str = str_replace('__defaultNamespace__', $pathCreated, $str);
                $str = str_replace('__defaultPattern__', str_replace('\\', '/', $pathCreated), $str);
                file_put_contents($path.'/'.str_replace('\\', '/', $pathCreated).'/Providers/routeServiceProvider.php', $str);

            // Route Web
                copy(__DIR__.'/Stubs/web.stub', $path.'/'.str_replace('\\', '/', $pathCreated).'/Routes/web.php');
                $str = file_get_contents($path.'/'.str_replace('\\', '/', $pathCreated).'/Routes/web.php');
                $str = str_replace('__defaultName__', ucfirst($arguments[count($arguments) - 1]), $str);
                $str = str_replace('__defaultUrl__', strtolower(str_replace('\\', '/', $pathCreated)), $str);
                $str = str_replace('__defaultGroup__', strtolower($arguments[count($arguments) - 1]), $str);
                file_put_contents($path.'/'.str_replace('\\', '/', $pathCreated).'/Routes/web.php', $str);

            // route api
                copy(__DIR__.'/Stubs/api.stub', $path.'/'.str_replace('\\', '/', $pathCreated).'/Routes/api.php');

            // index blade
                copy(__DIR__.'/Stubs/index.stub', $path.'/'.str_replace('\\', '/', $pathCreated).'/Views/index.blade.php');
                $str = file_get_contents($path.'/'.str_replace('\\', '/', $pathCreated).'/Views/index.blade.php');
                $str = str_replace('__defaultContent__', str_replace('\\', '/', $pathCreated), $str);
        
                file_put_contents($path.'/'.str_replace('\\', '/', $pathCreated).'/Views/index.blade.php', $str);

            // controller
                $conName = lcfirst($arguments[count($arguments) - 1]).'Controller';

                copy(__DIR__.'/Stubs/controller.stub', $path.'/'.str_replace('\\', '/', $pathCreated).'/Controllers/'.$conName.'.php');
                $str = file_get_contents($path.'/'.str_replace('\\', '/', $pathCreated).'/Controllers/'.$conName.'.php');
                $str = str_replace('__defaultGroup__', $pathCreated, $str);
                $str = str_replace('__defaultClass__', $conName, $str);

                file_put_contents($path.'/'.str_replace('\\', '/', $pathCreated).'/Controllers/'.$conName.'.php', $str);

            // model
                copy(__DIR__.'/Stubs/model.stub', $path.'/'.str_replace('\\', '/', $pathCreated).'/Models/'.$argument[1].'.php');
                $str = file_get_contents($path.'/'.str_replace('\\', '/', $pathCreated).'/Models/'.$argument[1].'.php');
                $str = str_replace('__defaultGroup__', $pathCreated, $str);
                $str = str_replace('__defaultClass__', $arguments[count($arguments) - 1], $str);
        
                file_put_contents($path.'/'.str_replace('\\', '/', $pathCreated).'/Models/'.$argument[1].'.php', $str);
        

            $this->info("\nModul Berhasil Dibuat => url => ".$this->argument('name')."\n");


    }
}