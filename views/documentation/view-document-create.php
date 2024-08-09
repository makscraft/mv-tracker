<?
$projects = $mv -> projects -> getActiveProjectsIds();

$form = new Form("Documentation");
$form -> filterValuesList("project", array("id->in" => $projects, "order->asc" => "name"));

$fields = array("name", "project", "content", "files");
$form -> setRequired("content") -> useTokenCSRF();
$form -> removeField("files");
$form -> addField(["{attached-files}", "file", "files", ["files_folder" => "documents", "multiple" => 5]]);

if(Http :: isPostRequest())
{
    $form -> submit() -> validate($fields);

    if($form -> isSubmitted() && $form -> isValid())
    {
        $document = $mv -> documentation -> getEmptyRecord();
        $document -> setValues($form -> getAllValues($fields));
        $document -> date_created = $document -> date_updated = I18n :: getCurrentDateTime();
        $document -> author = $account -> id;

        $form -> copyMultipleFilesToTargetFolder("files");
        
        $document -> files = implode("-*//*-", $form -> getMultipleFilesValue("files"));
        $document -> create();

        $_SESSION["account"]["message-success"] = I18n :: locale("document-created");
        $mv -> redirect("documentation/".$document -> id);
    }
}

include $mv -> views_path."main-header.php";
?>
    <div id="content">
        <h1><? echo I18n :: locale("create-document"); ?></h1>
        <form enctype="multipart/form-data" class="regular" method="post" action="<? echo $mv -> root_path; ?>documentation/create">
            <?
               echo $form -> displayErrors();
               echo $form -> displayVertical(["name", "project", "content"]);
               echo Tasks :: displayTextileHelp();
            ?>
            <div class="single">
                <div class="field-name"><? echo I18n :: locale("attached-files"); ?></div>
                <div class="field-input">
                   <? echo $form -> displayFieldHtml("files"); ?>
                </div>
            </div>
            <div class="form-buttons clearfix">
                <? echo $form -> displayTokenCSRF(); ?>
                <input class="button big submit" type="button" value="<? echo I18n :: locale("create"); ?>"/>
                <a class="cancel" href="<? echo $mv -> root_path; ?>documentation/"><? echo I18n :: locale("cancel"); ?></a>
            </div>            
        </form>
    </div>
<?
include $mv -> views_path."main-footer.php";
?>