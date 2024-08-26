<?php
if($account && isset($_GET['logout']) && $_GET['logout'] == $mv -> accounts -> generateLogoutToken($account))
{
	$mv -> accounts -> logout();
	$mv -> reload();
}

if($account)
	$mv -> redirect("home");

$mv -> accounts -> dropAutoLogin();

$form = new Form("Accounts");

$form -> addRule("login", "required", true, I18n :: locale("complete-login"));
$form -> addRule("password", "required", true, I18n :: locale("complete-password"));
$form -> removeRule("password", "min_length") -> removeRule("password", "digits_required");
$form -> removeRule("password", "letters_required") -> removeRule("login", "unique");

$form -> addField(array("{remember-login}", "bool", "remember_me", array("html_params" => 'id="remember_me"')));

$form -> setHtmlParams("login", 'placeholder="'.I18n :: locale("login").'"');
$form -> setHtmlParams("password", 'placeholder="'.I18n :: locale("password").'"');

$form -> useTokenCSRF() -> useJqueryToken();
$after_login_path = "";
$login_done = false;

if(Http :: isPostRequest())
{
	$form -> submit() -> validate(array("login", "password"));
   	
   	if($form -> isSubmitted() && $form -> isValid())
   		if(!$account = $mv -> accounts -> login($form -> login, $form -> password))
   			$form -> addError(I18n :: locale("login-failed"));
   		else
   		{
   			if($form -> remember_me)
   			{
   				if(!$account -> autologin_key)
   				{
   					$account -> autologin_key = Service :: strongRandomString(50);
   					$account -> update();
   				}
   				
   				$mv -> accounts -> remember($account);
   			}
   			
   			if(!$account -> date_registration || $account -> date_registration == "0000-00-00 00:00:00")
   			{
   				$account -> date_registration = I18n :: getCurrentDateTime();
   				$account -> update();
   			}
   			
   			$login_done = true;
   			$after_login_path = isset($_SESSION["login-back-path"]) ? $_SESSION["login-back-path"] : "home";
   			unset($_SESSION["login-back-path"]);
   		}
   		
	$form -> password = "";
}
else
	$form -> remember_me = 1;

$hide_login_form = ($form -> isValid() && $login_done);

include $mv -> views_path."main-header.php";
?>
   <div id="content">
      <?php if(!$hide_login_form): ?>
      <div class="registration-block">
         <h1><?php echo I18n :: locale("authorization"); ?></h1>
         <?php 
         	echo $form -> displayErrors();
         	
         	if(isset($_GET['recovered']))
         		echo "<div class=\"success\"><p>".I18n :: locale("password-confirmed")."</p></div>";
         ?>
         <form class="regular" method="post" action="<?php echo $mv -> root_path; ?>login">
            <?php echo $form -> displayVertical(array("login", "password")); ?>
            <p class="remember-me">
            	<?php echo $form -> displayFieldHtml("remember_me"); ?>
                <label for="remember_me"><?php echo I18n :: locale("remember-login"); ?></label>
            </p>
            <div class="form-buttons clearfix">
               <?php echo $form -> displayTokenCSFR(); ?>
               <input type="submit" value="<?php echo I18n :: locale("login-action"); ?>" class="button big"/>
            </div>
            <div class="clear"></div>
            <p class="recovery">
               <a href="<?php echo $mv -> root_path; ?>recovery"><?php echo I18n :: locale("fogot-password"); ?></a>
            </p>
         </form>
      </div>
      <div class="clear"></div>
      <?php endif; ?>
   </div>
<?php
include $mv -> views_path."main-footer.php";
?>