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
    }

    /**
     * Final configuration at the end of "composer create-project" command.
     */
    static public function finish()
    {
        Installation :: instance(['directory' => __DIR__.'/..']);

        self :: configureDirectory();
        self :: generateSecurityToken();
        self :: changeAutoloaderString('/index.php');

        self :: displaySuccessMessage('Now please fill database settings for MySQL in .env file and run "composer database" in your project directory.');
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

        self :: moveCoreFoldersFromVendor(['adminpanel', 'core', 'extra', 'log', 'userfiles']);
        self :: displayFinalInstallationMessage();
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

    static public function displayFinalInstallationMessage()
    {
        Installation :: instance(['directory' => __DIR__.'/..']);
        $env = parse_ini_file(self :: $instance['directory'].DIRECTORY_SEPARATOR.'.env');

        $message = "Installation complete, now you can open MV tracker in browser.".PHP_EOL;
        $message .= " MV tracker start page http://yourdomain.com".preg_replace('/\/$/', '', $env['APP_FOLDER']).PHP_EOL;
        $message .= " Use the admin panel to manage users and statuses http://yourdomain.com".$env['APP_FOLDER']."adminpanel";

        self :: displayDoneMessage($message);
    }    
}