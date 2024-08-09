<?
if(isset($_GET["delete"], $_GET["token"]) && is_numeric($_GET["delete"]))
{
    if(!$mv -> registry -> getSetting("DemoMode"))
        $mv -> projects -> deleteProject($_GET["delete"], $_GET["token"]);
    
    $mv -> redirect("projects");
}

$total = $mv -> projects -> countRecords(array("active" => 1));
$current_page = $mv -> router -> defineCurrentPage("page");
$limit = $mv -> tasks -> definePagerLimit($account);
$mv -> projects -> runPager($total, $limit, $current_page);

$pager_url = $mv -> root_path."projects";

include $mv -> views_path."main-header.php";
?>
    <div id="content">
        <? echo $mv -> accounts -> displayReloadMessage(); ?>
        <h1 class="floated"><? echo I18n :: locale("projects"); ?></h1>        
        <div class="item-actions horizontal">
            <a class="create create-project button gradient big" href="<? echo $mv -> root_path; ?>projects/create">
               <? echo I18n :: locale("create-project"); ?>
            </a>
            <a class="archive button green big" href="<? echo $mv -> root_path; ?>projects/archive">
               <? echo I18n :: locale("projects-archive"); ?>
            </a>
        </div>
        <div class="clear"></div>
        <table class="tasks-table">
            <tr>
                <th><? echo I18n :: locale("name"); ?></th>
                <th><? echo I18n :: locale("tasks"); ?></th>
                <th><? echo I18n :: locale("implementation"); ?></th>
                <th><? echo I18n :: locale("hours-spent"); ?></th>
                <th><? echo I18n :: locale("date-created"); ?></th>
                <th><? echo I18n :: locale("last-activity"); ?></th>
                <th>&nbsp;</th>
            </tr>
            <? echo $mv -> projects -> display(); ?>
        </table>
        <div class="form-buttons clearfix">
           <?
        	   $pager_model = $mv -> projects;
        	   include $mv -> views_path."parts/pager.php";
        	   include $mv -> views_path."parts/pager-limiter.php";
           ?>        
        </div>
    </div>
<?
include $mv -> views_path."main-footer.php";
?>