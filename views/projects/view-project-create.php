<?php
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
        <h1><?php echo I18n :: locale("create-project"); ?></h1>
        <form class="regular" method="post" action="<?php echo $mv -> root_path; ?>projects/create">
           <?php
              echo $form -> displayErrors();
           	  echo $form -> displayVertical($fields);
           ?>
           <div class="form-buttons clearfix">
              <?php echo $form -> displayTokenCSRF(); ?>
              <input type="button" value="<?php echo I18n :: locale("create"); ?>" class="button big submit" />
              <a class="cancel" href="<?php echo $mv -> root_path; ?>projects"><?php echo I18n :: locale("cancel"); ?></a>
           </div>
        </form>
    </div>
<?php
include $mv -> views_path."main-footer.php";
?>