<?
class Journal extends Model
{
	protected $name = "{journal}";
	
	protected $model_elements = array(
		array("{task}", "enum", "task", array("required" => true, "foreign_key" => "Tasks", 
											  "long_list" => true)),
		array("{account}", "enum", "account", array("required" => true, "foreign_key" => "Accounts")),
		array("{date}", "date_time", "date"),
		array("{operations}", "text", "title", array("show_in_admin" => 70)),
		array("{content}", "text", "content", array("show_in_admin" => 70)),
		array("{attached-files}", "text", "files", array("show_in_admin" => 70, "rich_text" => true))
	);
	
	public function displayFullHistory()
	{
		$html = "";
		$rows = $this -> select(array("order->desc" => "date",
									  "extra->" => "(`content`!='' OR `title`!='' OR `files`!='')",
									  "limit->" => $this -> pager -> getParamsForSelect()));
		
		$accounts_model = new Accounts();
		
		foreach($rows as $row)
		{
			$task = $this -> findRecord(array("table->" => "tasks", "id" => $row["task"]));
			$mass_action = preg_match("/^[\d\,]+$/", $row["title"]);
			
			if(!$task && !$mass_action)
				continue;
			
			if(!$mass_action && ($task -> date_created == $row["date"] || $row["title"] == "created"))
			{
				$row["content"] = $task -> description;
				$row["title"] = ($row["title"] == "created") ? "" : $row["title"];
			}
			
			$html .= "<div class=\"section\">\n";
			$html .= "<div class=\"author\"><span class=\"account-image\">";
			$html .= $accounts_model -> displayAvatar($row)."</span>\n";
			
			if($name = $this -> getEnumTitle("account", $row["account"]))
				$html .= "<span class=\"name\">".$name."</span>\n";
			
			$html .= "<span class=\"date\">".Tasks :: processDateTimeValue($row["date"])."</span></div>\n";
            
			if($task)
			{
				$html .= "<div class=\"task\"><a href=\"".$this -> root_path."task/".$task -> id."\">";
				$html .= $task -> name."</a></div>\n";
				$html .= "<div class=\"project\"><span>".I18n :: locale("project").": </span>";
				$html .= "<a href=\"".$this -> root_path."project/".$task -> project."\">";
				$html .= $task -> getEnumTitle("project")."</a></div>\n";
			}
			else if($mass_action)
			{
				$links = array();
				
				foreach(explode(",", $row["title"]) as $task_id)
					$links[] = "<a href=\"".$this -> root_path."task/".$task_id."\">#".$task_id."</a>";
				
				$html .= "<div class=\"task\">".I18n :: locale("tasks")." ".implode(", ", $links)."</div>\n";
				$row["title"] = $row["content"];
				$row["content"] = "";
			}
			
			if($row["title"])
				$html .= "<div class=\"actions\">".$row["title"]."</div>\n";

			if($row["content"])
			{
				$row["content"] = Service :: cutText($row["content"], 300, "...");
				
				$html .= "<div class=\"content\"><div class=\"text\">";
				$html .= Tasks :: processDescriptionText($row["content"])."</div>\n</div>\n";
			}
			
			$html .= $this -> displayFiles($row["files"])."</div>\n";
		}
		
		return $html;
	}
	
	public function displayFiles($files, $account_id = false, $type = false)
	{
		$html = "";
		$files = MultiImagesModelElement :: unpackValue($files);
		
		if(!is_array($files) || !count($files))
			return;
		
		$root = $this -> registry -> getSetting("IncludePath");
		$html .= "<ul class=\"files\">\n";
			
		foreach($files as $file)
			if(is_file($root.$file['image']))
			{
				$file = $file['image'];

				if($account_id && $type)
				{
					$delete = "<span class=\"delete\" id=\"".$type."-";
					$delete .= self :: generateFileDeleteToken($account_id, basename($file))."\">".I18n :: locale("delete")."</span>\n";
				}
				else
					$delete = "";
					
				$html .= "<li><a target=\"blank\" href=\"".$this -> root_path.$file."\"><span>".basename($file);
				$html .= "</span>".$delete."</a></li>\n";
			}
					
		$html .= "</ul>\n";
		
		return $html;
	}
	
