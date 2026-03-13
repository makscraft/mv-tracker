<?php
$projects = $mv -> projects -> getActiveProjectsIds();

$form = new Form("Documentation");
$form -> filterValuesList("project", array("id->in" => $projects, "order->asc" => "name"));

$fields = array("name", "project", "content", "files");
$form -> setRequired("content") -> useTokenCSRF();
$form -> removeField("files");
$form -> addField(["{attached-files}", "file", "files", ["files_folder" => "documents", "multiple" => 5]]);

if(HttpisPostRequest())
{
    $form -> submit() -> validate($fields);

    if($form -> isSubmitted() && $form -> isValid())
    {
        $document = $mv -> documentation -> getEmptyRecord();
        $document -> setValues($form -> getAllValues($fields));
        $document -> date_created = $document -> date_updated = I18ngetCurrentDateTime();
        $document -> author = $account -> id;

        $form -> copyMultipleFilesToTargetFolder("files");
        
        $document -> files = implode("-*//*-", $form -> getMultipleFilesValue("files"));
        $document -> create();

        $_SESSION["account"]["message-success"] = I18nlocale("document-created");
        $mv -> redirect("documentation/".$document -> id);
    }
}

include $mv -> views_path."main-header.php";
?>
    <div id="content">
        <h1><?php echo I18nlocale("create-document"); ?></h1>
        <form enctype="multipart/form-data" class="regular" method="post" action="<?php echo $mv -> root_path; ?>documentation/create">
            <?php
               echo $form -> displayErrors();
               echo $form -> displayVertical(["name", "project", "content"]);
               echo TasksdisplayTextileHelp();
            ?>
            <div class="single">
                <div class="field-name"><?php echo I18nlocale("attached-files"); ?></div>
                <div class="field-input">
                   <?php echo $form -> displayFieldHtml("files"); ?>
                </div>
            </div>
            <div class="form-buttons clearfix">
                <?php echo $form -> displayTokenCSRF(); ?>
                <input class="button big submit" type="button" value="<?php echo I18nlocale("create"); ?>"/>
                <a class="cancel" href="<?php echo $mv -> root_path; ?>documentation/"><?php echo I18nlocale("cancel"); ?></a>
            </div>            
        </form>
    </div>
<?php
include $mv -> views_path."main-footer.php";
?>