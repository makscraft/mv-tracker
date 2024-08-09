<?
class Tasks extends Model
{
	protected $name = "{tasks}";
	
	protected $model_elements = array(
		array("{name}", "char", "name", array("required" => true)),
		array("{tracker}", "enum", "tracker", array("required" => true, "foreign_key" => "Trackers")),
		array("{project}", "enum", "project", array("required" => true, "foreign_key" => "Projects")),
		array("{date-created}", "date_time", "date_created"),
		array("{date-updated}", "date_time", "date_updated"),
		array("{date-due}", "date", "date_due"),
		array("{status}", "enum", "status", array("required" => true, "foreign_key" => "Statuses")),
		array("{assigned-to}", "enum", "assigned_to", array("foreign_key" => "Accounts")),
		array("{priority}", "enum", "priority", array("foreign_key" => "Priorities")),
		array("{author}", "enum", "author", array("foreign_key" => "Accounts")),
		array("{implementation}", "enum", "complete", array("empty_value" => "0%",
													  "values_list" => array("10" => "10%", 
														  					 "20" => "20%",
																		     "30" => "30%",
																		     "40" => "40%",
																		     "50" => "50%",
																		     "60" => "60%",
																		     "70" => "70%",
																		     "80" => "80%",
																		     "90" => "90%",
																		     "100" => "100%"))),
		array("{hours-estimated}", "float", "hours_estimated"),
		array("{hours-spent}", "float", "hours_spent"),
		array("{description}", "text", "description"),
		array("{attached-files}", "text", "files", ["rich_text" => true]),
		array("{journal-records}", "many_to_one", "journal", array("related_model" => "Journal", "name_field" => "task"))
	);
	
	private $allowed_columns = array("id", "name", "tracker", "project", "assigned_to", "priority", "date_due", 
									"date_created", "date_updated", "complete", "status", "author", "hours_estimated", 
									"hours_spent");
	
	private $background_colors = [];
	
	public function defineTaskPage(Router $router)
	{
		$url_parts = $router -> getUrlParts();
		
		if(count($url_parts) == 3 && $url_parts[1] == "edit" && is_numeric($url_parts[2]))
			$params = array("id" => $url_parts[2]);
		else if(count($url_parts) == 2 && $url_parts[0] == "task" && is_numeric($url_parts[1]))
			$params = array("id" => $url_parts[1]);
		else
			return false;
			
		return $this -> findRecord($params);
	}
	
	static public function processDescriptionText($text)
	{
		$matches = [];
		$found = preg_match_all("/https?:\/\/[\w\.\/#!\?&-=;%_\{\}~]+/ui", $text, $matches);
		
		if($found)
		{
			$matches = array_unique($matches[0]);
			
			foreach($matches as $url)
			{
				$re = Service :: prepareRegularExpression($url);
				$text = preg_replace("/".$re."\s/", "<a target=\"_blank\" href=\"".$url."\">".$url."</a> ", $text);
				$text = preg_replace("/".$re."$/", "<a target=\"_blank\" href=\"".$url."\">".$url."</a> ", $text);
			}
		}
		
		$text = str_replace(array("&lt;pre&gt;", "&lt;/pre&gt;"), array("<pre>", "</pre>"), $text);
		$text = str_replace("\r", "", $text);
		$text = preg_replace("/(\n\s*){3,}/", "\n\n", $text);
		
		$data = preg_split("/\n/", $text);
		
		foreach($data as $key => $line)
			if(preg_match("/^h[123]\.\s/", $line))
				$data[$key] = preg_replace("/^h([123])\.\s+(.*)/", "<h$1>$2</h$1>", $line);
			
		$text = implode("\n", $data);
		
		$text = nl2br($text);
		$text = str_replace("</h1><br />", "</h1>", $text);
		$text = str_replace("</h2><br />", "</h2>", $text);
		$text = str_replace("</h3><br />", "</h3>", $text);
		$text = str_replace("<pre><br />", "<pre>", $text);
		$text = str_replace("<br />\n</pre>", "</pre>", $text);
		
		return $text;
	}
	
	static public function processDateTimeValue($date)
	{
		$parts = explode(" ", $date);
		$today = date("Y-m-d");
		
		if($parts[0] == $today)
			return I18n :: locale("today")." ".I18n :: formatDate($date, "H:i");
		else if($parts[0] == date("Y-m-d", strtotime("-1 day", strtotime($today))))
			return I18n :: locale("yesterday")." ".I18n :: formatDate($date, "H:i");
		
		return I18n :: formatDate($date, "no-seconds");
	}
	
