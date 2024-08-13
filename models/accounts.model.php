<?php
class Accounts extends Model
{
	protected $name = "{accounts}";
   
	private $auto_login_days = 90;
   
	protected $model_elements = array(
		array("{active}", "bool", "active", array("on_create" => true)),
		array("{receive-emails}", "bool", "send_emails", array("on_create" => true)),
		array("{date-registered}", "date_time", "date_registration"),
		array("{date-last-visit}", "date_time", "date_last_visit", array("now_on_create" => false)),
		array("{name}", "char", "name", array("required" => true)),
		array("{login}", "char", "login", array("required" => true, "unique" => true)),
		array("Email", "email", "email", array("required" => true, "unique" => true)),
		array("{password}", "password", "password", array("required" => true, "letters_required" => true,
													  	  "digits_required" => true)),		
		array("Password token", "char", "password_token"),
		array("Autologin key", "char", "autologin_key"),
		array("{phone}", "phone", "phone"),
		array("{photo}", "image", "photo"),
		array("Settings", "text", "settings"),
   );
   
	protected $model_display_params = array(
		"not_editable_fields" => array("date_registration", "date_last_visit"),
		"hidden_fields" => array("password_token", "autologin_key", "settings")
	);
	
	public function beforeCreate($fields)
	{
		$salt = $this -> registry -> getSetting("SecretCode");
		return array("password" => Service :: makeHash($fields["password"].$salt));
	}
	
	public function beforeUpdate($id, $old_fields, $new_fields)
	{
		if($new_fields["password"] != $old_fields["password"])
		{
			$salt = $this -> registry -> getSetting("SecretCode");
			return array("password" => Service :: makeHash($new_fields["password"].$salt));
		}
	}
		
	public function generateSessionToken($account)
	{
		$token = $account -> id.$this -> registry -> getSetting('SecretCode');
		$token .= Debug :: browser().session_id();
		
		return md5($token);
	}
	
	static public function generateActionToken($account)
	{
		$token = $_SERVER["HTTP_USER_AGENT"].Registry :: instance() -> getSetting('SecretCode').$account -> id;
		$token .= $_SESSION["account"]["token"].$_SERVER["REMOTE_ADDR"];
		
		return md5($token);
	}
	
	public function generatePasswordRecoveryString($account, $token, $time)
	{
		$string = $token.$account -> email.$account -> password.$account -> id;
		$string .= $this -> registry -> getSetting("SecretCode").$time;
		
		return $string;
	}
	
	public function generateLogoutToken($account)
	{
		return md5($account -> id.$_SESSION["account"]["token"].$_SERVER["HTTP_USER_AGENT"]);
	}
	      
	public function login($login, $password, $autologin = false)
	{
		$account = $this -> findRecord(array("login" => $login, "active" => 1));
		$salt = $this -> registry -> getSetting("SecretCode");
      
		if($account && ($autologin || Service :: checkHash($password.$salt, $account -> password)))
		{
			$_SESSION["account"]["id"] = $account -> id;
			$_SESSION["account"]["password"] = md5($account -> password);
			$_SESSION["account"]["token"] = $this -> generateSessionToken($account);
			
			$account -> date_last_visit = I18n :: getCurrentDateTime();
			$account -> update();         
			
			return $account;
		}
	}
   
	public function checkAuthorization()
	{
		if(isset($_SESSION["account"]["id"], $_SESSION["account"]["password"], $_SESSION["account"]["token"]))
		{
			$account = $this -> findRecordById($_SESSION["account"]["id"]);
			
			if($account && $account -> active && $_SESSION["account"]["password"] == md5($account -> password))
				if($_SESSION["account"]["token"] == $this -> generateSessionToken($account))
					return $account;
		}
	}
   
	public function logout()
	{
		unset($_SESSION["account"]);
		
		return $this -> dropAutoLogin();
	}
   
	public function remember($account)
	{
		$key = $account -> autologin_key;
		$token = Service :: makeHash(Debug :: browser().$account -> password.$account -> email, 12);
		
		setcookie("autologin_key", $key, time() + 86400 * $this -> auto_login_days, $this -> root_path, "", Router :: isHttps(), true);
		setcookie("autologin_token", $token, time() + 86400 * $this -> auto_login_days, $this -> root_path, "", Router :: isHttps(), true);
		
		return $this;
	}
   
	public function autoLogin($key, $token)
	{
		sleep(1);
		
		$account = $this -> findRecord(array("autologin_key" => $key, "active" => 1));
   
		if(!$account)
		{
			$this -> dropAutoLogin();
			return;
		}
   
		$string = Debug :: browser().$account -> password.$account -> email;
        
		if(Service :: checkHash($string, $token))
		{
			$this -> remember($account);
			
			return  $this -> login($account -> login, $account -> password, true);
		}
		
		$this -> dropAutoLogin();
	}
   
	public function dropAutoLogin()
	{
		setcookie("autologin_key", "", time() + 86400 * $this -> auto_login_days, $this -> root_path, "", Router :: isHttps(), true);
		setcookie("autologin_token", "", time() + 86400 * $this -> auto_login_days, $this -> root_path, "", Router :: isHttps(), true);

		return $this;
	}
   
