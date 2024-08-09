<?
$form = new Form("Accounts", $account -> id);
$form -> loadRecord();

$form -> addRule("login", "unique", true, "{login-occupied}");
$form -> addRule("email", "unique", true, "{email-occupied}");

$formats = ["dd/mm/yyyy" => "dd/mm/yyyy", "mm/dd/yyyy" => "mm/dd/yyyy", "dd.mm.yyyy" => "dd.mm.yyyy"];
$form -> addField(["{date-format}", "enum", "date_format", ["values_list" => $formats]]);

$form -> useTokenCSRF();

$fields = array("photo", "name", "email", "login", "phone", "date_format", "send_emails");

if(Http :: isPostRequest())
{
	$form -> submit() -> validate($fields);

    if($form -> isSubmitted() && $form -> isValid())
    {
    	$account -> setValues($form -> getAllValues($fields));
        $account -> autologin_key = Service :: strongRandomString(50);
        $account -> update();
        
        Accounts :: setSetting($account, "date_format", $form -> date_format);
        
        if($account -> photo)
        	if($photo = Accounts :: rotateUploadedImage($account -> photo))
        		$account -> setValue("photo", $photo) -> update();

        $_SESSION['account']['login'] = $form -> login;
        $_SESSION['account']['token'] == $mv -> accounts -> generateSessionToken($account);

        if(isset($_COOKIE['autologin_key'], $_COOKIE['autologin_token']))
	        $mv -> accounts -> remember($account);
		
        $_SESSION["account"]["message-success"] = I18n :: locale("done-update");
        $mv -> reload();
    }
}
else if($format = Accounts :: getSetting($account, "date_format"))
    $form -> date_format = $format;

include $mv -> views_path."main-header.php";
?>
   <div id="content">
        <h1><? echo I18n :: locale("profile"); ?></h1>
        <form class="regular" method="post" action="<? echo $mv -> root_path; ?>profile" enctype="multipart/form-data">
	        <?
	        	echo $form -> displayErrors();
	        	echo $mv -> accounts -> displayReloadMessage();
	        	echo $form -> displayVertical($fields);
	        ?>
            <div class="form-buttons clearfix high">
                <? echo $form -> displayTokenCSRF(); ?>
                <input class="button big submit" type="button" value="<? echo I18n :: locale("update"); ?>" />
                <a class="cancel" href="<? echo $mv -> root_path; ?>home"><? echo I18n :: locale("cancel"); ?></a>
                <a class="link-button" href="<? echo $mv -> root_path; ?>password/"><? echo I18n :: locale("change-password"); ?></a>
            </div>
        </form>
   </div>
<?
include $mv -> views_path."main-footer.php";
?>