<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="robots" content="noindex, nofollow" />
    <?php if($mv -> router -> getUrl() == '/login' && $hide_login_form): ?>
    <? $redirect = Registry :: get('ServerDomain').$mv -> root_path.$after_login_path; ?>
    <meta http-equiv="refresh" content="1;URL='<?php echo $redirect; ?>'" />
    <?php endif; ?>
    <title>MV tracker</title>
    <?php
        $version = "?v".str_replace('.', '', Accounts :: getMvTrackerVersion());
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
        const rootPath = "<?php echo $mv -> root_path; ?>";
        const localeRegion = "<?php echo $region; ?>";
        const americanLocale = <?php echo $mv -> registry -> getSetting("AmericanFix") ? "true" : "false"; ?>;
        const dateFormat = "<?php echo I18n :: getDateFormat(); ?>";
        <?php if($mv -> registry -> getSetting("DemoMode")): ?>
        const isDemoMode = true;
        <?php endif; ?>
    </script>
    <?php
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
    <?php if(!$login_page_url): ?>
    <script type="text/javascript" src="<?php echo $mv -> root_path; ?>views/ajax/translate.php?locale=<?php echo $region; ?>"></script>
    <?php endif; ?>
    <?php echo Form :: createAndDisplayJqueryToken(); ?>     

    <link rel="icon" href="<?php echo $mv -> root_path; ?>media/images/favicon.ico" type="image/x-icon"/>
    <link rel="shortcut icon" href="<?php echo $mv -> root_path; ?>media/images/favicon.ico" type="image/x-icon"/>
</head>
<?php
$css_body = $login_page_url ? ' class="with-background"' : "";
$css_footer = $login_page_url ? ' class="no-background"' : "";
?>
<body<?php echo $css_body; ?>>
<div id="container">
    <div id="sticky-footer-wrapper">
        <div id="header">
            <div class="wrapper clearfix">
                <a href="<?php echo $mv -> root_path.($account ? "home" : "login"); ?>" class="logo">
                   <img src="<?php echo $mv -> root_path; ?>media/images/logo.svg" alt="MV tracker" />
                </a>
                <?php if($account && !isset($hide_login_form)): ?>
                    <div class="search-area">
                        <form method="get" action="<?php echo $mv -> root_path ?>search">
                            <?php $search_text = isset($search_text) ? $search_text : ""; ?>
                            <input class="search-string" type="text" name="text" value="<?php echo $search_text; ?>"/>
                            <input class="search-button" type="submit" value="<?php echo I18n :: locale("search"); ?>"/>
                        </form>
                    </div>
                    <div class="header-right">
                        <ul class="account-menu clearfix">
                            <?php echo $mv -> accounts -> displayAccountMenu($mv -> router); ?>
                            <li class="user-menu">
                                <a href="<?php echo $mv -> root_path ?>profile">
                                    <div class="account-image">
                                        <?php echo $mv -> accounts -> displayAvatar($account); ?>
                                    </div>
                                    <span class="account-name"><?php echo $account -> name; ?></span>
                                </a>
                            </li>
                            <?php $logout = $mv -> root_path."login?logout=".$mv -> accounts -> generateLogoutToken($account); ?>
                            <li class="user-menu exit">
                               <a href="<?php echo $logout; ?>"> / <?php echo I18n :: locale("exit"); ?></a>
                            </li>
                        </ul>
                    </div>
                    <div id="menu-button"></div>
                <?php endif; ?>
            </div>
        </div>
        <?php if($account && !isset($hide_login_form)): ?>
        <div id="mobile-menu">
            <div id="menu-close"></div>
            <ul>
                <?php echo $mv -> accounts -> displayAccountMenu($mv -> router); ?>
                <li class="user-menu">
                    <a href="<?php echo $mv -> root_path ?>profile">
                        <?php echo $account -> name; ?>
                    </a>
                </li>
                <?php $logout = $mv -> root_path."login?logout=".$mv -> accounts -> generateLogoutToken($account); ?>
                <li class="user-menu"><a href="<?php echo $logout; ?>"><?php echo I18n :: locale("exit"); ?></a></li>
            </ul>
        </div>
        <?php endif; ?>
        <div class="wrapper clearfix">