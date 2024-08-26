<?php
class Statuses extends Model
{
	protected $name = '{statuses}';
	
	protected $model_elements = [
		['{active}', 'bool', 'active', ['on_create' => true]],
		['{closed}', 'bool', 'closed'],
		['{name}', 'char', 'name', ['required' => true, 'unique' => 1]],
		['{color}', 'char', 'color'],
		['{position}', 'order', 'position']
	];
	
	public function getOpenStatusesIds()
	{
		$ids = $this -> selectColumn(['fields->' => 'id', 'active' => 1, 'closed' => 0]);
		
		return count($ids) ? implode(',', $ids) : '0';
	}
	
	public function isTaskOpen($id)
	{
		$statuses = $this -> getOpenStatusesIds();
		
		return (bool) $this -> countRecords(['table->' => 'tasks', 'id' => $id, 'status->in' => $statuses]);
	}
}