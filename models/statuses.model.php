<?
class Statuses extends Model
{
	protected $name = "{statuses}";
	
	protected $model_elements = array(
		array("{active}", "bool", "active", array("on_create" => true)),
		array("{closed}", "bool", "closed"),
		array("{name}", "char", "name", array("required" => true, "unique" => 1)),
		array("{color}", "char", "color"),
		array("{position}", "order", "position")
	);
	
	public function getOpenStatusesIds()
	{
		$ids = $this -> selectColumn(array("fields->" => "id", "active" => 1, "closed" => 0));
		
		return count($ids) ? implode(",", $ids) : "0";
	}
	
	public function isTaskOpen($id)
	{
		$statuses = $this -> getOpenStatusesIds();
		
		return (bool) $this -> countRecords(array("table->" => "tasks", "id" => $id, "status->in" => $statuses));
	}
}
?>