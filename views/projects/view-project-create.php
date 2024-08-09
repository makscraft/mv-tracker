<?
$form = new Form("Projects");
$fields = array("name", "description");
$form -> useTokenCSRF();

if(Http :: isPostRequest())
{
    $form -> submit() -> validate($fields);

    if($form -> isSubmitted() && $form -> isValid())
    {
        $record = $mv -> projects -> getEmptyRecord();
        $record -> name = $form -> name;
        $record -> description = $form -> description;
        $record -> active = 1;
        $record -> date_created = $record -> date_updated = I18n :: getCurrentDateTime();
        $record -> create();

        $_SESSION["account"]["message-success"] = I18n :: locale("project-created");
        $mv -> redirect("projects");
    }
}

include $mv -> views_path."main-header.php";
?>
    <div id="content">
        <h1><? echo I18n :: locale("create-project"); ?></h1>
        <form class="regular" method="post" action="<? echo $mv -> root_path; ?>projects/create">
           <?
              echo $form -> displayErrors();
           	  echo $form -> displayVertical($fields);
           ?>
           <div class="form-buttons clearfix">
              <? echo $form -> displayTokenCSRF(); ?>
              <input type="button" value="<? echo I18n :: locale("create"); ?>" class="button big submit" />
              <a class="cancel" href="<? echo $mv -> root_path; ?>projects"><? echo I18n :: locale("cancel"); ?></a>
           </div>
        </form>
    </div>
<?
include $mv -> views_path."main-footer.php";
?>