	static public function getPagerLimits()
	{
		return array(5, 10, 15, 20, 30, 50, 100, 200);
	}
	
	static public function generateDeleteToken($task_id)
	{
		return md5($_SERVER["HTTP_USER_AGENT"].$_SESSION["account"]["token"].$_SERVER["REMOTE_ADDR"].$task_id);
	}
	
	static public function generateCommentToken($task_id)
	{
		return md5($_SERVER["HTTP_USER_AGENT"].$task_id.$_SESSION["account"]["token"]);
	}
	
	public function getAllowedColumns()
	{
		return $this -> allowed_columns;
	}
	
	public function defineTableColumns($view, $account)
	{
		if(isset($_SESSION["account"]["columns"][$view]))
			return $_SESSION["account"]["columns"][$view];
			
		if($setting = Accounts :: getSetting($account, "columns-".$view))
		{
			$setting = $_SESSION["account"]["columns"][$view] = explode(",", $setting);
			return $setting;
		}
		
		if($view == "my-tasks")
			return array("id", "name", "project", "priority", "date_due", "complete");
		else if($view == "all-tasks")
			return array("id", "name", "project", "assigned_to", "priority", "date_due", "complete");
		else if($view == "projects")
			return array("id", "name", "priority", "assigned_to", "date_due", "complete");
	}
	
	public function setAndSaveTableColumns($view, $account, $columns)
	{
		$save = [];
		
		foreach($columns as $column)
			if(in_array($column, $this -> allowed_columns))
				$save[] = $column;
		
		if(!count($save))
			return;
		
		if($view == "my-tasks" || $view == "all-tasks" || $view == "projects")
		{
			$_SESSION["account"]["columns"][$view] = $save;
			Accounts :: setSetting($account, "columns-".$view, implode(",", $save));
			
			echo Accounts :: getSetting($account, "columns-".$view);
		}
	}
	
	public function getActiveAndPassiveColumnsOptions($active)
	{
		$result = array("active" => "", "passive" => "");
		
		foreach($active as $name)
		{
			$caption = ($name == "id") ? "#" : $this -> getCaption($name);
			$result["active"] .= "<option value=\"".$name."\">".$caption."</option>\n";
		}
			
		$passive = array_diff($this -> allowed_columns, $active);
		
		foreach($passive as $name)
		{
			$caption = ($name == "id") ? "#" : $this -> getCaption($name);
			$result["passive"] .= "<option value=\"".$name."\">".$caption."</option>\n";
		}
		
		return $result;
	}
	
	public function displayTableColumns($columns, $sorter_url)
	{
		$html = "";
		$changes = array("complete" => "implementation");
		
		foreach($columns as $column)
		{
			if($column == "id")
				$title = "#";
			else if(isset($changes[$column]))
				$title = I18n :: locale($changes[$column]);
			else
				$title = I18n :: locale(str_replace("_", "-", $column));
			
			$html .= "<th>".$this -> sorter -> displayLink($column, $title, $sorter_url)."</th>\n";
		}
		
		return $html;
	}
	
	public function getBackgroudColors()
	{
		$this -> background_colors = array("trackers" => [], "priorities" => [], "statuses" => []);
		
		foreach($this -> background_colors as $key => $data)
			foreach($this -> select(array("table->" => $key, "active" => 1)) as $row)
				if($row["color"])
					$this -> background_colors[$key][$row["id"]] = $row["color"];
	}
	
	public function defineBackgroundColor($row)
	{
		$css = "";
		
		if($row["priority"] && isset($this -> background_colors["priorities"][$row["priority"]]))
			$css = $this -> background_colors["priorities"][$row["priority"]];
		else if($row["tracker"] && isset($this -> background_colors["trackers"][$row["tracker"]]))
			$css = $this -> background_colors["trackers"][$row["tracker"]];
		else if($row["status"] && isset($this -> background_colors["statuses"][$row["status"]]))
			$css = $this -> background_colors["statuses"][$row["status"]];
		
		return $css ? ' style="background-color: '.$css.'"' : "";
	}
	
