</div>
</div>
<div id="footer"<?php echo $css_footer; ?>>
    <div class="wrapper">
        <p>&copy; <?php echo date("Y"); ?> MV tracker. All rights reserved. Version <?php echo SetupComposer :: $version; ?></p>
        <a href="https://mv-tracker.com" target="_blank">MV tracker</a>
        <a href="https://mv-framework.<?php echo $language == "ru" ? "ru" : "com"; ?>" target="_blank">Powered by MV framework</a>
    </div>
</div>
<div id="overlay"></div>
<?php
$url = $mv -> router -> getUrlPart(0);
$active_tasks = ($url == "home" || $url == "tasks") ? ' class="active"' : "";
$active_projects = ($url == "projects" || $url == "project" || $url == "archive") ? ' class="active"' : "";
?>
<div class="bottom-menu">
    <a href="<?php echo $mv -> root_path; ?>home"<?php echo $active_tasks; ?>>
        <span class="icon tasks"><?php echo I18n :: locale("tasks"); ?></span>
    </a>
    <a href="<?php echo $mv -> root_path; ?>projects"<?php echo $active_projects; ?>>
        <span class="icon docs"><?php echo I18n :: locale("projects"); ?></span>
    </a>
</div>
</div>
</body>
</html>