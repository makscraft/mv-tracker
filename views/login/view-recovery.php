<?php
if($account)
	$mv -> redirect("home");
	
$mv -> accounts -> dropAutoLogin();
	
if(isset($_GET["token"], $_GET["hash"], $_GET["time"]) && $_GET["token"] && $_GET["hash"] && $_GET["time"])
	if($account = $mv -> accounts -> checkNewPasswordParams($_GET["token"], $_GET["hash"], $_GET["time"]))
	{
		$token = $mv -> registry -> getSetting("SecretCode").$account -> id.$account -> autologin_key;
		$token .= $_SERVER["HTTP_USER_AGENT"];
			
		$_SESSION["account"]["recover"]["id"] = $account -> id;
		$_SESSION["account"]["recover"]["token"] = md5($token);
					
		$mv -> redirect("recovery/create");
	}
	else
		$mv -> reload("?wrong-token");

$fields = array(array("Email", "email", "email", array("required" => true)),
            	array("{captcha}", "char", "captcha", array("captcha" => "extra/captcha-simple", "required" => true)));

$form = new Form($fields);
$form -> setHtmlParams("email", 'placeholder="Email"');
$form -> setHtmlParams("captcha", 'placeholder="'.I18n :: locale("captcha").'"');
$form -> useTokenCSRF() -> useJqueryToken();

if(Http :: isPostRequest())
{
    $form -> submit() -> validate(array("email", "captcha")); 

    if($form -> captcha && $_SESSION["captcha"] != $form -> captcha)
    	$form -> addError(I18n :: locale("wrong-captcha"), "captcha");
         
    if($form -> isSubmitted() && $form -> isValid())
		if(!$mv -> accounts -> countRecords(array("email" => $form -> email, "active" => 1)))
			$form -> addError(I18n :: locale("email-wrong-blocked"), "email");
	
    if(!$form -> hasErrors())
    {
    	if(!$mv -> registry -> getSetting("DemoMode"))
	    	$mv -> accounts -> sendPasswordRecoveryLink($form -> email);
	    
        $mv -> reload("?done");
    }
}

include $mv -> views_path."main-header.php";
?>
	<div id="content">
		<div class="registration-block recovery">
			<h1><?php echo I18n :: locale("password-restore"); ?></h1>
			<p><?php echo I18n :: locale("enter-account-email"); ?></p>
            <?php 
	            if(isset($_GET['done']))
	            	echo "<div class=\"success\"><p>".I18n :: locale("change-password-sent")."</p></div>\n";
	            else if(isset($_GET['wrong-token']))
	            	echo "<div class=\"form-errors\"><p>".I18n :: locale("password-not-confirmed")."</p></div>\n";

	            if(!isset($_GET['done'])):
            ?>
            <?php echo $form -> displayErrors(); ?>
            <form class="regular" method="post" action="<?php echo $mv -> root_path; ?>recovery">
                <?php echo $form -> displayVertical(array("email", "captcha")); ?>
	            <div class="form-buttons clearfix">
                   <?php echo $form -> displayTokenCSFR(); ?>
	               <input type="submit" value="<?php echo I18n :: locale("restore"); ?>" class="button big"/>
	            </div>
	            <div class="clear"></div>
            </form>
            <?php endif; ?>
		</div>
		<div class="clear"></div>
	</div>
<?php
include $mv -> views_path."main-footer.php";
?>