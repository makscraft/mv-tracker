<?php
class Trackers extends Model
{
	protected $name = "{trackers}";
	
	protected $model_elements = array(
		array("{active}", "bool", "active", array("on_create" => true)),
		array("{name}", "char", "name", array("required" => true, "unique" => 1)),
		array("{color}", "char", "color"),
		array("{position}", "order", "position")
	);
}