<?
if(isset($_GET["delete"], $_GET["token"]) && is_numeric($_GET["delete"]))
{
	$mv -> projects -> deleteProject($_GET["delete"], $_GET["token"]);
	$mv -> redirect("projects/archive");
}

if(isset($_GET["restore"], $_GET["token"]) && is_numeric($_GET["restore"]))
{
	$mv -> projects -> restoreProject($_GET["restore"], $_GET["token"]);
	$mv -> redirect("projects/archive");
}

if(isset($_GET["archive"], $_GET["token"]) && is_numeric($_GET["archive"]))
{
	$mv -> projects -> archiveProject($_GET["archive"], $_GET["token"]);
	$mv -> redirect("projects");
}

$total = $mv -> projects -> countRecords(array("active" => 0));
$current_page = $mv -> router -> defineCurrentPage("page");
$limit = $mv -> tasks -> definePagerLimit($account);
$mv -> projects -> runPager($total, $limit, $current_page);

$pager_url = $mv -> root_path."projects/archive";

include $mv -> views_path."main-header.php";
?>
<div id="content">
   <div class="content-wrapper">
       <? echo $mv -> accounts -> displayReloadMessage(); ?>
       <h1 class="floated"><? echo I18n :: locale("projects-archive"); ?></h1>
       <div class="item-actions horizontal">
            <a class="create create-project button gradient big" href="<? echo $mv -> root_path; ?>projects/create">
               <? echo I18n :: locale("create-project"); ?>
            </a>
       </div>       
       <table class="tasks-table">
          <tr>
             <th><? echo I18n :: locale("name"); ?></th>
             <th><? echo I18n :: locale("date-created"); ?></th>             
             <th><? echo I18n :: locale("last-activity"); ?></th>
             <th>&nbsp;</th>
          </tr>
          <? echo $mv -> projects -> displayArchive(); ?>
       </table>
       <div class="form-buttons clearfix">
           <?
        	   $pager_model = $mv -> projects;
        	   include $mv -> views_path."parts/pager.php";
        	   include $mv -> views_path."parts/pager-limiter.php";
           ?>        
       </div>    
   </div>
</div>
<?
include $mv -> views_path."main-footer.php";
?>