	public function displayTaskHistory($task, $account, $for_applications = false)
	{
		$html = "";
		
		$ids = array();
		$rows = $this -> select(array("fields->" => "id,title", "task" => 0, "title->like" => $task -> id));
		
		foreach($rows as $row)
			foreach(explode(",", $row["title"]) as $id)
			{
				$id = intval(trim($id));
				
				if($id == $task -> id)
					$ids[] = $row["id"];
			}
		
		$params = array("order->desc" => "date");
		
		if(!count($ids))
			$params["task"] = $task -> id;
		else
			$params["extra->"] = "`task`='".$task -> id."' OR `id` IN(".implode(",", $ids).")";
		
		$rows = $this -> select($params);

		if($for_applications == "count")
			return $rows;
		
		if($for_applications)
			return array_values($rows);
		
		$accounts_model = new Accounts();
		$skip_first = false;
		
		foreach($rows as $row)
		{
			/*
			if(!$skip_first)
			{
				$skip_first = true;
				
				if($row["title"] == "created" || strpos($row["title"], I18n :: locale("assigned-to").":") !== false)
				    continue;
			}
			*/
			
			if($row["content"] == "" && $row["files"] == "" && $row["title"] == "")
				continue;

			if($row["title"] == "created")
				continue;
			
			$account_id = ($row["account"] == $account -> id) ? $account -> id : false;
			
			if(preg_match("/^[\d\,]+$/", $row["title"]))
			{
				$row["title"] = $row["content"];
				$row["content"] = "";
			}
			
			$html .= "<div class=\"section\" id=\"comment-".$row["id"]."\">\n";
			$html .= "<div class=\"author\"><span class=\"account-image\">\n";
			$html .= $accounts_model -> displayAvatar($row)."</span>\n";
			
			if($name = $this -> getEnumTitle("account", $row["account"]))
				$html .= "<span class=\"name\">".$name."</span>\n";
			
			$html .= " <span class=\"date\">".Tasks :: processDateTimeValue($row["date"])."</span></div>\n";

			if($row["title"])
				$html .= "<div class=\"actions\">".$row["title"]."</div>\n";

			if($row["files"])
			{
				$css = $row["content"] ? " with-margin" : "";
				$html.= "<div class=\"files".$css."\"><span>".I18n :: locale("attached-files").":</span>";
            	$html .= $this -> displayFiles($row["files"], $account_id, "journal-".$row["id"])."</div>\n";
			}

			if($row["content"])
			{				
				$html .= "<div class=\"content\"><div class=\"text\">";
				$html .= Tasks :: processDescriptionText($row["content"])."</div>\n";
				
				if($account_id)
				{
					$token = $this -> generateCommentToken($account -> id, $row["id"]);
					$css_id = " id=\"comment-".$row["id"]."-".$row["task"]."-".$token."\"";
					
					$html .= "<div class=\"controls\"".$css_id."><span class=\"edit\">".I18n :: locale("edit")."</span>";
					$html .= "<span class=\"delete\">".I18n :: locale("delete")."</span></div>\n";
				}
				
				$html .= "</div>\n";
			}
			
			$html .= "</div>\n";
		}
		
		return $html;
	}
	
	static public function generateFileDeleteToken($account_id, $file_name)
	{
		return md5($account_id.$_SERVER["HTTP_USER_AGENT"].$file_name.$_SERVER["REMOTE_ADDR"]);
	}
	
	static public function generateCommentToken($account_id, $journal_id)
	{
		return md5($_SERVER["REMOTE_ADDR"].$journal_id.$account_id.$_SERVER["HTTP_USER_AGENT"].$_SERVER["REMOTE_ADDR"]);
	}
	
