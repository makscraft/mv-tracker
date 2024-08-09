<?
class Documentation extends Model
{
	protected $name = "{documentation}";
	
	protected $model_elements = array(
		array("{name}", "char", "name", array("required" => true)),		
		array("{project}", "enum", "project", array("required" => true, "foreign_key" => "Projects")),
		array("{author}", "enum", "author", array("foreign_key" => "Accounts")),
		array("{date-created}", "date_time", "date_created"),
		array("{date-updated}", "date_time", "date_updated"),
		array("{content}", "text", "content"),
		array("{attached-files}", "text", "files")
	);
	
	public function defineDocumentPage(Router $router)
	{
		$url_parts = $router -> getUrlParts();
		
		if(count($url_parts) == 3 && $url_parts[1] == "edit" && is_numeric($url_parts[2]))
			$params = array("id" => $url_parts[2]);
		else if(count($url_parts) == 2 && $url_parts[0] == "documentation" && is_numeric($url_parts[1]))
			$params = array("id" => $url_parts[1]);
		else
			return false;
				
		return $this -> findRecord($params);
	}
	
	public function generateDeleteToken($id)
	{
		return md5($_SERVER["REMOTE_ADDR"].$_SERVER["HTTP_USER_AGENT"].$_SESSION["account"]["token"].$id);
	}
	
	public function display($params)
	{
		$html = "";

		$params["order->".$this -> sorter -> getOrder()] = $this -> sorter -> getField();
		$params["limit->"] = $this -> pager -> getParamsForSelect();
		$rows = $this -> select($params);
		
		if(!count($rows))
			return "<tr><td class=\"name mobile-visible\" colspan=\"10\">".I18n :: locale("no-records-found")."</td></tr>\n";
		
		foreach($rows as $row)
		{
			$url = $this -> root_path."documentation/".$row["id"];
			$project = $this -> getEnumTitle("project", $row["project"]);
			$author = $this -> getEnumTitle("author", $row["author"]);
			$date = ($row["date_updated"] == "0000-00-00 00:00:00") ? $row["date_created"] : $row["date_updated"];
			$token = $this -> generateDeleteToken($row["id"]);
			
			$html .= "<td class=\"mobile-visible\">".$row["id"]."</td>\n";
			$html .= "<td class=\"name mobile-visible\"><a href=\"".$url."\">".$row["name"]."</a>\n";
			$html .= "<span class=\"tracker\"><span class=\"for-mobile\">";

			if($project)
				$html .= $project;
			
			if($author)
				$html .= ($project ? ", " : "").$author;
			
			$html .= "</span></span></td>\n";
			$html .= "<td>".($project ? $project : "-")."</td>\n";
			$html .= "<td>".($author ? $author : "-")."</td>\n";
			$html .= "<td>".I18n :: formatDate($date, "no-seconds")."</td>\n";
			$html .= "<td class=\"actions mobile-visible\">\n";
			$html .= "<a class=\"edit\" title=\"".I18n :: locale("edit")."\" href=\"";
			$html .= $this -> root_path."documentation/edit/".$row["id"]."\"></a>\n";
			$html .= "<span class=\"delete\" title=\"".I18n :: locale("delete")."\" id=\"document-delete-";
			$html .= $row["id"]."-".$token."\"></span>\n";
			$html .= "</td>\n";
			$html .= "</tr>\n";
		}
		
		return $html;
	}
	
	public function deleteDocument($id, $token)
	{
		if($this -> find($id))
			if($this -> generateDeleteToken($id) == $token)
			{
				$this -> delete($id);
				$_SESSION["account"]["message-success"] = I18n :: locale("document-deleted");
				return true;
			}
			else
				$_SESSION["account"]["message-error"] = I18n :: locale("error-wrong-token");
	}
	
	public function afterFinalDelete($id, $fields)
	{
		Journal :: deleteFiles($fields["files"]);
	}
}
?>