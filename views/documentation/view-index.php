<?php
if(isset($_GET["delete"], $_GET["token"]) && is_numeric($_GET["delete"]))
{
   if(!$mv -> registry -> getSetting("DemoMode"))
      $mv -> documentation -> deleteDocument($_GET["delete"], $_GET["token"]);

   $mv -> redirect("documentation");
}

$projects = $mv -> projects -> getActiveProjectsIds();
$projects_ids = $mv -> documentation -> selectColumn(array("fields->" => "project", "group->by" => "project"));

if($projects != "0")
	$projects = array_intersect($projects_ids, explode(",", $projects));

$projects = (is_array($projects) && count($projects)) ? implode(",", $projects) : "0";
$params = array("project->in" => $projects);

$mv -> documentation -> runFilter(array("project"));
$mv -> documentation -> filter -> filterValuesList("project", array("id->in" => $projects, "order->asc" => "name"));

$conditions = $mv -> documentation -> filter -> getConditions();
$params = array_merge($params, $conditions);

$total = $mv -> documentation -> countRecords($params);
$current_page = $mv -> router -> defineCurrentPage("page");
$limit = $mv -> tasks -> definePagerLimit($account);
$mv -> documentation -> runPager($total, $limit, $current_page);

$mv -> documentation -> runSorter(array("id", "name", "project", "author", "date_updated"), "id", "desc");

if(isset($_GET["sort-field"], $_GET["sort-order"]))
{
	$mv -> documentation -> sorter -> setParams($_GET["sort-field"], $_GET["sort-order"]);
	
	if($mv -> documentation -> sorter -> getField() == $_GET["sort-field"])
		if($mv -> documentation -> sorter -> getOrder() == $_GET["sort-order"])
		{
			$_SESSION["account"]["sorting"]["documentation"]["field"] = $mv -> documentation -> sorter -> getField();
			$_SESSION["account"]["sorting"]["documentation"]["order"] = $mv -> documentation -> sorter -> getOrder();
		}
}

if(isset($_SESSION["account"]["sorting"]["documentation"]["field"]))
	$mv -> documentation -> sorter -> setParams($_SESSION["account"]["sorting"]["documentation"]["field"],
												$_SESSION["account"]["sorting"]["documentation"]["order"]);

$sorter_url = $pager_url = $mv -> documentation -> filter -> addUrlParams($mv -> root_path."documentation");

include $mv -> views_path."main-header.php";
?>
<div id="content" class="documents-page">
   <div class="content-wrapper">
      <?php echo $mv -> accounts -> displayReloadMessage(); ?>
      <h1><?php echo I18n :: locale("documentation"); ?></h1>
      <div class="open-filters">
         <span><?php echo I18n :: locale("project"); ?></span>
         <?php echo $mv -> documentation -> filter ->  display("project"); ?>
      </div>
      <div class="item-actions">
          <a class="create button gradient big" href="<?php echo $mv -> root_path; ?>documentation/create">
              <?php echo I18n :: locale("create-document"); ?>
          </a>
      </div>
      <div class="clear"></div>
      <form action="<?php echo $mv -> root_path; ?>documentation" method="post" id="items-table-form">
         <table class="tasks-table">
            <tr>
                <th><?php echo $mv -> documentation -> sorter -> displayLink("id", "#", $sorter_url); ?></th>
                <th><?php echo $mv -> documentation -> sorter -> displayLink("name", I18n :: locale("name"), $sorter_url); ?></th>
                <th><?php echo $mv -> documentation -> sorter -> displayLink("project", I18n :: locale("project"), $sorter_url); ?></th>
                <th><?php echo $mv -> documentation -> sorter -> displayLink("author", I18n :: locale("author"), $sorter_url); ?></th>
                <th><?php echo $mv -> documentation -> sorter -> displayLink("date_updated", I18n :: locale("date-updated"), $sorter_url); ?></th>
                <th>&nbsp;</th>
            </tr>
            <?php echo $mv -> documentation -> display($params); ?>
         </table>
         <div class="form-buttons docs clearfix">
             <?php
         	    $pager_model = $mv -> documentation;
           	    include $mv -> views_path."parts/pager.php";
      		 ?>
             <input type="hidden" id="filter-url-params" value="<?php echo $mv -> documentation -> filter -> getUrlParams(); ?>" />
             <?php include $mv -> views_path."parts/pager-limiter.php"; ?>
         </div>         
      </form> 
   </div>
</div>
<?php
include $mv -> views_path."main-footer.php";
?>