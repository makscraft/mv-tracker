<?php
class Applications extends Plugin
{
	private $token;
	
	private $methods = array("checkApi", "loginUser", "getTasks", "getTaskData", "getHistory", "getProfileData", 
							 "commentTask", "updateProfileData", "getAccountsList");
	
	private $account;
	
	public function __construct()
	{
		parent :: __construct();
		
		$this -> token = $this -> registry -> getSetting("SecretCode")."~!@#$%^&*()";
	}
	
	public function checkMethod($method)
	{
		return (in_array($method, $this -> methods) && method_exists($this, $method));
	}
	
	public function processDataTypes($data)
	{
		foreach($data as $index => $row)
			foreach($row as $key => $value)
				if($key == "id")
					$data[$index][$key] = intval($value);
				else if($key == "name" || $key == "email" || $key == "login")
					$data[$index][$key] = htmlspecialchars_decode($value, ENT_QUOTES);
				else if($key == "send_emails")
					$data[$index][$key] = (bool) $value;

		return $data;
	}
	
	private function generateAccountToken($account)
	{
		return md5($account -> id.$this -> token.$account -> password.$this -> token);
	}
	
	public function loginUser($params)
	{
		$fields = array(array("Логин", "char", "login", array("required" => true)),
						array("Пароль", "password", "password", array("required" => true)));
		
		$form = new Form($fields);
		$form -> getDataFromArray($params) -> validate(array("login", "password"));
		
		if($form -> isValid())
		{
			$accounts = new Accounts();
			$account = $accounts-> findRecord(array("login" => $form -> login, "active" => 1));
			$salt = $this -> registry -> getSetting("SecretCode");
			
			if($account && Service :: checkHash($form -> password.$salt, $account -> password))
			{
				$account -> date_last_visit = I18n :: getCurrentDateTime();
				$account -> update();
				
				$data = array("id" => intval($account -> id), "name" => $account -> name, 
							  "account_token" => $this -> generateAccountToken($account));
				
				return array("data" => $data);
				
				
				return $account;
			}
			else
				$form -> addError("Неверный логин или пароль.");
		}
		
		return array("error" => trim(strip_tags($form -> displayErrors())));
	}
	
	public function checkUser($params)
	{
		if(!isset($params["account_id"]) || !$params["account_id"])
			return "Не передан параметр account_id.";
		
		$accounts = new Accounts();
		
		if(!$account = $accounts -> findRecord(array("id" => $params["account_id"], "active" => 1)))
			return "Пользователь не найден.";
				
		if(!isset($params["account_token"]) || !$params["account_token"])
			return "Не передан параметр account_token.";
		else if($params["account_token"] != $this -> generateAccountToken($account))
			return "Не верный параметр account_token.";
		
		$this -> account = $account;
	}
	
	public function checkTask($params)
	{
		if(!isset($params["task_id"]) || !intval($params["task_id"]))
			return "Не передан параметр task_id.";
			
		$tasks = new Tasks();
		$task = $tasks -> findRecordById($params["task_id"]);
			
		if(!$task)
			return "Данная задача не найдена.";
		
		return $task;
	}
	
	public function getTasks($params)
	{
		$statuses = new Statuses();
		$projects = new Projects();
		$tasks = new Tasks();
		
		$open = $statuses -> getOpenStatusesIds();
		$projects = $projects -> getActiveProjectsIds();
		$params = array("assigned_to" => $this -> account -> id, "status->in" => $open, 
						"project->in" => $projects, "fields->" => "id,name,project,status,priority,tracker,complete");
		
		$data = $tasks -> select($params);
		
		foreach($data as $index => $row)
		{
			foreach(array("status", "project", "priority", "tracker") as $property)
				$data[$index][$property] = $tasks -> getEnumTitle($property, $row[$property]);
			
			$data[$index]["complete"] = intval($data[$index]["complete"]);
		}
			
		return array("data" => $this -> processDataTypes(array_values($data)));
	}
	
	public function getTaskData($params)
	{
		$task = $this -> checkTask($params);
		
		if(!is_object($task))
			return array("error" => $task);
		
		$data = $task -> getValues();
		$tasks = new Tasks();
		
		foreach(array("status", "project", "priority", "tracker") as $property)
			$data[$property] = $tasks -> getEnumTitle($property, $data[$property]);
		
		$data["comments"] = array();
		
		$journal = new Journal();
		$accounts = new Accounts();
		$rows = $journal -> displayTaskHistory($task, false, true);
		
		if(count($rows) > 1)
		{
			unset($rows[0]);
			
			foreach($rows as $key => $row)
			{
				
				$rows[$key]["user_photo"] = $this -> getUserPhoto($row);
				
				if($rows[$key]["files"])
				{
					$rows[$key]["files"] = explode("-*//*-", $rows[$key]["files"]);
					$rows[$key]["id"] = intval($rows[$key]["id"]);
					
					foreach($rows[$key]["files"] as $index => $file)
						$rows[$key]["files"][$index] = Service :: setFullHttpPath($file);
				}
					
				$rows[$key]["user_name"] = $journal -> getEnumTitle("account", $row["account"]);
				unset($rows[$key]["task"], $rows[$key]["account"]);
				
				$data["comments"][] = $rows[$key];
			}			
		}
		
		return array("data" => $data);
	}
	