	public function sendPasswordRecoveryLink($email)
	{
		$account = $this -> findRecord(array("email" => $email, "active" => 1));
		
		if($account)
		{
			$token = Service :: strongRandomString(35);
			$time = time();
			$hash = Service :: makeHash($this -> generatePasswordRecoveryString($account, $token, $time));
			
			$url = $this -> registry -> getSetting("DomainName").$this -> root_path."recovery";
			$url .= "?token=".$token."&hash=".$hash."&time=".$time;
			
			$message = "<p>".I18n :: locale("hello").", ".$account -> name.",<br />\n";
			$message .= I18n :: locale("to-password-link")."<br /><br />\n";
			$message .= "<a href=\"".$url."\">".$url."</a></p>\n";
			
			$account -> password_token = $token;
			$account -> update();
			
			Email :: send($account -> email, I18n :: locale("password-restore"), $message);
			
			return true;
		}
	}
	
	public function checkNewPasswordParams($token, $hash, $time)
	{
		$account = $this -> findRecord(array("active" => 1, "password_token" => $token));
		$result = false;
		
		if(!$account)
			return false;
			
		$string = $this -> generatePasswordRecoveryString($account, $token, $time);
			
		if($account && Service :: checkHash($string, $hash))
		{
			if(time() - $time > 3600 * 24)
				return false;
					
			$account -> password_token = "";
			$account -> autologin_key = Service :: strongRandomString(50);
			$account -> update();
				
			return $account;
		}
			
		return false;
	}
      
	public function displayAccountMenu(Router $router)
	{
		$html = "";      
		$url_parts = $router -> getUrlParts();

		$pathes = array("home" => I18n :: locale("tasks"),
						"projects" => I18n :: locale("projects"),
						"documentation" => I18n :: locale("documentation"),
						"history" => I18n :: locale("history"));

		foreach($pathes as $url => $title)
		{
			$class = "";
			
			if($url == "home" && ($url_parts[0] == "home" || $url_parts[0] == "tasks" || $url_parts[0] == "task"))
				$class = " class=\"active\"";
			else if($url == "projects" && ($url_parts[0] == "projects" || $url_parts[0] == "project"))
				$class = " class=\"active\"";
			else if($url == "documentation" && $url_parts[0] == "documentation")
				$class = " class=\"active\"";
			else if($url == $url_parts[0]."")
				$class = " class=\"active\"";

			$tasks_css = $url == "home" ? " class=\"tasks\"" : "";
			
			$html .= "<li".$class."><a".$tasks_css." href=\"".$this -> root_path.$url."\">".$title."</a></li>\n";
		}

		return $html;
	}
	
	public function displayAvatar($data)
	{
		$person = false;
				
		if(is_object($data) && $data -> photo)
			$person = $data;
		else if(is_object($data) && $data -> author)
			$person = $this -> findRecord(array("id" => $data -> author, "active" => 1));
		else if(is_array($data) && isset($data["account"]) && $data["account"])
			$person = $this -> findRecord(array("id" => $data["account"], "active" => 1));
		
		if($person && $person -> photo)
			return $this -> cropImage($person -> photo, 30, 30);
		
		return "<img src=\"".$this -> root_path."media/images/avatar.png\" />\n";
	}
	
	public function displayReloadMessage()
	{
		$html = "";
		
		if(isset($_SESSION["account"]["message-success"]) && $_SESSION["account"]["message-success"])
			$html .= "<div class=\"success\"><p>".$_SESSION["account"]["message-success"]."</p></div>\n";
		
		if(isset($_SESSION["account"]["message-error"]) && $_SESSION["account"]["message-error"])
			$html .= "<div class=\"form-errors\"><p>".$_SESSION["account"]["message-error"]."</p></div>\n";
		
		unset($_SESSION["account"]["message-success"], $_SESSION["account"]["message-error"]);
		
		return $html;
	}
	
	static public function rotateUploadedImage($image)
	{
		$image = Service :: addFileRoot($image);
		
		if(!is_file($image))
			return;
			
		$extension = Service :: getExtension($image);
		$exif = @exif_read_data($image);
		$result = "";
		$rotate = false;
		
		if(isset($exif['Orientation']) && $exif['Orientation'] != 1)
		{
			if($exif['Orientation'] == 3)
				$rotate = 180;
			else if($exif['Orientation'] == 6)
				$rotate = 270;
			else if($exif['Orientation'] == 8)
				$rotate = 90;
		}
		else
			return Service :: removeFileRoot($image);
			
		if(!$rotate)
			return Service :: removeFileRoot($image);
			
		$directory = dirname($image)."/";
		$image_name = Service :: removeExtension(basename($image));
		
		if($extension == "jpg" || $extension == "jpeg")
		{
			$result = imagerotate(imagecreatefromjpeg($image), $rotate, 0);
			$image_name = $directory.$image_name.".jpg";
			imagejpeg($result, $image_name, 90);
		}
		else if($extension == "png")
		{
			$result = imagerotate(imagecreatefrompng($image), $rotate, 0);
			$image_name = $directory.$image_name.".png";
			imagepng($result, $image_name);
		}
		
		return Service :: removeFileRoot($image_name);
	}
	
	static public function getSetting($account, $key)
	{
		$settings = Service :: unserializeArray($account -> settings);
		
		if(isset($settings[$key]))
			return $settings[$key];
	}
	
	static public function setSetting($account, $key, $value)
	{
		$settings = Service :: unserializeArray($account -> settings);
		
		$settings[$key] = $value;
		$account -> settings = Service :: serializeArray($settings);
		$account -> update();
	}
}