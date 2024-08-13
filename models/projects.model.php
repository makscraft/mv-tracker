<?php
class Projects extends Model
{
	protected $name = "{projects}";
	
	protected $model_elements = array(
		array("{active}", "bool", "active", array("on_create" => true)),
		array("{name}", "char", "name", array("required" => true)),
		array("{date-created}", "date_time", "date_created"),
		array("{date-updated}", "date_time", "date_updated"),
		array("{description}", "text", "description"),
		array("{tasks}", "many_to_one", "tasks", array("related_model" => "Tasks"))
	);
	
	public function defineProjectPage(Router $router)
	{
		$url_parts = $router -> getUrlParts();
		
		if(count($url_parts) == 3 && $url_parts[1] == "edit" && is_numeric($url_parts[2]))
			$params = array("id" => $url_parts[2], "active" => 1);
		else if(count($url_parts) == 2 && $url_parts[0] == "project" && is_numeric($url_parts[1]))
			$params = array("id" => $url_parts[1], "active" => 1);
		else
			return false;
						
		return $this -> findRecord($params);
	}
	
	public function generateDeleteToken($project_id)
	{
		return md5($_SESSION["account"]["token"].$project_id.$_SERVER["HTTP_USER_AGENT"].$_SERVER["REMOTE_ADDR"]);
	}
	
	public function getActiveProjectsIds()
	{
		$ids = $this -> selectColumn(array("fields->" => "id", "active" => 1));
		
		return count($ids) ? implode(",", $ids) : "0";
	}
	
	public function display()
	{
		$html = "";
		$rows = $this -> select(array("active" => 1, "order->asc" => "name",
									  "limit->" => $this -> pager -> getParamsForSelect()));
		
		if(!count($rows))
			return "<tr><td class=\"name mobile-visible\" colspan=\"10\">".I18n :: locale("no-records-found")."</td></tr>\n";
		
		$tasks = new Tasks();
		$statuses = new Statuses();
		
		$open = $statuses -> getOpenStatusesIds();
		
		foreach($rows as $row)
		{
			$url = $this -> root_path."project/".$row["id"];
			$params = array("status->in" => $open, "project" => $row["id"]);
			$active_tasks = $tasks -> countRecords($params);
			
			$sql = "SELECT AVG(`complete`) FROM `tasks` WHERE `project`='".$row["id"]."' AND `status` IN(".$open.")";
			$ready = $this -> db -> getCell($sql);

			$sql = "SELECT SUM(`hours_spent`) FROM `tasks` WHERE `project`='".$row["id"]."' AND `status` IN(".$open.")";
			$time_spent = Service :: roundTo05((float) $this -> db -> getCell($sql));
			
			$params["fields->"] = "id";			
			$ids = $tasks -> selectColumn($params);
			
			if(count($ids))
			{
				$params = array("table->" => "journal", "task->in" => implode(",", $ids), "order->desc" => "date");
				$last_action = $tasks -> selectOne($params);
			}
			else
				$last_action = false;
			
			$token = $this -> generateDeleteToken($row["id"]);
			$complete = ' style="width: '.($ready ? ceil($ready) : 0).'%"';
			
			$html .= "<tr>\n";
			$html .= "<td class=\"name mobile-visible\"><a href=\"".$url."\">".$row["name"]."</a>\n";
			$html .= "<span class=\"tracker\"><span class=\"for-mobile\">".I18n :: locale("tasks").": ";
			$html .= $active_tasks;
			
			if($active_tasks)
				$html .= ", ".I18n :: locale("implementation").": ".ceil($ready)."%";
			
			$html .= "</span></span></td>\n";
			$html .= "<td>".($active_tasks ? $active_tasks : "-")."</td>\n";
			
			if($active_tasks)
				$html .= "<td><span title=\"".ceil($ready)."%\" class=\"complete\"><span".$complete."></span></span></td>\n";
			else
				$html .= "<td>-</td>\n";

			$html .= "<td>".$time_spent."</td>\n";
			
			$html .= "<td>".I18n :: formatDate($row["date_created"], "no-seconds")."</td>\n";
			$html .= "<td>".($last_action ? I18n :: formatDate($last_action["date"], "no-seconds") : "-")."</td>\n";
			$html .= "<td class=\"actions mobile-visible\">\n";
			$html .= "<a class=\"edit\" title=\"".I18n :: locale("edit")."\" href=\"";
			$html .= $this -> root_path."project/edit/".$row["id"]."\"></a>\n";
			$html .= "<span class=\"delete\" title=\"".I18n :: locale("delete")."\" id=\"project-delete-";
			$html .= $row["id"]."-".$token."\"></a>\n";
			$html .= "</td>\n";
			$html .= "</tr>\n";
		}
		
		return $html;
	}
	
