<?php
class Priorities extends Model
{
	protected $name = '{priorities}';
	
	protected $model_elements = [
		['{active}', 'bool', 'active', ['on_create' => true]],
		['{name}', 'char', 'name', ['required' => true, 'unique' => 1]],
		['{color}', 'char', 'color'],
		['{position}', 'order', 'position']
	];
}