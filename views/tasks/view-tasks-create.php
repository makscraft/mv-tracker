<?
$form = new Form("Tasks");
$fields = array("name", "tracker", "project", "date_due", "assigned_to", "priority", "status", "complete",
       			 "hours_estimated", "hours_spent", "description", "files");

$form -> setHtmlParams("date_due", "autocomplete=\"off\"");
$form -> addField(["{attached-files}", "file", "files", ["files_folder" => "uploads", "multiple" => 5]]);

$mv -> tasks -> filterFormValues($form) -> changeFloatSeparator();
$form -> useTokenCSRF();

$back_url = $mv -> root_path.(isset($_SESSION["last-page"]) ? $_SESSION["last-page"] : "tasks");

if(Http :: isPostRequest())
{
    $form -> submit() -> validate($fields);

    if($form -> isSubmitted() && $form -> isValid())
    {
        $form -> copyMultipleFilesToTargetFolder("files");

        $record = $mv -> tasks -> getEmptyRecord();
        $record -> setValues($form -> getAllValues($fields));
        $record -> date_created = I18n :: getCurrentDateTime();
        $record -> author = $account -> id;

        $data_files = $form -> getMultipleFilesValue("files");
        $record -> files = MultiImagesModelElement :: packValue($data_files);
        $record -> create();
        
        $message = "";
        $action_comment = "created";
		        
        if($record -> assigned_to)
            $action_comment = I18n :: locale("assigned-to").": ".$record -> getEnumTitle("assigned_to");
        	
        $mv -> journal -> add($account, $record, $action_comment, $message);

        if($record -> assigned_to && $record -> assigned_to != $account -> id)
        {
            $assigned_to = $mv -> accounts -> findRecordById($record -> assigned_to);
            Journal :: sendEmail($assigned_to, $record, $record -> description, $record -> files);
            $mv -> journal -> addTaskToSee($record -> assigned_to, $record -> id);
        }

        if($record -> assigned_to && $record -> assigned_to == $account -> id)
            $mv -> tasks -> dropLastSeenAssignedTasks($account, $mv -> statuses, $mv -> projects);
		
        $_SESSION["account"]["message-success"] = I18n :: locale("task-created");
        
        $mv -> redirect("task/".$record -> id);
    }
}
else
{
    $form -> tracker = $mv -> trackers -> findRecord(array("order->asc" => "position")) -> id;
    $form -> status = $mv -> statuses -> findRecord(array("order->asc" => "position")) -> id;
    $form -> priority = $mv -> priorities -> findRecord(array("order->asc" => "position")) -> id;

    if(isset($_GET["project"]))
        if($project = $mv -> projects -> findRecord(array("id" => $_GET["project"], "active" => 1)))
            $form -> project = $project -> id;
}

include $mv -> views_path."main-header.php";
?>
   <div id="content">
	  <h1><? echo I18n :: locale("create-task"); ?></h1>        
	  <form class="regular" enctype="multipart/form-data" method="post" action="<? echo $mv -> root_path; ?>tasks/create">
	     <? echo $form -> displayErrors(); ?>
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
               <div class="single">
                  <? echo $form -> displayVertical(array("description")); ?>
                  <? echo Tasks :: displayTextileHelp(); ?>
               </div>
               <div class="single">
                  <div class="field-name"><? echo I18n :: locale("attached-files"); ?></div>
                  <div class="field-input">
                     <? echo $form -> displayFieldHtml("files"); ?>
                  </div>
               </div>
               <div class="form-buttons clearfix">
                    <? echo $form -> displayTokenCSRF(); ?>
                    <input class="button big submit" type="button" value="<? echo I18n :: locale("create"); ?>"/>
                    <a class="cancel" href="<? echo $back_url; ?>"><? echo I18n :: locale("cancel"); ?></a>
               </div>
         </form>
   </div>
<?
include $mv -> views_path."main-footer.php";
?>