	public function display($params, $columns, $account = false)
	{
		$html = "";
		$rows = $this -> select($params);
		
		if(!count($rows))
			return "<tr><td class=\"name mobile-visible\" colspan=\"12\">".I18n :: locale("no-records-found")."</td></tr>\n";
		
		$this -> getBackgroudColors();
		
		foreach($rows as $row)
		{
			$url = $this -> root_path."task/".$row["id"];
			$enums = array("priority", "tracker", "assigned_to", "status", "author");

			$html .= "<tr".$this -> defineBackgroundColor($row)."><td class=\"mobile-visible\">";
			$html .= "<label class='checkbox-wrapper'>";
			$html .= "<input type=\"checkbox\" name=\"item-".$row["id"]."\" /></label></td>\n";
			
			foreach($columns as $column)
			{
				$project = $this -> getEnumTitle("project", $row["project"]);
				
				if($column == "name")
				{
					$html .= "<td class=\"name mobile-visible\"><a href=\"".$url."\">".$row["name"]."</a>";
					$html .= "<span class=\"tracker\">".$this -> getEnumTitle("tracker", $row["tracker"]);
					$html .= "<span class=\"for-mobile\">";
					$html .= $project ? ", ".$project : "";
					$html .= ", ".$this -> getEnumTitle("status", $row["status"]);
					
					if($value = $this -> getEnumTitle("assigned_to", $row["assigned_to"]))
						$html .= ", ".$value;
					
					$html .= ", ".intval($row["complete"])."%";
					$html .= "</span></span></td>\n";
				}
				else if($column == "project")
				{
					$project_url = $project ? " href=\"".$this -> root_path."project/".$row["project"]."\"" : "";					
					$html .= "<td><a".$project_url.">".($project ? $project : "-")."</a></td>\n";
				}
				else if(in_array($column, $enums))
				{
					$value = $this -> getEnumTitle($column, $row[$column]);
					$html .= "<td>".($value ? $value : "-")."</td>\n";
				}
				else if($column == "hours_spent")
					$html .= "<td>".($row["hours_spent"] ? $row["hours_spent"] : "-")."</td>\n";
				else if($column == "hours_estimated")
					$html .= "<td>".($row["hours_estimated"] ? $row["hours_estimated"] : "-")."</td>\n";
				else if($column == "date_created" || $column == "date_updated" || $column == "date_due")
				{
					if($row[$column] && $row[$column] != "0000-00-00" && $row[$column] != "0000-00-00 00:00:00")
						$html .= "<td>".I18n :: formatDate($row[$column], "no-seconds")."</td>\n";
					else
						$html .= "<td>-</td>\n";
				}
				else if($column == "complete")
				{
					$complete = ' style="width: '.intval($row[$column]).'%"';
					$html .= "<td><span title=\"".intval($row["complete"])."%\" class=\"complete\"><span";
					$html .= $complete."></span></span></td>\n";
				}
				else if($column == "id")
					$html .= "<td class=\"number\">".$row[$column]."</td>\n";
				else
					$html .= "<td>".$row[$column]."</td>\n";
			}
			
			$token = $this -> generateDeleteToken($row["id"]);
			$can_delete = ($account && $account -> id == $row["author"]) ? "" : " off";
			
			$html .= "<td class=\"actions mobile-visible\">\n";
			$html .= "<a class=\"edit\" title=\"".I18n :: locale("edit")."\" href=\"";
			$html .= $this -> root_path."task/edit/".$row["id"]."\"></a>\n";
			$html .= "<span class=\"delete".$can_delete."\" title=\"".I18n :: locale("delete")."\" ";
			$html .= "id=\"task-delete-".$row["id"]."-".$token."\"></span>\n";
			$html .= "</td>\n";
			$html .= "</tr>\n";			
		}
		
		return $html;
	}
	
	public function definePagerLimit($account)
	{
		$limits = self :: getPagerLimits();
		
		if(isset($_GET["pager-limit"]) && in_array($_GET["pager-limit"], $limits))
			$limit = $_SESSION["account"]["pager-limit"] = intval($_GET["pager-limit"]);
		else if(isset($_SESSION["account"]["pager-limit"]) && in_array($_SESSION["account"]["pager-limit"], $limits))
			$limit = intval($_SESSION["account"]["pager-limit"]);
		else if($value = Accounts :: getSetting($account, "pager-limit"))
			$limit = $value;
		else
			$limit = 20;
		
		return $limit;
	}
	
	public function filterFormValues($form)
	{
		$form -> filterValuesList("project", array("active" => 1, "order->asc" => "name"));
		$form -> filterValuesList("assigned_to", array("active" => 1, "order->asc" => "name"));
		$form -> filterValuesList("tracker", array("active" => 1, "order->asc" => "position"));
		$form -> filterValuesList("status", array("active" => 1, "order->asc" => "position"));
		$form -> filterValuesList("priority", array("active" => 1, "order->asc" => "position"));
		
		return $this;
	}
	
	public function changeFloatSeparator()
	{
		$fields = array("hours_estimated", "hours_spent");
		
		foreach($fields as $field)
			if(isset($_POST[$field]))
				$_POST[$field] = str_replace(",", ".", $_POST[$field]);
			
		return $this;
	}


	//My assigned tasks

