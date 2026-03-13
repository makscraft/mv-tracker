<?php
$open = $mv -> statuses -> getOpenStatusesIds();
$projects = $mv -> projects -> getActiveProjectsIds();
$params = array("assigned_to" => $account -> id, "status->in" => $open, "project->in" => $projects);
$base_url = $_SESSION["last-page"] = "home";
$current_view = "my-tasks";
$columns = $mv -> tasks -> defineTableColumns($current_view, $account);

$mv -> tasks -> dropLastSeenAssignedTasks($account, $mv -> statuses, $mv -> projects);
$tasks_ids = $mv -> tasks -> getLastSeenAssignedTasks($account, $mv -> statuses, $mv -> projects);
$to_see = $mv -> journal -> getTasksToSee($account -> id);

include $mv -> views_path."parts/tasks-actions.php";
include $mv -> views_path."main-header.php";
?>
    <div id="content" class="tasks-page">
        <?php echo $mv -> accounts -> displayReloadMessage(); ?>
        <script type="text/javascript">
            let myCurrentTasksHash = "<?php echo md5($tasks_ids); ?>";
            let tasksToSee = [<?php echo implode(", ", $to_see); ?>];
        </script>
        <div class="top-buttons clearfix">
            <a class="button grey big tab active" href="<?php echo $mv -> root_path; ?>home"><?php echo I18n :: locale("my-tasks"); ?></a>
            <a class="button grey big tab" href="<?php echo $mv -> root_path; ?>tasks"><?php echo I18n :: locale("all-tasks"); ?></a>
            <a class="button gradient big create-task" href="<?php echo $mv -> root_path; ?>tasks/create"><?php echo I18n :: locale("create-task"); ?></a>
        </div>
        <input type="button" class="button green medium mass-action" value="<?php echo I18n :: locale("with-selected"); ?>">
        <?php include $mv -> views_path."parts/tasks-filters.php"; ?>
        <form action="<?php echo $mv -> root_path; ?>home" method="post" id="items-table-form">
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
<?php
include $mv -> views_path."main-footer.php";
?>