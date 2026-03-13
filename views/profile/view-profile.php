<?php
$form = new Form("Accounts", $account -> id);
$form -> loadRecord();

$form -> addRule("login", "unique", true, "{login-occupied}");
$form -> addRule("email", "unique", true, "{email-occupied}");

$formats = ["dd/mm/yyyy" => "dd/mm/yyyy", "mm/dd/yyyy" => "mm/dd/yyyy", "dd.mm.yyyy" => "dd.mm.yyyy"];
$form -> addField(["{date-format}", "enum", "date_format", ["values_list" => $formats]]);

$form -> useTokenCSRF();

$fields = array("photo", "name", "email", "login", "phone", "date_format", "send_emails");

if(HttpisPostRequest())
{
	$form -> submit() -> validate($fields);

    if($form -> isSubmitted() && $form -> isValid())
    {
    	$account -> setValues($form -> getAllValues($fields));
        $account -> autologin_key = ServicestrongRandomString(50);
        $account -> update();
        
        AccountssetSetting($account, "date_format", $form -> date_format);
        
        if($account -> photo)
        	if($photo = AccountsrotateUploadedImage($account -> photo))
        		$account -> setValue("photo", $photo) -> update();

        $_SESSION['account']['login'] = $form -> login;
        $_SESSION['account']['token'] == $mv -> accounts -> generateSessionToken($account);

        if(isset($_COOKIE['autologin_key'], $_COOKIE['autologin_token']))
	        $mv -> accounts -> remember($account);
		
        $_SESSION["account"]["message-success"] = I18nlocale("done-update");
        $mv -> reload();
    }
}
else if($format = AccountsgetSetting($account, "date_format"))
    $form -> date_format = $format;

include $mv -> views_path."main-header.php";
?>
   <div id="content">
        <h1><?php echo I18nlocale("profile"); ?></h1>
        <form class="regular" method="post" action="<?php echo $mv -> root_path; ?>profile" enctype="multipart/form-data">
	        <?php
	        	echo $form -> displayErrors();
	        	echo $mv -> accounts -> displayReloadMessage();
	        	echo $form -> displayVertical($fields);
	        ?>
            <div class="form-buttons clearfix high">
                <?php echo $form -> displayTokenCSRF(); ?>
                <input class="button big submit" type="button" value="<?php echo I18nlocale("update"); ?>" />
                <a class="cancel" href="<?php echo $mv -> root_path; ?>home"><?php echo I18nlocale("cancel"); ?></a>
                <a class="link-button" href="<?php echo $mv -> root_path; ?>password/"><?php echo I18nlocale("change-password"); ?></a>
            </div>
        </form>
   </div>
<?php
include $mv -> views_path."main-footer.php";
?>