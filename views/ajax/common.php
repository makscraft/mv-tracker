<?php
include "../../config/autoload.php";
Http :: isAjaxRequest('post', true);
$mv = new Builder();

include_once $mv -> views_path."before-view.php";

if(!$account)
	exit();

if(isset($_POST["get-mass-action-data"]))
{
	$form = new Form("Tasks");
	$form -> setHtmlParams("date_due", "autocomplete=\"off\"");
	$mv -> tasks -> filterFormValues($form);
	
	$fields = array("status", "complete", "assigned_to", "date_due", "hours_estimated", 
					"hours_spent", "priority", "project", "tracker");
	
	$form -> removeRule($fields, "required");
	
	echo "<form><table>";
	
	for($i = 0; $i < count($fields); $i += 3)
	{
		echo "<tr>";
		echo "<td>".$form -> displayVertical(array($fields[$i]))."</td>";
		
		if(isset($fields[$i + 1]))
			echo "<td>".$form -> displayVertical(array($fields[$i + 1]))."</td>";
		
		if(isset($fields[$i + 2]))
			echo "<td>".$form -> displayVertical(array($fields[$i + 2]))."</td>";
		
		echo "</tr>";
	}
	
	echo "</table></form>";
}

if(isset($_POST["get-add-comment-data"]))
{
	$form = new Form("Tasks");
	$mv -> tasks -> filterFormValues($form);
	$fields = ["assigned_to", "status", "complete"];	

	echo "<form><textarea name=\"text\"></textarea>";
	echo "<div class=\"fields-list\">";

	$form -> removeRule($fields, "required");

	foreach($fields as $field)
		echo "<div>".$form -> displayVertical([$field])."</div>";

	echo "</div></form>";
}

if(isset($_POST["delete-file"], $_POST["params"]))
{
	$types = array("tasks", "journal", "documentation");
	$params = explode("-", $_POST["params"]);
	$type = $params[0];
	$token = Journal :: generateFileDeleteToken($account -> id, $_POST["delete-file"]);
	$result = array("files_left" => "", "error" => "");
	
	if(!in_array($type, $types) || count($params) != 3 || !is_numeric($params[1]) || $params[2] != $token)
	{
		$result["error"] = I18n :: locale("error-data-transfer");
		Http :: responseJson($result);
		exit();
	}
	
	if($record = $mv -> $type -> findRecordById($params[1]))
	{
		if((($type == "documentation" || $type == "tasks") && $account -> id != $record -> author) || 
		   ($type == "journal" && $account -> id != $record -> account))
		{
			$result["error"] = I18n :: locale("author-delete-file");

			Http :: responseJson($result);
			exit();
		}
		
		$root = $mv -> registry -> getSetting("IncludePath");
		$files = explode("-*//*-", $record -> files);
		
		foreach($files as $key => $file)
			if(basename($file) == $_POST["delete-file"])
			{
				if(is_file($root.$file) && !$mv -> registry -> getSetting("DemoMode"))
					@unlink($root.$file);
				
				unset($files[$key]);
				break;
			}
		
		$record -> files = implode("-*//*-", $files);
		$record -> update();
		
		$result["files_left"] = count($files);

		Http :: responseJson($result);
	}
}

if(isset($_POST["add-comment"], $_POST["text"], $_POST["assigned_to"], $_POST["token"]))
{
	if($_POST["text"] == "" && $_POST["assigned_to"] == "" && $_POST["status"] == "" && $_POST["complete"] == "")
		return;
	
	$task = $mv -> tasks -> findRecordById($_POST["add-comment"]);
	$token_comment = $mv -> tasks -> generateDeleteToken($_POST["add-comment"]);
	
	if($_POST["text"] == "" && $_POST["status"] == "" && $_POST["complete"] == "")
		if($_POST["assigned_to"] == $task -> assigned_to)
			return;

	if($token_comment != $_POST["token"] || $mv -> registry -> getSetting("DemoMode"))
		return;
	
	$action_comment = "";
	$text = htmlspecialchars($_POST["text"], ENT_QUOTES);
	$update_task = false;
	
	$old_data = $new_data = $task -> getValues();

	foreach(["assigned_to", "status", "complete"] as $field)
		if($_POST[$field] != "")
		{
			$new_data[$field] = intval($_POST[$field]);
			$task -> $field = $new_data[$field];

			$update_task = true;
		}

	if($update_task)
	{
		$action_comment = $mv -> tasks -> createActionComment($new_data, $old_data);	
		$task -> update();
	}
	
	if($task -> assigned_to != $account -> id)
	{
		$assigned_to = $mv -> accounts -> findRecordById($task -> assigned_to);
		$task -> date_due = I18n :: formatDate($task -> date_due);
		
		Journal :: sendEmail($assigned_to, $task, $text);

		if($text)
			$mv -> journal -> addTaskToSee($task -> assigned_to, $task -> id);
	}
    else
		$mv -> tasks -> dropLastSeenAssignedTasks($account, $mv -> statuses, $mv -> projects);

	$comment_id = $mv -> journal -> add($account, $task, $action_comment, $text, "");
	$_SESSION["scroll-to-comment"] = $comment_id;
	
	echo $comment_id;
}
else if(isset($_POST["edit-comment"], $_POST["token"], $_POST["text"]))
{
	$record = $mv -> journal -> findRecordById($_POST["edit-comment"]);
	$token = $mv -> journal -> generateCommentToken($account -> id, $_POST["edit-comment"]);

	if($mv -> registry -> getSetting("DemoMode"))
		return;
	
	if(!$record || $record -> account != $account -> id || trim($_POST["text"]) == "" || $_POST["token"] != $token)
		return;
	
	$record -> setValue("content", $_POST["text"]) -> update();
	$content = htmlspecialchars($record -> content, ENT_QUOTES);
	
	echo Tasks :: processDescriptionText($content);
}
else if(isset($_POST["delete-comment"], $_POST["token"]))
{
	$record = $mv -> journal -> findRecordById($_POST["delete-comment"]);
	$token = $mv -> journal -> generateCommentToken($account -> id, $_POST["delete-comment"]);

	if($mv -> registry -> getSetting("DemoMode"))
		return;
		
	if(!$record || $record -> account != $account -> id || $_POST["token"] != $token)
		return;
	
	if(!$record -> title && !$record -> files)
	{
		$record -> delete();
		echo "delete";
	}
	else
	{
		$record -> setValue("content", "") -> update();
		echo $record -> id;
	}
}

if(isset($_POST["save-columns"], $_POST["view"]) && $_POST["save-columns"])
	$mv -> tasks -> setAndSaveTableColumns($_POST["view"], $account, explode(",", $_POST["save-columns"]));