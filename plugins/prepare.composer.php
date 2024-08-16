<?php
class PrepareComposer
{
    static public function prepareFilesystem()
    {
        $root_directory = realpath(__DIR__.'/..');
        $framework = realpath($root_directory.'/vendor/makscraft/mv-framework');
        
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