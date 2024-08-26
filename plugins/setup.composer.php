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

        $driver = in_array('sqlite', PDO :: getAvailableDrivers()) ? 'sqlite' : 'mysql';
        self :: setEnvFileParameter('DATABASE_ENGINE', $driver);

        if($driver === 'sqlite')
        {
            self :: configureDatabaseSQLite();
            self :: findAndExecuteAllAvailableMigartions();
            self :: insertInitionDatabaseContent('en');

            $message = 'If you want to use MySQL database instead of SQLite, ';
            $message .= 'please fill database settings for MySQL in .env file and run "composer database"';
            $message .= ' in your project directory.';

            echo ' - '.$message.PHP_EOL;
        }
        else
        {
            $message = ' - ;Now please fill database settings for MySQL in .env file';
            $message .= ' and run "composer database" in your project directory.';

            self :: displaySuccessMessage($message);
        }            
    }

    static public function commandConfigureDatabase(Event $event)
    {
        Installation :: instance([
            'directory' => __DIR__.'/..',
            'package' => 'tracker'
        ]);

        parent :: commandConfigureDatabase($event);
        self :: findAndExecuteAllAvailableMigartions();

        self :: setFirstUserLogin(self :: runPdo());
        self :: insertInitionDatabaseContent('en');
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
            self :: displaySuccessMessage(' - First user has been already created.');
            return;
        }

        $user = $accounts -> findRecordOrGetEmpty(['id' => 1]);

        $user -> login = self :: $instance['login'];
        $user -> name = 'Root';
        $user -> password = Service :: makeHash(self :: $instance['password'].Registry :: get('APP_TOKEN'));
        $user -> date_registration = I18n :: getCurrentDateTime('SQL');
        $user -> autologin_key = Service :: strongRandomString(50);
        $user -> active = 1;
        $user -> send_emails = true;

        $user -> save();

        self :: displaySuccessMessage(' - First user of MV tracker been successfully created.');
    }

    static public function commandRegion(Event $event)
    {
        Installation :: instance([
            'directory' => __DIR__.'/..',
            'package' => 'tracker'
        ]);

        self :: boot();
        $region = parent :: commandRegion($event);

        $env = parse_ini_file(self :: $instance['directory'].'/.env');
        $env_region = $env['APP_REGION'] ?? '';
        $projects = Database :: instance() -> getCount('projects');
        $tasks = Database :: instance() -> getCount('tasks');
        $logs = Database :: instance() -> getCount('log');

        if($env_region !== '' || $projects > 1 || $tasks > 2 || $logs > 0)
        {
            $message = "Attention! Changing of the region will cause overwriting the database content of ";
            $message .= "tables 'projects', 'tasks', 'trackers', 'priorities' and 'statuses'.";

            self :: displayErrorMessage($message);

            $message = "Do you want to proceed? [yes / no]";

            $answer = self :: typePromptWithCoices($message, ['yes', 'y', 'no', 'n', '']);
            
            if($answer !== 'yes' && $answer !== 'y')
                return;
        }

        self :: setEnvFileParameter('APP_REGION', $region);
        self :: displaySuccessMessage(' - .env file has been configurated.');

        $region_initial = $region;
        self :: insertInitionDatabaseContent($region);

        $message = 'Region settings from the "'.$region_initial.'" package have been installed.';

        if(isset($data['hello']) && $data['hello'] !== '')
            $message .= PHP_EOL.' '.$data['hello'];
    
        self :: displayDoneMessage($message);
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