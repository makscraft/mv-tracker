<?
$task = $mv -> tasks -> defineTaskPage($mv -> router);
$mv -> display404($task);

$old_data = $task -> getValues();
$can_edit_content = ($task -> author == $account -> id);

$form = new Form("Tasks", $task -> id);
$form -> loadRecord();
$form -> setHtmlParams("date_due", "autocomplete=\"off\"");
$form -> addField(["Файлы", "file", "files", ["files_folder" => "uploads", "multiple" => 5]]);

$fields = array("name", "tracker", "project", "date_due", "assigned_to", "priority", "status", "complete",
    			    "hours_estimated", "hours_spent", "description", "comment", "files");

$form -> addField(array("{comment}", "text", "comment"));
$form -> addField(array("{attached-files}", "file", "files[]"));

$mv -> tasks -> filterFormValues($form) -> changeFloatSeparator();
$form -> useTokenCSRF();
$action_form = $mv -> root_path."task/edit/".$task -> id;

if(Http :: isPostRequest())
{
    $form -> submit() -> validate($fields);

    if($form -> isSubmitted() && $form -> isValid())
    {
        $old_files = $task -> files;
        $task -> setValues($form -> getAllValues($fields));
        $task -> files = $old_files;

        if(!$can_edit_content)
            $task -> description = $old_data["description"];

        $task -> date_updated = I18n :: getCurrentDateTime();
        $task -> update();

        $new_data = $task -> getValues();

        $notify = $mv -> journal -> needsNotification($old_data, $new_data);
        $action_comment = $mv -> tasks -> createActionComment($new_data, $old_data);

        $form -> copyMultipleFilesToTargetFolder("files");
        $data_files = $form -> getMultipleFilesValue("files");
        $files = MultiImagesModelElement :: packValue($data_files);

        if($task -> assigned_to && $task -> assigned_to != $account -> id && ($notify || $form -> comment || $files))
        {
            $assigned_to = $mv -> accounts -> findRecordById($task -> assigned_to);
            $message = $form -> comment ? $form -> comment : $task -> description;
            
            if($notify == "closed")
            	$message = $form -> comment;

            Journal :: sendEmail($assigned_to, $task, $message, $files);
            $mv -> journal -> addTaskToSee($task -> assigned_to, $task -> id);
        }

        if($task -> assigned_to && $task -> assigned_to == $account -> id)
            $mv -> tasks -> dropLastSeenAssignedTasks($account, $mv -> statuses, $mv -> projects);

        if($form -> comment != "" || $action_comment != "" || $files)
            $comment_id = $mv -> journal -> add($account, $task, $action_comment, $form -> comment, $files);

        $_SESSION["account"]["message-success"] = I18n :: locale("task-updated");

        $url = "task/".$task -> id;
        
        if(isset($comment_id))
        	$_SESSION["scroll-to-comment"] = $comment_id;
        
        $mv -> redirect($url);
    }
}

include $mv -> views_path."main-header.php";
?>
    <div id="content">
        <h1><? echo I18n :: locale("edit-task"); ?></h1>
        <? echo $form -> displayErrors(); ?>
        <form class="regular" enctype="multipart/form-data" method="post" action="<? echo $action_form; ?>">
               <div class="single">
                  <? echo $form -> displayVertical(array("name")); ?>
               </div>
               <div class="double">
                  <div class="column">
                     <? echo $form -> displayVertical(array("tracker")); ?>
                  </div>
                  <div class="column">
                     <? echo $form -> displayVertical(array("project")); ?>
                  </div>
               </div>
               <div class="double">
                  <div class="column">
                     <? echo $form -> displayVertical(array("assigned_to")); ?>
                  </div>
                  <div class="column">
                     <? echo $form -> displayVertical(array("date_due")); ?>
                  </div>
               </div>
               <div class="double">
                  <div class="column">
                     <? echo $form -> displayVertical(array("priority")); ?>
                  </div>
                  <div class="column">
                     <? echo $form -> displayVertical(array("status")); ?>
                  </div>
               </div>
               <div class="triple">
                  <div class="column">
                     <? echo $form -> displayVertical(array("complete")); ?>
                  </div>
                  <div class="column">
                     <? echo $form -> displayVertical(array("hours_estimated")); ?>
                  </div>
                  <div class="column">
                     <? echo $form -> displayVertical(array("hours_spent")); ?>
                  </div>
               </div>
               <? if($can_edit_content): ?>
               <div class="single">
                  <div class="field-name">
                     <? echo I18n :: locale("description"); ?>                     
                     <span id="edit-description"><? echo I18n :: locale("edit"); ?></span>
                  </div>
                  <div id="description-area" class="field-input hidden">
                     <? echo $form -> displayFieldHtml("description"); ?>
                     <? echo Tasks :: displayTextileHelp(); ?>
                  </div>
               </div>
               <? endif; ?>
               <div class="single">
                  <? echo $form -> displayVertical(array("comment")); ?>
               </div>               
               <div class="single">
                  <div class="field-name"><? echo I18n :: locale("attached-files"); ?></div>
                  <div class="field-input">
                     <? echo $form -> displayFieldHtml("files"); ?>
                  </div>
               </div>
               <div class="form-buttons clearfix">
                   <? echo $form -> displayTokenCSRF(); ?>
                   <input class="button big submit" type="button" value="<? echo I18n :: locale("save"); ?>"/>
                   <a class="cancel" href="<? echo $mv -> root_path; ?>task/<? echo $task -> id; ?>">
                       <? echo I18n :: locale("cancel"); ?>
                   </a>
               </div>
         </form>
    </div>
<?
include $mv -> views_path."main-footer.php";
?>