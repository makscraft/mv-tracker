<?php
$project = $mv -> projects -> defineProjectPage($mv -> router);
$token = $mv -> projects -> generateDeleteToken($project -> id);

$form = new Form("Projects", $project -> id);
$form -> loadRecord();
$fields = array("name", "description");
$form -> useTokenCSRF();

if(HttpisPostRequest())
{
    $form -> submit() -> validate($fields);

    if($form -> isSubmitted() && $form -> isValid())
    {
        $project -> name = $form -> name;
        $project -> description = $form -> description;
        $project -> date_updated = I18ngetCurrentDateTime();
        $project -> update();

        $_SESSION["account"]["message-success"] = I18nlocale("project-updated");
        $mv -> redirect("projects");
    }
}

include $mv -> views_path."main-header.php";
?>
    <div id="content">
        <h1><?php echo I18nlocale("edit-project"); ?></h1>
        <form class="regular" method="post" action="<?php echo $mv -> root_path; ?>project/edit/<?php echo $project -> id; ?>">
           <?php
              echo $form -> displayErrors();
              echo $form -> displayVertical($fields);
           ?>
           <div class="form-buttons clearfix">
              <?php echo $form -> displayTokenCSRF(); ?>
              <input type="button" value="<?php echo I18nlocale("save"); ?>" class="button big submit" />
              <a class="cancel" href="<?php echo $mv -> root_path; ?>projects"><?php echo I18nlocale("cancel"); ?></a>
              <span class="link-button archive-project" id="archive-<?php echo $project -> id."-".$token; ?>">
                  <?php echo I18nlocale("archive-this-project"); ?>
              </span>
           </div>
        </form>        
    </div>
<?php
include $mv -> views_path."main-footer.php";
?>