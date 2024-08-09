<?
include "../../config/autoload.php";
$mv = new Builder();

$account = $mv -> accounts -> checkAuthorization();

if($account && isset($_GET["locale"]) && I18n :: getRegion() == $_GET["locale"])
{
	$keys = array("create", "edit", "cancel", "delete", "fold", "delete-document", "delete-project", "delete-task", "archive-project", 
				  "restore-project", "delete-file", "with-selected", "delete-checked", "add-comment", "edit-comment", 
				  "delete-comment", "not-defined", "new-comments-reload", "tasks-list-reload");
	
	$data = array();
	
	header("Content-Type: application/javascript; charset=utf-8");
	
	echo "var js_locale_data = {\n\n";
	
	foreach($keys as $key)
		$data[] = str_replace("-", "_", $key).': '.'"'.I18n :: locale($key).'"';
		
	echo implode(",\n", $data)."\n\n};";
}
?>