	static public function uploadFiles($field, $folder)
	{
		$registry = Registry :: instance();
		$allowed = $registry -> getSetting("AllowedFiles");
		$folder = $registry -> getSetting("FilesPath").$folder;
		$files = $copied = array();
		
		if(isset($_FILES[$field]))
			foreach($_FILES[$field] as $section => $data)
				foreach($data as $key => $value)
					$files[$key][$section] = $value;
				
		if(!is_dir($folder))
			@mkdir($folder);
		
		foreach($files as $file)
		{			
			$extension = Service :: getExtension($file['name']);
			
			if($extension == "jpeg")
				$extension = "jpg";
		
			if($file["error"] || !is_uploaded_file($file["tmp_name"]) || !in_array($extension, $allowed))
				continue;

			$name = Service :: removeExtension($file["name"]);
			$name = I18n :: translitUrl($name);
						
			$path = $folder.$name.".".$extension;
					
			if(is_file($path))
			{
				$counter = intval($registry -> getDatabaseSetting('files_counter')) + 1;
				$registry -> setDatabaseSetting('files_counter', $counter);
				
				$path = $folder.$name."-f".$counter.".".$extension;
			}
			
			move_uploaded_file($file["tmp_name"], $path);
			
			if(is_file($path))
				$copied[] = Service :: removeFileRoot($path);
		}
		
		return implode("-*//*-", $copied);
	}
	
	static public function deleteFiles($files)
	{
		if(!$files)
			return;
		
		$root = Registry :: instance() -> getSetting("IncludePath");
		$files = explode("-*//*-", $files);
		
		foreach($files as $file)
			if(is_file($root.$file))
				@unlink($root.$file);
	}
	
