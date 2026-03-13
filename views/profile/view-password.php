<?php
$fields = array(array("{new-password}", "password", "password", array("required" => true,
																	"letters_required" => true,
																    "digits_required" => true)),
				
    			array("{password-repeat}", "password", "repeat_password", array("required" => true,
        																		"letters_required" => true,
        																		"digits_required" => true,
        																		"must_match" => "password")));

$form = new Form($fields);
$form -> useTokenCSRF();

if(Http :: isPostRequest())
{
    $form -> submit() -> validate(array("password", "repeat_password"));

    if($form -> isSubmitted() && $form -> isValid())
    {
        $salt = $mv -> registry -> getSetting("SecretCode");

        $account -> password = Service :: makeHash($form -> password.$salt);
        $account -> autologin_key = Service :: strongRandomString(50);
        $account -> update();

        $_SESSION['account']['password'] = md5($account -> password);

        if(isset($_COOKIE['autologin_key'], $_COOKIE['autologin_token']))
	        $mv -> accounts -> remember($account);

        $_SESSION["account"]["message-success"] = I18n :: locale("done-update");
        $mv -> reload();
    }
}

include $mv -> views_path."main-header.php";
?>
    <div id="content">
        <h1><?php echo I18n :: locale("change-password"); ?></h1>
        <form class="regular" method="post" action="<?php echo $mv -> root_path; ?>password/">
           <?php
               echo $form -> displayErrors();
           	   echo $mv -> accounts -> displayReloadMessage();
           	   echo $form -> displayVertical(array("password", "repeat_password"));
           ?>
           <div class="form-buttons clearfix">
                <?php echo $form -> displayTokenCSRF(); ?>
                <input class="button big submit" type="button" value="<?php echo I18n :: locale("update"); ?>" />
                <a class="cancel" href="<?php echo $mv -> root_path; ?>profile"><?php echo I18n :: locale("cancel"); ?></a>
           </div>
        </form>
    </div>
<?php
include $mv -> views_path."main-footer.php";
?>