	public function getLastSeenAssignedTasks($account, $statuses, $projects)
	{
		if(!isset($_SESSION["account"]["assigned-tasks"]))
		{
			$ids = $this -> getMyAssignedTasks($account, $statuses, $projects);
			$_SESSION["account"]["assigned-tasks"] = implode(",", $ids);
		}

		return $_SESSION["account"]["assigned-tasks"];
	}

	public function getMyAssignedTasks($account, $statuses, $projects)
	{
		$open = $statuses -> getOpenStatusesIds();
		$projects_ids = $projects -> getActiveProjectsIds();
		$params = array("assigned_to" => $account -> id, "status->in" => $open, "project->in" => $projects_ids);
		$params["fields->"] = "id";

		return $this -> selectColumn($params);
	}

	public function dropLastSeenAssignedTasks()
	{
		unset($_SESSION["account"]["assigned-tasks"]);
	}

	
	//Actions
	
	public function getMassActionIds()
	{
		$ids = [];
		
		foreach($_POST as $key => $value)
			if(strpos($key, "item-") !== false)
				$ids[] = intval(str_replace("item-", "", $key));
				
		return $ids;
	}
	
	public function createActionComment($new_data, $old_data)
	{
		$comment = "";
		
		if($old_data == [])
			$old_data = array("assigned_to" => "", "complete" => "", "status" => "", "hours_estimated" => "", 
							  "hours_spent" => "");
		
		if(isset($new_data["assigned_to"]) && $new_data["assigned_to"] && $new_data["assigned_to"] != $old_data["assigned_to"])
			if($old_data["assigned_to"])
			{
				$comment = I18n :: locale("task-reassigned-from")." ".$this -> getEnumTitle("assigned_to", $old_data["assigned_to"]);
				$comment .= " ".I18n :: locale("to-person")." ".$this -> getEnumTitle("assigned_to", $new_data["assigned_to"]);
			}
			else
				$comment = I18n :: locale("assigned-to").": ".$this -> getEnumTitle("assigned_to", $new_data["assigned_to"]);
		
		if(isset($new_data["complete"]) && $new_data["complete"] && $new_data["complete"] != $old_data["complete"])
		{
			$comment .= $comment ? ", " : "";
			$comment .= I18n :: locale("implementation").": ".$new_data["complete"]."%";
		}
		
		if(isset($new_data["status"]) && $new_data["status"] && $new_data["status"] != $old_data["status"])
		{
			$comment .= $comment ? ", " : "";
			$comment .= I18n :: locale("status").": ".$this -> getEnumTitle("status", $new_data["status"]);
		}
		
		if(isset($new_data["hours_estimated"]) && $new_data["hours_estimated"] && $new_data["hours_estimated"] != $old_data["hours_estimated"])
		{
			$comment .= $comment ? ", " : "";
			$comment .= I18n :: locale("hours-estimated").": ".$new_data["hours_estimated"];
		}
		
		if(isset($new_data["hours_spent"]) && $new_data["hours_spent"] && $new_data["hours_spent"] != $old_data["hours_spent"])
		{
			$comment .= $comment ? ", " : "";
			$comment .= I18n :: locale("hours-spent").": ".$new_data["hours_spent"];
		}		
		
		return $comment;
	}
	