	public function deleteOneComment($account, $id, $token)
	{
		if($comment = $this -> findRecord(array("id" => $id, "account" => $account -> id)))
			if($this -> generateCommentToken($account -> id, $comment -> id) == $token)
			{
				$this -> delete($comment -> id);
				$_SESSION["account"]["message-success"] = I18n :: locale("comment-deleted");
				return true;
			}
			else
				$_SESSION["account"]["message-error"] = I18n :: locale("error-wrong-token");
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
	
	static public function sendEmail($account, $task, $text, $files = null)
	{
		if(!is_object($account) || !$account -> send_emails)
			return false;
		
		$url = Service :: setFullHttpPath("task/".$task -> id);
		
		$message = "<p><a style=\"background: #eaeaea; text-decoration: none; padding: 3px 10px; ";
		$message .= "border-radius: 3px;\" href=\"".$url."\">".I18n :: locale("go-to-task")."</a></p>\n";
		$message .= "<ul>\n<li>".I18n :: locale("author").": ".$task -> getEnumTitle("author")."</li>\n";
		$message .= "<li>".I18n :: locale("priority").": ".$task -> getEnumTitle("priority")."</li>\n";
		$message .= "<li>".I18n :: locale("tracker").": ".$task -> getEnumTitle("tracker")."</li>\n";
		$message .= "<li>".I18n :: locale("status").": ".$task -> getEnumTitle("status")."</li>\n";
		$message .= "<li>".I18n :: locale("assigned-to").": ".$task -> getEnumTitle("assigned_to")."</li>\n";
		
		if($task -> date_due && $task -> date_due != "0000-00-00")
		{
			$date = $task -> date_due;
			
			if(preg_match("/^\d{4}-\d{2}-\d{2}$/", $date))
				$date = I18n :: formatDate($date);
			
			$message .= "<li>".I18n :: locale("date-due").": ".$date."</li>\n";
		}
		
		if($task -> hours_estimated)
			$message .= "<li>".I18n :: locale("hours-estimated").": ".$task -> hours_estimated."</li>\n";
		
		if($task -> hours_spent)
			$message .= "<li>".I18n :: locale("hours-spent").": ".$task -> hours_spent."</li>\n";
		
		if($task -> complete)
			$message .= "<li>".I18n :: locale("implementation").": ".$task -> getEnumTitle("complete")."</li>\n";
		
		$message .= "</ul>\n";
		
		if($text != "")
		{
			$message .= "<p style=\"padding: 12px 15px; border-radius: 5px; background: #f6f5f5;\">";
			$message .= Tasks :: processDescriptionText($text)."</p>\n";
		}
		
		if($files)
		{
			$message .= "<p>".I18n :: locale("attached-files")."</p>\n<ul>\n";
			
			foreach(MultiImagesModelElement :: unpackValue($files) as $file)
			{
				$path = Service :: setFullHttpPath($file['image']);
				$message .= "<li><a target=\"_blank\" href=\"".$path."\">".basename($file['image'])."</a></li>\n";
			}
			
			$message .= "</ul>\n";
		}
		
		$subject = $task -> getEnumTitle("project")." #".$task -> id." ".$task -> name;
		$subject = str_replace("&quot;", '"', $subject);
		
		Email :: send($account -> email, $subject, $message);
	}
	
	static public function needsNotification($old_data, $new_data)
	{
		if($old_data["status"] != $new_data["status"])
		{
			$statuses = new Statuses();
			$status = $statuses -> findRecordById($new_data["status"]);
			
			if($status -> closed)
				return "closed";
		}
		
		if($old_data["project"] != $new_data["project"] || 
		   $old_data["description"] != $new_data["description"] || 
		   $old_data["assigned_to"] != $new_data["assigned_to"])
			return true;
	}
	
	public function add($account, $task, $action_comment, $comment, $files = '')
	{
		$record = $this -> getEmptyRecord();
		
		$record -> task = is_object($task) ? $task -> id : 0;
		$record -> date = I18n :: getCurrentDateTime();
		$record -> account = $account -> id;
		$record -> title = $action_comment;
		$record -> content = $comment;
		$record -> files = $files;
		
		return $record -> create();
	}
	
	public function deleteTaskComments($task_id)
	{
		$ids = $this -> selectColumn(array("fields->" => "id", "task" => $task_id));
		
		foreach($ids as $id)
			$this -> delete($id);
	}
	
	public function afterFinalDelete($id, $fields)
	{
		Journal :: deleteFiles($fields["files"]);
	}
	
	public function createSearchUrl($row)
	{
		return $this -> root_path."task/".$row["task"]."#comment-".$row["id"];
	}

	public function countNewTasksAndComments($account_id, $open, $projects)
	{
		$tasks = $this -> select(["table->" => "tasks", "assigned_to" => $account_id, "status->in" => $open, "project->in" => $projects,
								  "fields->" => "`id`,`date_created`,`date_updated`"]);

		foreach($tasks as $id => $task)
		{
			$tasks[$id]["last_comment"] = "";

			if($comment = $this -> selectOne(["task" => $task["id"], "order->desc" => "date"]))
				$tasks[$id]["last_comment"] = $comment["date"];
		}

		$data = json_encode($tasks);
		$hash = md5($data);
	}

	public function addTaskToSee($account_id, $task_id)
	{
		$key = "see_tasks_".$account_id;

		$data = $this -> registry -> getDatabaseSetting($key);
		$data = $data ? json_decode($data, true) : [];
		$data[] = $task_id;
		$data = array_unique($data);

		$this -> registry -> setDatabaseSetting($key, json_encode($data));
	}

	public function removeTaskToSee($account_id, $task_id)
	{
		$key = "see_tasks_".$account_id;
		
		$data = $this -> registry -> getDatabaseSetting($key);
		$data = $data ? json_decode($data, true) : [];
		$data = array_unique($data);
		
		$index = array_search($task_id, $data);

		if($index !== false)
			unset($data[$index]);

		$this -> registry -> setDatabaseSetting($key, json_encode($data));
	}

	public function getTasksToSee($account_id)
	{
		$key = "see_tasks_".$account_id;
		$data = $this -> registry -> getDatabaseSetting($key);

		return $data ? json_decode($data, true) : [];
	}
}
?>