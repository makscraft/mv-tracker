<?php
$project = $mv -> projects -> defineProjectPage($mv -> router);
$mv -> display404($project);

$open = $mv -> statuses -> getOpenStatusesIds();
$params = array("status->in" => $open, "project" => $project -> id);
$base_url = $_SESSION["last-page"] = "project/".$project -> id;
$current_view = "projects";
$columns = $mv -> tasks -> defineTableColumns($current_view, $account);
$to_see = $mv -> journal -> getTasksToSee($account -> id);

include $mv -> views_path."parts/tasks-actions.php";
include $mv -> views_path."main-header.php";
?>
<div id="content" class="project-page">
   <script type="text/javascript">
      let myCurrentTasksHash = "";
      let tasksToSee = [<?php echo implode(", ", $to_see); ?>];
   </script>
   <div class="content-wrapper">
      <h1><?php echo $project -> name; ?></h1>
      <div class="item-actions">
         <a class="create button gradient big" href="<?php echo $mv -> root_path; ?>tasks/create?project=<?php echo $project -> id; ?>"><?php echo I18n :: locale("create-task"); ?></a>
      </div>
      <div class="clear"></div>
      <?php echo $mv -> accounts -> displayReloadMessage(); ?>
      <input type="button" class="button green medium mass-action" value="<?php echo I18n :: locale("with-selected"); ?>">
      <?php include $mv -> views_path."parts/tasks-filters.php"; ?>
      <form action="<?php echo $mv -> root_path; ?>project/<?php echo $project -> id; ?>" method="post" id="items-table-form">
	      <table class="tasks-table">
	         <tr>
                <th class="mobile-visible"><input type="checkbox" id="check-all"/></th>
                <th class="for-mobile"><?php echo I18n :: locale("tasks"); ?></th>
                <?php echo $mv -> tasks -> displayTableColumns($columns, $sorter_url); ?>         
                <th class="mobile-visible">&nbsp;</th>
	         </tr>
	         <?php echo $mv -> tasks -> display($params, $columns, $account); ?>
	     </table>
         <?php include $mv -> views_path."parts/tasks-bottom.php"; ?>
   </div>
</div>
<?php
include $mv -> views_path."main-footer.php";
?>