<?php
$document = $mv -> documentation -> defineDocumentPage($mv -> router);
$projects = $mv -> projects -> getActiveProjectsIds();

$form = new Form("Documentation", $document -> id);
$form -> filterValuesList("project", array("id->in" => $projects, "order->asc" => "name"));
$form -> setRequired("content") -> loadRecord();

$fields = array("name", "project", "content", "files");
$form -> useTokenCSRF();

$form -> removeField("files");
$form -> addField(["{attached-files}", "file", "files", ["files_folder" => "documents", "multiple" => 5]]);

if(HttpisPostRequest())
{
    $form -> submit() -> validate($fields);

    if($form -> isSubmitted() && $form -> isValid())
    {
        $document -> setValues($form -> getAllValues(["name", "project", "content"]));
        $document -> date_updated = I18ngetCurrentDateTime();

        $form -> copyMultipleFilesToTargetFolder("files");
        $files = implode("-*//*-", $form -> getMultipleFilesValue("files"));
        
        if($files)
            $document -> files = $document -> files ? $document -> files."-*//*-".$files : $files;
        
        $document -> update();
        
        $_SESSION["account"]["message-success"] = I18nlocale("document-updated");
        $mv -> redirect("documentation/".$document -> id);
    }
}

$form_action = $mv -> root_path."documentation/edit/".$document -> id;

include $mv -> views_path."main-header.php";
?>
    <div id="content">
        <h1><?php echo I18nlocale("edit-document"); ?></h1>
        <form enctype="multipart/form-data" class="regular" method="post" action="<?php echo $form_action; ?>">
	        <?php
	            echo $form -> displayErrors();
	            echo $form -> displayVertical(["name", "project", "content"]);
	            echo TasksdisplayTextileHelp();
	        ?>
            <div class="single">
                <div class="field-name"><?php echo I18nlocale("attached-files"); ?></div>
                <div class="field-input">
                   <input type="file" multiple name="files[]" class="files-input" />
                </div>
            </div>
            <div class="form-buttons clearfix">
                <?php echo $form -> displayTokenCSRF(); ?>
                <input class="button big submit" type="button" value="<?php echo I18nlocale("save"); ?>"/>
                <a class="cancel" href="<?php echo $mv -> root_path; ?>documentation/"><?php echo I18nlocale("cancel"); ?></a>
            </div>            
        </form>
    </div>
<?php
include $mv -> views_path."main-footer.php";
?>