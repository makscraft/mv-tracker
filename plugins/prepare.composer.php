<?php
class PrepareComposer
{
    static public function prepareFilesystem()
    {
        $root_directory = realpath(__DIR__.'/..');
        $framework = realpath($root_directory.'/vendor/makscraft/mv-framework');

        if(file_exists($root_directory.'/core/.gitkeep'))
            unlink($root_directory.'/core/.gitkeep');

        if(file_exists($root_directory.'/core') && !file_exists($root_directory.'/core/builder.class.php'))
            rmdir($root_directory.'/core');

        $files = [
            '/config/autoload.php',
            '/adminpanel',
            '/core',
            '/userfiles',
            '/extra'
        ];

        foreach($files as $one)
        {
            if(file_exists($framework.$one) && !file_exists($root_directory.$one))
            {
                rename($framework.$one, $root_directory.$one);
                echo " Moving item ".$one.PHP_EOL;
            }
        }

        file_put_contents($framework.'/config/autoload.php', "<?php\r\n");

        if(!is_dir($framework.'/core'))
            mkdir($framework.'/core');
        
        $dump_old = $root_directory.'/userfiles/database/mysql-dump.sql';
        $dump_new = $root_directory.'/customs/initial-dump.sql';

        if(file_exists($dump_old) && file_exists($dump_new))
            unlink($dump_old);

        if(file_exists($dump_new))
        {
            rename($dump_new, $dump_old);
            echo " Moving database dump".PHP_EOL;
        }

        if(file_exists($root_directory.'/userfiles/database/sqlite/database.sqlite'))
            unlink($root_directory.'/userfiles/database/sqlite/database.sqlite');        
    }
}