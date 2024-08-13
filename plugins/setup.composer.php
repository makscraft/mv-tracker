<?php
use Composer\Script\Event;

class SetupComposer extends Installation
{
    static public $version = '1.2';

    /**
     * Post "composer dump-autoload" event.
     */
    static public function postAutoloadDump(Event $event)
    {
        Installation :: instance(['directory' => __DIR__.'/..']);

        //self :: removeDirectory($base_to.$folder);
        self :: moveCoreFoldersFromVendor(['adminpanel', 'extra', 'log', 'userfiles']);
        // self :: configureDirectory();
        // self :: generateSecurityToken();
        
        

        // self :: displaySuccessMessage('Now please fill database settings for MySQL in .env file and run "composer database" in your project directory.');
        // self :: configureDatabaseMysql();
        

        //self :: setEnvFileParameter('APP_ENV', 'development');


        //self :: displayDoneMessage('111');

        //$accounts = new Accounts();
        // $registry = Registry :: instance();
        // $installation = new Installation();
        // //Installation :: displayDoneMessage('hello!');
        // //Installation :: generateSecurityToken();
        // $from = realpath(__DIR__.'/../vendor/makscraft/mv-framework/adminpanel');
        // $to = realpath(__DIR__.'/..').'/admin';

        //rename($from, $to);
        //echo $to;
    }

    /**
     * Final configuration at the end of "composer create-project" command.
     */
    static public function finish()
    {
        Installation :: instance(['directory' => __DIR__.'/..']);
        self :: generateSecurityToken();
        self :: moveCoreFoldersFromVendor(['adminpanel', 'extra', 'log', 'userfiles']);
    }

    static public function moveCoreFoldersFromVendor(mixed $folders = [])
    {
        $folders = is_array($folders) ? $folders : [$folders];
        $base_from = self :: $instance['directory'].'/vendor/makscraft/mv-framework/';
        $base_to = self :: $instance['directory'].'/';

        foreach($folders as $folder)
            if(file_exists($base_from.$folder) || !file_exists($base_to.$folder))
                rename($base_from.$folder, $base_to.$folder);
    }

    static public function configureDatabaseMysql()
    {
        Installation :: instance(['directory' => __DIR__.'/..']);

        parent :: configureDatabaseMysql();
        self :: setFirstUserLogin(self :: runPdo());
    }

    static public function setFirstUserLogin(PDO $pdo)
    {
        if(!isset(self :: $instance['login'], self :: $instance['password']))
        {
            self :: displayErrorMessage('User login and password are not found.');
            return;
        }

        self :: boot();

        $accounts = new Accounts();

        if($accounts -> countRecords() > 1)
        {
            self :: displaySuccessMessage('First user has been already created.');
            return;
        }

        $user = $accounts -> findRecordOrGetEmpty(['id' => 1]);
        $user -> login = self :: $instance['login'];

        $salt = Registry :: get('APP_TOKEN');
        $user -> password = Service :: makeHash(self :: $instance['password'].$salt);
        $user -> date_registration = I18n :: getCurrentDateTime('SQL');
        $user -> autologin_key = Service :: strongRandomString(50);
        $user -> active = 1;
        $user -> send_emails = true;

        $user -> save();

        self :: displaySuccessMessage('First user of MV tracker been successfully created.');
    }
}