	public function applyMassAction($account)
	{
		$elements = array("tracker", "project", "date_due", "assigned_to", "priority", "status",
						  "hours_estimated", "hours_spent", "complete");
		
		$ids = $this -> getMassActionIds();
		$fields = [];
		$total = intval($_POST["mass-action-total"]);
		
		foreach(explode("&", $_POST["mass-action-fields"]) as $string)
		{
			$values = explode("=", $string);
			
			if(isset($values[0], $values[1]) && in_array($values[0], $elements) && trim($values[1]) != "")
				$fields[$values[0]] = trim(urldecode($values[1]));
			
			if($values[0] == "hours_estimated" || $values[0] == "hours_spent")
				if(isset($fields[$values[0]]))
					$fields[$values[0]] = str_replace(",", ".", $fields[$values[0]]);
		}

		$_POST = array_merge($_POST, $fields);

		$token = Accounts :: generateActionToken($account);
		
		if(!isset($_POST["csrf-action-token"]) || $_POST["csrf-action-token"] != $token || !$total || 
		   $total != count($ids))
		{
			$_SESSION["account"]["message-error"] = I18n :: locale("error-wrong-token");
			return;
		}
		
		$form = new Form("Tasks");
		$form -> removeRule("name", "required") -> removeRule($elements, "required");
		$form -> submit() -> validate();
		
		if($form -> isSubmitted() && $form -> isValid())
		{
			$changed = [];
			
			foreach($form -> getAllValues($elements) as $key => $value)
				if($value != "")
				{
					if($key == "date_due")
						$changed[$key] = I18n :: dateForSQL($value);
					else
						$changed[$key] = $value;
				}
				
			if(count($changed) && count($ids))
			{
				$needs_email = [];
				$tasks_closed = false;
				
				if(isset($changed["assigned_to"]) && $changed["assigned_to"] != $account -> id)
					foreach($ids as $id)
						if($task = $this -> findRecord(array("id" => $id, "assigned_to!=" => $changed["assigned_to"])))
							$needs_email[] = $id;
						
				if(isset($changed["status"]))
				{
					$status = $this -> findRecord(array("table->" => "statuses", "id" => $changed["status"]));
					
					if($status -> closed)
					{
						$tasks_closed = true;
						
						foreach($ids as $id)
							if(!in_array($id, $needs_email))
								$needs_email[] = $id;
					}
				}
				
				$this -> updateManyRecords($changed, array("id->in" => implode(",", $ids)));
				$_SESSION["account"]["message-success"] = I18n :: locale("tasks-updated");
				
				if(isset($changed["assigned_to"]) || isset($changed["complete"]) || isset($changed["status"]) || 
				   isset($changed["hours_spent"]))
				{
					$journal = new Journal();
					$message = $this -> createActionComment($changed, []);
					
					$journal -> add($account, 0, implode(",", $ids), $message, "");
				}
				
				if(count($needs_email) && (isset($changed["assigned_to"]) || $tasks_closed))
				{
				    $sent = 0;
				    
					foreach($needs_email as $id)
					{
					    if($sent >= 3)
					        break;
					    
						$task = $this -> findRecordById($id);
						
						if($task -> assigned_to && $task -> assigned_to != $account -> id)
						{
							$user = $this -> findRecord(array("id" => $task -> assigned_to, "table->" => "accounts"));
							$text = $tasks_closed ? "" : $task -> description;
							
							Journal :: sendEmail($user, $task, $text);
							sleep(1);
							
							$sent ++;
						}
					}
				}
			}
		}
	}
	
	public function applyMassDelete($account)
	{
		$ids = $this -> getMassActionIds();
		$total = isset($_GET["mass-delete"]) ? intval($_GET["mass-delete"]) : 0;
		$token = Accounts :: generateActionToken($account);
		
		if(!isset($_POST["csrf-action-token"]) || $_POST["csrf-action-token"] != $token || !$total ||
		   $total != count($ids))
		{
			$_SESSION["account"]["message-error"] = I18n :: locale("error-wrong-token");
			return;
		}
		
		foreach($ids as $id)
		{
			$task = $this -> findRecordById($id);

			if(!$task || $task -> author != $account -> id)
				continue;

			$this -> delete($id);
			
			$journal = new Journal();
			$journal -> deleteTaskComments($task -> id);
		}
			
		$_SESSION["account"]["message-success"] = I18n :: locale("tasks-deleted");
	}
	
	public function deleteOneTask($id, $token)
	{
		if($task = $this -> findRecordById($id))
			if($this -> generateDeleteToken($task -> id) == $token)
			{
				$this -> delete($task -> id);
				
				$journal = new Journal();
				$journal -> deleteTaskComments($task -> id);
				
				$_SESSION["account"]["message-success"] = I18n :: locale("task-deleted");
				return true;
			}
			else
				$_SESSION["account"]["message-error"] = I18n :: locale("error-wrong-token");
	}
	
	public function deleteProjectTasks($project_id)
	{
		$journal = new Journal();
		$ids = $this -> selectColumn(array("fields->" => "id", "project" => $project_id));
		
		foreach($ids as $id)
		{
			$this -> delete($id);
			$journal -> deleteTaskComments($id);
		}
	}
	
	public function afterFinalDelete($id, $fields)
	{
		Journal :: deleteFiles($fields["files"]);
	}
	
	public function afterRestore($id, $fields)
	{
		$garbage = new Garbage();
		$rows = $garbage -> select(array("module" => "journal"));
		
		foreach($rows as $row)
		{
			$row["content"] = Service :: unserializeArray($row["content"]);
			
			if($row["content"]["task"] == $id)
				$garbage -> setId($row["id"]) -> restore();
		}
	}
	
	public function createSearchUrl($row)
	{
		return $this -> root_path."task/".$row["id"];
	}

	static public function displayTextileHelp()
	{
		return "<div class=\"help-text\">Textile: &lt;pre&gt;, h1.-h3.</div>\n";
	}	
}
?>