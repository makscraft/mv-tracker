<?php
class Trackers extends Model
{
	protected $name = '{trackers}';
	
	protected $model_elements = [
		['{active}', 'bool', 'active', ['on_create' => true]],
		['{name}', 'char', 'name', ['required' => true, 'unique' => 1]],
		['{color}', 'char', 'color'],
		['{position}', 'order', 'position']
	];
}