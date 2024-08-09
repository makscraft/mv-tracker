<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="robots" content="noindex, nofollow" />
    <? if($mv -> router -> getUrl() == "/login" && $hide_login_form): ?>
    <meta http-equiv="refresh" content="1; URL=<? echo $mv -> root_path.$after_login_path; ?>" />
    <? endif; ?>
    <title>MV tracker</title>
    <?
        $version = "?v".str_replace(".", "", Installation :: $version);
        $region = I18n :: getRegion();

        CacheMedia :: instance();

        CacheMedia :: addCssFile([
            'default.css',
            'style.css',
            'form.css',
            'datepicker.css',
            'media.css'
        ]);

        if(Router :: isLocalHost())
            echo CacheMedia :: getInitialFiles('css');
        else
            echo CacheMedia :: getCssCache();
    ?>

    <script type="text/javascript">
        const rootPath = "<? echo $mv -> root_path; ?>";
        const localeRegion = "<? echo $region; ?>";
        const americanLocale = <? echo $mv -> registry -> getSetting("AmericanFix") ? "true" : "false"; ?>;
        const dateFormat = "<? echo I18n :: getDateFormat(); ?>";
        <? if($mv -> registry -> getSetting("DemoMode")): ?>
        const isDemoMode = true;
        <? endif; ?>
    </script>
    <?
        CacheMedia :: addJavaScriptFile([
            'jquery.js',
            'datepicker.min.js',
            'modal.js',
            'utils.js'
        ]);

        if($language != 'ru')
            CacheMedia :: addJavaScriptFile('datepicker.'.$language.'.js');

        if(Router :: isLocalHost())
            echo CacheMedia :: getInitialFiles('js');
        else
            echo CacheMedia :: getJavaScriptCache();
    ?>
    <? if(!$login_page_url): ?>
    <script type="text/javascript" src="<? echo $mv -> root_path; ?>views/ajax/translate.php?locale=<? echo $region; ?>"></script>
    <? endif; ?>
    <? echo Form :: createAndDisplayJqueryToken(); ?>     

    <link rel="icon" href="<? echo $mv -> root_path; ?>media/images/favicon.ico" type="image/x-icon"/>
    <link rel="shortcut icon" href="<? echo $mv -> root_path; ?>media/images/favicon.ico" type="image/x-icon"/>
</head>
<?
$css_body = $login_page_url ? ' class="with-background"' : "";
$css_footer = $login_page_url ? ' class="no-background"' : "";
?>
<body<? echo $css_body; ?>>
<div id="container">
    <div id="sticky-footer-wrapper">
        <div id="header">
            <div class="wrapper clearfix">
                <a href="<? echo $mv -> root_path.($account ? "home" : "login"); ?>" class="logo">
                   <img src="<? echo $mv -> root_path; ?>media/images/logo.svg" alt="MV tracker" />
                </a>
                <? if($account && !isset($hide_login_form)): ?>
                    <div class="search-area">
                        <form method="get" action="<? echo $mv -> root_path ?>search">
                            <? $search_text = isset($search_text) ? $search_text : ""; ?>
                            <input class="search-string" type="text" name="text" value="<? echo $search_text; ?>"/>
                            <input class="search-button" type="submit" value="<? echo I18n :: locale("search"); ?>"/>
                        </form>
                    </div>
                    <div class="header-right">
                        <ul class="account-menu clearfix">
                            <? echo $mv -> accounts -> displayAccountMenu($mv -> router); ?>
                            <li class="user-menu">
                                <a href="<? echo $mv -> root_path ?>profile">
                                    <div class="account-image">
                                        <? echo $mv -> accounts -> displayAvatar($account); ?>
                                    </div>
                                    <span class="account-name"><? echo $account -> name; ?></span>
                                </a>
                            </li>
                            <? $logout = $mv -> root_path."login?logout=".$mv -> accounts -> generateLogoutToken($account); ?>
                            <li class="user-menu exit">
                               <a href="<? echo $logout; ?>"> / <? echo I18n :: locale("exit"); ?></a>
                            </li>
                        </ul>
                    </div>
                    <div id="menu-button"></div>
                <? endif; ?>
            </div>
        </div>
        <? if($account && !isset($hide_login_form)): ?>
        <div id="mobile-menu">
            <div id="menu-close"></div>
            <ul>
                <? echo $mv -> accounts -> displayAccountMenu($mv -> router); ?>
                <li class="user-menu">
                    <a href="<? echo $mv -> root_path ?>profile">
                        <? echo $account -> name; ?>
                    </a>
                </li>
                <? $logout = $mv -> root_path."login?logout=".$mv -> accounts -> generateLogoutToken($account); ?>
                <li class="user-menu"><a href="<? echo $logout; ?>"><? echo I18n :: locale("exit"); ?></a></li>
            </ul>
        </div>
        <? endif; ?>
        <div class="wrapper clearfix">