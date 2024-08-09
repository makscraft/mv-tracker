<?
Installation :: checkFinalInstallRemove($mv);

$account = $mv -> accounts -> checkAuthorization();

if(!$account && isset($_COOKIE['autologin_key'], $_COOKIE['autologin_token']))
	$account = $mv -> accounts -> autoLogin($_COOKIE['autologin_key'], $_COOKIE['autologin_token']);

$login_urls = array('/login', '/recovery', '/recovery/create');
$url = $mv -> router -> getUrl();
$login_page_url = in_array($url, $login_urls);

if(!$account && !$login_page_url)
{
    if($url != '/' && strpos($url, '/ajax/') === false && strpos($url, '.php') === false)
    {
        $_SESSION['login-back-path'] = $url;
        
        if(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'])
            $_SESSION['login-back-path'] .= '?'.$_SERVER['QUERY_STRING'];
    }
    
    if(strpos($url, '/ajax/') === false && strpos($url, '.php') === false)
        $mv -> redirect('/login');
}

$language = I18n :: getRegion();

if($account)
    if($format = Accounts :: getSetting($account, 'date_format'))
        I18n :: setDateFormat($format);
?>