<?
include "../../config/autoload.php";
Http :: isAjaxRequest('post', true);
$mv = new Builder();

include_once $mv -> views_path."before-view.php";

if(!$account)
	exit();

if(isset($_POST["get-my-new-tasks"]))
{
	$old = $mv -> tasks -> getLastSeenAssignedTasks($account, $mv -> statuses, $mv -> projects);
	$old = explode(",", $old);
	$current = $mv -> tasks -> getMyAssignedTasks($account, $mv -> statuses, $mv -> projects);

	$new = 0;

	foreach($current as $id)
		if(!in_array($id, $old))
			$new ++;

	if($new)
		echo "<span class=\"new\">".$new."</span>";
}

if(isset($_POST["get-new-comments"]))
{
	if($task = $mv -> tasks -> findRecordById(intval($_POST["get-new-comments"])))
	{
		$count = 0;
		$rows = $mv -> journal -> displayTaskHistory($task, $account, "count");

		foreach($rows as $row)
		{
			if($row["title"] == "created" || ($row["content"] == "" && $row["files"] == "" && $row["title"] == ""))
				continue;

			$count ++;
		}

		echo $count;
	}
}

if(isset($_POST["check-my-new-tasks"]))
{
	$tasks_ids = $mv -> tasks -> getMyAssignedTasks($account, $mv -> statuses, $mv -> projects);
	$tasks_ids = md5(implode(",", $tasks_ids));
	$result = ["reload" => false, "to_see" => $mv -> journal -> getTasksToSee($account -> id)];
	$result["to_see_hash"] = md5(json_encode($result["to_see"]));

	if($_POST["check-my-new-tasks"] && $_POST["check-my-new-tasks"] != $tasks_ids)
		$result["reload"] = true;

	Http :: responseJson($result);
}
?>