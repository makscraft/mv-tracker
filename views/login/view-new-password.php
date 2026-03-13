<?php
if(!isset($_SESSION["account"]["recover"]["id"], $_SESSION["account"]["recover"]["token"]))
	$mv -> redirect("/login");

$account = $mv -> accounts -> findRecord(array("active" => 1, "id" => $_SESSION["account"]["recover"]["id"]));

if(!$account)
	$mv -> redirect("/login");

$token = $mv -> registry -> getSetting("SecretCode").$account -> id.$account -> autologin_key;
$token .= $_SERVER["HTTP_USER_AGENT"];

if(md5($token) != $_SESSION["account"]["recover"]["token"])
	$mv -> redirect("/login");

$fields = array(array("{new-password}", "password", "password", array("required" => true,
																  "letters_required" => true,
																  "digits_required" => true)),
				array("{password-repeat}", "password", "password_repeat", array("required" => true,
																			"letters_required" => true,
																			"digits_required" => true,
																			"must_match" => "password")));

$form = new Form($fields);
$form -> setHtmlParams("password", 'placeholder="'.I18n :: locale("new-password").'"');
$form -> setHtmlParams("password_repeat", 'placeholder="'.I18n :: locale("password-repeat").'"');
$form -> useTokenCSRF() -> useJqueryToken();

if(Http :: isPostRequest())
{
	$form -> submit() -> validate();
	
	if($form -> isSubmitted() && $form -> isValid())
	{
		$salt = $mv -> registry -> getSetting("SecretCode");
		$account -> password = Service :: makeHash($form -> password.$salt);
		$account -> update();
		
		unset($_SESSION["account"]["recover"]);
		
		$mv -> redirect("/login?recovered");
	}
}

$account = false;
include $mv -> views_path."main-header.php";
?>
   <div id="content">
      <div class="registration-block recovery">
         <h1><?php echo I18n :: locale("password-restore"); ?></h1>
            <?php echo $form -> displayErrors(); ?>
            <form class="regular" method="post" action="<?php echo $mv -> root_path; ?>recovery/create">
                  <?php echo $form -> displayVertical(); ?>
               <div class="form-buttons clearfix">
                   <?php echo $form -> displayTokenCSRF(); ?>
                   <input type="submit" value="<?php echo I18n :: locale("save"); ?>" class="button big"/>
               </div>
               <div class="clear"></div>
            </form>
      </div>
      <div class="clear"></div>
   </div>
<?php
include $mv -> views_path."main-footer.php";
?>