	private function getUserPhoto($row)
	{
		$accounts = new Accounts();
		
		if($avatar = $accounts -> displayAvatar($row))
		{
			$avatar = preg_replace("/.*\ssrc=.([^\s]+).\s.*/i", "$1", trim($avatar));
			$avatar = Service :: removeRootPath($avatar);
			return Service :: setFullHttpPath($avatar);
		}
	}
	
	public function getHistory($params)
	{
		if(!isset($params["page"]) || !intval($params["page"]))
			return array("error" => "Не передан параметр page.");
		
		$journal = new Journal();
		$accounts = new Accounts();
		$data = array();
		
		$total = $journal -> countRecords(array("extra->" => "(`content`!='' OR `title`!='' OR `files`!='')"));
		$journal -> runPager($total, 10, intval($params["page"]));
		
		$rows = $journal -> select(array("order->desc" => "date", "fields->" => "account,task,content,date,title",
										 "extra->" => "(`content`!='' OR `title`!='' OR `files`!='')",
										 "limit->" => $journal -> pager -> getParamsForSelect()));
		
		foreach($rows as $key => $row)
		{
			$rows[$key]["user_photo"] = $this -> getUserPhoto($row);
			$rows[$key]["user_name"] = $journal -> getEnumTitle("account", $row["account"]);
			$rows[$key]["task"] = $journal -> getEnumTitle("task", $row["task"]);
			unset($rows[$key]["id"], $rows[$key]["account"]);
			
			$data[] = $rows[$key];
		}
		
		return array("data" => $data);
	}
	
	public function getProfileData($params)
	{
		$accounts = new Accounts();
		$account = $accounts -> findRecordById($params["account_id"]);
		
		$data = array("name" => $account-> name, "email" => $account -> email, "login" => $account -> login,
					  "phone" => $account-> phone, "send_emails" => $account -> send_emails ? true : false,
					  "photo" => Service :: setFullHttpPath($account -> photo));
		
		return array("data" => $data);
	}
	
	public function updateProfileData($params)
	{
		$accounts = new Accounts();
		$account = $accounts -> findRecordById($params["account_id"]);
		
		$fields = array("name", "email", "login", "phone", "send_emails");
		
		$form = new Form("Accounts", $account -> id);
		$form -> addRule("email", "unique", true, "Данный email уже зарегистрирован.");
		$form -> addRule("login", "unique", true, "Данный логин уже зарегистрирован.");
		
		$form -> getDataFromArray($params) -> validate($fields);
		
		if($form -> hasErrors())
			return array("error" => str_replace("\n", " ", trim(strip_tags($form -> displayErrors()))));
		else
		{
			$account -> setValues($form -> getAllValues($fields)) -> update();
			return $this -> getProfileData($params);
		}
	}
	
	public function getAccountsList()
	{
		$data = array();
		$accounts = new Accounts();
		
		foreach($accounts -> select(array("active" => 1)) as $row)
			$data[] = array("id" => intval($row["id"]), "name" => $row["name"]);
		
		return array("data" => $data);
	}
	
	public function commentTask($params)
	{
		$task = $this -> checkTask($params);
		$tasks = new Tasks();
		$accounts = new Accounts();
		$action_comment = "";
		
		if(!is_object($task))
			return array("error" => $task);
		
		if(!isset($params["comment"], $params["assigned_to"]) || ($params["comment"] == "" && !$params["assigned_to"]))
			return array("error" => "Не переданы параметры comment и assigned_to.");
		
		if($params["assigned_to"] && $params["assigned_to"] != $task -> assigned_to)
		{
			$assigned_to = $accounts -> findRecord(array("id" => $params["assigned_to"], "active" => 1));
			
			if(!$assigned_to)
				return;
			
			$old_data = $new_data = $task -> getValues();
			$task -> assigned_to = $new_data["assigned_to"] = $assigned_to -> id;
			$action_comment = $tasks -> createActionComment($new_data, $old_data);
			$task -> update();
		}
		
		if($task -> assigned_to != $this -> account -> id)
		{
			$assigned_to = $accounts -> findRecordById($task -> assigned_to);
			$task -> date_due = I18n :: formatDate($task -> date_due);
			
			Journal :: sendEmail($assigned_to, $task, $params["comment"]);
		}
		
		$journal = new Journal();
		$comment_id = $journal -> add($this -> account, $task, $action_comment, $params["comment"], "");
		
		if($comment_id)
			return array("data" => array("comment_id" => intval($comment_id)));
	}
}
?>