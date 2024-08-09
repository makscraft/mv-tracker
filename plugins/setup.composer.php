<?php
class Setup
{
    static public $version = '1.2';

    /**
     * Post "composer dump-autoload" event.
     */
    static public function postAutoloadDump()
    {
        
    }

    /**
     * Final configuration at the end of "composer create-project" command.
     */
    static public function finish()
    {

    }
}
    /*
    
    private $root_path = '';
    
    private $errors = [];
    
    private $setup_data = [
        'DbHost' => '',
        'DbUser' => '',
        'DbPassword' => '',
        'DbName' => '',
        'TimeZone' => '',
        'Region' => '',
        'DomainName' => '',
        'MainPath' => '',
        'SecretCode' => '',
        'EmailFrom' => '',
    ];
    
    public function defineRootPath()
    {
        $this -> root_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])).'/';
        
        return $this;
    }
    
    public function defineCurrentStep()
    {
        $step = 1;
        
        if(isset($_SESSION['installation']['step']) && intval($_SESSION['installation']['step']) <= 3)
            $step = intval($_SESSION['installation']['step']);
        else
            $_SESSION['installation']['step'] = 1;
        
        return $step;
    }
    
    public function createToken()
    {
        return md5($_SERVER['HTTP_USER_AGENT'].$this -> defineCurrentStep().$_SERVER['HTTP_HOST']);
    }
    
   static public function checkInMainIndexFile()
   {
       $path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])).'/';
       
       if(is_file($path.'install.php') && strpos($path, '/mv/tracker/') === false)
       {
            $base = dirname($_SERVER['PHP_SELF']);
            $base = ($base == '/') ? $base : $base.'/';
            
            header('Location: '.$base.'install.php');
            exit();
       }
   }
   
   static public function checkFinalInstallRemove(Builder $mv)
   {
       if($mv -> router -> getUrlPart(0) == 'install.php')
           if(!is_file($mv -> include_path.'install.php'))
               $mv -> redirect('home');
   }
    
    //Forms processing
    
    public function validateFormData(&$fields)
    {        
        foreach($fields as $name => $value)
            if(isset($_POST[$name]))
            {
                $value = str_replace(["'", '"'], '', trim($_POST[$name]));
                $fields[$name] = htmlspecialchars($value, ENT_QUOTES);
            }
        
        if($this -> defineCurrentStep() == 1)
            $captions = ['db_host' => 'Host', 'db_login' => 'Login', 'db_password' => 'Password', 
                         'db_name' => 'Database name'];
        else
            $captions = ['user_name' => 'Name', 'user_email' => 'Email', 'user_login' => 'Login',
                         'user_password' => 'Password'];
            
        foreach($fields as $name => $value)
        {
            if($value == '' && $name != 'db_password')
            {
                $this -> addError('Field &laquo;'.$captions[$name].'&raquo; is required.');
                continue;
            }
        }
        
        if(isset($fields['db_host']) && !count($this -> errors))
        {
            try
            {
                $pdo = $this -> runPdoConnection($fields['db_host'], $fields['db_login'], $fields['db_password'], 
                                                 $fields['db_name']);
            }
            catch(PDOException $error)
            {
                $this -> addError($error -> getMessage());
            }
        }
        
        if(isset($fields['user_name']))
        {
            if($fields['user_email'])
            {
                $re = '/^[-a-z0-9_\.]+@[-a-z0-9_\.]+\.[a-z]{2,5}$/i';
                
                if(!preg_match($re, $fields['user_email']) || strpos($fields['user_email'], '..') !== false)
                    $this -> addError('Field &laquo;Email&raquo; must contain valid address.');
            }
            
            if($fields['user_login'] && strlen($fields['user_login']) < 2)
                $this -> addError('Field &laquo;Login&raquo; must contain at least 2 symbols.');
            
            if($fields['user_password'])
            {
                if(strlen($fields['user_password']) < 6)
                    $this -> addError('Field &laquo;Password&raquo; must contain at least 6 symbols.');
                else if(!preg_match('/\D/iu', $fields['user_password']))
                    $this -> addError('Field &laquo;Password&raquo; must contain letters.');
                else if(!preg_match('/\d/', $fields['user_password']))
                    $this -> addError('Field &laquo;Password&raquo; must contain digits.');
            }
        }
        
        if(!isset($_POST['token']) || $_POST['token'] != $this -> createToken())
            $this -> addError('Wrong security token. Please try to submit the form one more time.');
            
        return $fields;
    }
    
    public function addError($error)
    {
        $this -> errors[] = $error;
    }
    
    public function displayErrors()
    {
        if(!count($this -> errors))
            return;
        
        $html = "<div class=\"errors\">\n";
        
        foreach($this -> errors as $error)
            $html .= "<p>".$error."</p>\n";
        
        return $html."</div>\n";
    }
    
    public function formIsValid()
    {
        return !(bool) count($this -> errors);
    }
    
    public function checkInitialConditions()
    {
        if(version_compare(PHP_VERSION, '7.0.0', '<'))
            $this -> errors[] = 'PHP 7.0 or higher is required.';
        
        if(!extension_loaded('pdo_mysql'))
            $this -> errors[] = 'PHP extension pdo_mysql is required.';
        
        if(function_exists('apache_get_modules'))
            if(!in_array('mod_rewrite', apache_get_modules()))
                $this -> errors[] = 'Apache module mod_rewrite is required.';
            
        if(!is_writable('.'))
            $this -> errors[] = 'Please make the current directory writable.';
        
        if(!is_writable($this -> root_path.'config/setup.php'))
            $this -> errors[] = 'Please make file config/setup.php writable.';
        
        if(preg_replace('/\/?install\.php\??.*$/u', '', $_SERVER['REQUEST_URI']) != '')
            if(!is_writable('.htaccess'))
                $this -> errors[] = 'Please make file .htaccess writable.';
            
        if(!is_writable($this -> root_path.'userfiles/'))
            $this -> errors[] = 'Please make folder userfiles and all subfolders writable.';
        
        if(!is_writable($this -> root_path.'log/'))
            $this -> errors[] = 'Please make folder log writable.';
        
        return !(bool) count($this -> errors);
    }
    
    public function runPdoConnection($host, $login, $password, $database)
    {
        $pdo = new PDO("mysql:host=".$host.";dbname=".$database, $login, $password,
                   array(PDO :: MYSQL_ATTR_INIT_COMMAND => "SET NAMES \"UTF8\""));
        
        return $pdo;
    }
    
    public function reload()
    {
        header('Location: install.php');
        exit();
    }
    
    
    //Installation actions
    
    public function configIsCompleted()
    {
        $config = file_get_contents($this -> root_path.'config/setup.php');
        
        if(strpos($config, '---DbHost---') !== false || strpos($config, '---SecretCode---') !== false)
            return false;
        
        return true;
    }
    
    public function firstUserIsCompleted()
    {
        $accounts = new Accounts();
        $users = new Users();
        
        return ($accounts -> countRecords() && $users -> countRecords());
    }
    
    public function totallyCompleted($mv)
    {
        if(!$this -> configIsCompleted())
            return false;
        
        if(!is_object($mv))
            return false;
        
        if(!Database :: $adapter -> ifTableExists("accounts"))
            return false;
        
        if(!$this -> firstUserIsCompleted())
            return false;
        
        return true;
    }
    
    public function writeSetupData()
    {
        $this -> setup_data['DbHost'] = $_SESSION['installation']['db_host'];
        $this -> setup_data['DbUser'] = $_SESSION['installation']['db_login'];
        $this -> setup_data['DbPassword'] = $_SESSION['installation']['db_password'];
        $this -> setup_data['DbName'] = $_SESSION['installation']['db_name'];
        
        $timezone = @date_default_timezone_get();
        $this -> setup_data['TimeZone'] = $timezone ? $timezone : 'GMT';
        
        $timezones_us = DateTimeZone :: listIdentifiers(DateTimeZone :: PER_COUNTRY, 'US');
        $this -> setup_data['Region'] = in_array($timezone, $timezones_us) ? 'am' : 'en';
        
        $protocol = 'http://';
        
        if((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || $_SERVER['SERVER_PORT'] == 443)
            $protocol = 'https://';
        
        $this -> setup_data['DomainName'] = $protocol.$_SERVER['HTTP_HOST'];
        
        if(preg_replace('/\/?install\.php$/u', '', $_SERVER['REQUEST_URI']) != '')
            $this -> setup_data['MainPath'] = preg_replace('/\/?install\.php$/u', '', $_SERVER['REQUEST_URI']).'/';
        else
            $this -> setup_data['MainPath'] = '/';
        
        $this -> setup_data['SecretCode'] = Service :: strongRandomString(mt_rand(40, 50));
        $this -> setup_data['EmailFrom'] = 'MV tracker <noreply@'.$_SERVER['HTTP_HOST'].'>';
                
        $config = file_get_contents($this -> root_path.'config/setup.php');
        
        foreach($this -> setup_data as $key => $value)
            $config = str_replace('---'.$key.'---', $value, $config);
        
        file_put_contents($this -> root_path.'config/setup.php', $config);
        
        if($this -> setup_data['MainPath'] != '/')
        {
            $base = 'RewriteBase '.$this -> setup_data['MainPath'];
            $config = file_get_contents($this -> root_path.'.htaccess');
            
            if(strpos($config, $base) === false)
            {
                $config = str_replace('RewriteBase /', $base, $config);
                file_put_contents($this -> root_path.'.htaccess', $config);
            }
        }
        
        return $this;
    }
    
    public function loadDatabaseDump()
    {
        $db = Database :: instance();
        $tables = $db -> getTables();
        
        if(in_array('accounts', $tables) && in_array('versions', $tables))
            return $this;
        
        $file = $this -> root_path.'userfiles/database/initial-dump.sql';
        $lines = file($file);
        $sql = '';
        
        foreach($lines as $line)
        {
            if(substr($line, 0, 2) == '--' || $line == '')
                continue;
            
            $sql .= $line;
            
            if(substr(trim($line), -1, 1) == ';')
            {
                try
                {
                    $db -> query($sql);
                } 
                catch(Exception $error)
                {
                    Debug :: pre($error);
                    exit();
                }
                
                $sql = '';
            }
        }
        
        return $this;
    }
    
    public function writeFirstUserData()
    {
        $accounts = new Accounts();
        
        if($accounts -> countRecords() == 0)
        {
            $accounts -> clearTable();
            $record = $accounts -> getEmptyRecord();
            $record -> login = $_SESSION['installation']['user_login'];
            
            $salt = Registry :: instance() -> getSetting('SecretCode');
            $record -> password = Service :: makeHash($_SESSION['installation']['user_password'].$salt);
            
            $record -> name = $_SESSION['installation']['user_name'];
            $record -> email = $_SESSION['installation']['user_email'];
            $record -> active = 1;
            $record -> send_emails = 1;
            $record -> date_registration = I18n :: getCurrentDateTime();
            
            $record -> create();
        }
        
        $users = new Users();
        
        if($users -> countRecords() == 0)
        {
            $users -> clearTable() -> removeElement('password_repeat');
            $record = $users -> getEmptyRecord();
            
            $record -> login = $_SESSION['installation']['user_login'];
            $record -> password = Service :: makeHash($_SESSION['installation']['user_password']);
            $record -> name = $_SESSION['installation']['user_name'];
            $record -> email = $_SESSION['installation']['user_email'];
            $record -> active = 1;
            $record -> date_registered = I18n :: getCurrentDateTime();
            
            $record -> create();
        }
    }
    
    public function finish()
    {
        $urls = ['admin_panel' => $this -> setup_data['MainPath'].'adminpanel/', 
                 'tracker' => $this -> setup_data['MainPath'].'home/'];
        
        $_SESSION['installation'] = ['done' => true];

        if(Registry :: instance() -> getSetting('MainPath') != '/mv/tracker/')
            @unlink('./install.php');
        
        Registry :: instance() -> setDatabaseSetting('install_date', I18n :: getCurrentDateTime('SQL'));
        Registry :: instance() -> setDatabaseSetting('install_version', self :: $version);
        
        return $urls;
    }
}
?>
*/