	public function displayArchive()
	{
		$html = "";
		$rows = $this -> select(array("active" => 0, "order->asc" => "name",
									  "limit->" => $this -> pager -> getParamsForSelect()));
		
		if(!count($rows))
			return "<tr><td class=\"name mobile-visible\" colspan=\"10\">".I18n :: locale("no-records-found")."</td></tr>\n";
		
		foreach($rows as $row)
		{
			$token = $this -> generateDeleteToken($row["id"]);
			
			$params = array("table->" => "tasks", "project" => $row["id"], "order->desc" => "date_updated");
			$last_action = $this -> selectOne($params);
				
			$html .= "<tr>\n";
			$html .= "<td class=\"name mobile-visible\">".$row["name"]."</td>\n";
			$html .= "<td>".($last_action ? I18n :: formatDate($last_action["date_created"], "only-date") : "-")."</td>\n";
			$html .= "<td>".($last_action ? I18n :: formatDate($last_action["date_updated"], "only-date") : "-")."</td>\n";
			$html .= "<td class=\"actions mobile-visible\">\n";
			$html .= "<span class=\"restore\" title=\"".I18n :: locale("restore")."\" id=\"project-restore-".$row["id"]."-".$token."\"></span>\n";
			$html .= "<span class=\"delete\" title=\"".I18n :: locale("delete")."\" id=\"project-delete-".$row["id"]."-".$token."\"></span>\n";
			$html .= "</td>\n";
			$html .= "</tr>\n";
		}
		
		return $html;
	}
	
	public function deleteProject($id, $token)
	{
		if($record = $this -> findRecordById($id))
			if($this -> generateDeleteToken($id) == $token)
			{
				$this -> delete($id);
				
				$tasks = new Tasks();
				$tasks -> deleteProjectTasks($id);
				
				$_SESSION["account"]["message-success"] = I18n :: locale("project-deleted");
				return true;
			}
			else
				$_SESSION["account"]["message-error"] = I18n :: locale("error-wrong-token");
	}
	
	public function restoreProject($id, $token)
	{
		if($record = $this -> findRecordById($id))
			if($this -> generateDeleteToken($id) == $token)
			{
				$record -> setValue("active", 1) -> update();
				$_SESSION["account"]["message-success"] = I18n :: locale("project-restored");
				return true;
			}
			else
				$_SESSION["account"]["message-error"] = I18n :: locale("error-wrong-token");
	}
	
	public function archiveProject($id, $token)
	{
		if($record = $this -> findRecordById($id))
			if($this -> generateDeleteToken($id) == $token)
			{
				$record -> setValue("active", 0) -> update();
				$_SESSION["account"]["message-success"] = I18n :: locale("project-archived");
				return true;
			}
			else
				$_SESSION["account"]["message-error"] = I18n :: locale("error-wrong-token");
	}
	
	public function afterRestore($id, $fields)
	{
		$garbage = new Garbage();
		$rows = $garbage -> select(array("module" => "tasks"));
		
		foreach($rows as $row)
		{
			$row["content"] = Service :: unserializeArray($row["content"]);
			
			if($row["content"]["project"] == $id)
				$garbage -> setId($row["id"]) -> restore();
		}
	}
	
	public function createSearchUrl($row)
	{
		return $this -> root_path."project/".$row["id"];
	}
}