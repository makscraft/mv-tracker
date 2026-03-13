<?php
$filter_fields = array("name", "tracker", "date_due", "status", "assigned_to", "priority", "complete", "description");

if($base_url == "home")
	unset($filter_fields[array_search("assigned_to", $filter_fields)]);

$mv -> tasks -> setFieldProperty("complete", "empty_value", I18n :: locale("not-defined"));
$mv -> tasks -> runFilter($filter_fields);
$mv -> tasks -> filter -> setDisplaySingleField("date_due");
$mv -> tasks -> filter -> filterValuesList("assigned_to", array("active" => 1));
$mv -> tasks -> filter -> filterValuesList("status", array("order->asc" => "position"));
$mv -> tasks -> filter -> addOptionToValuesList("status", 100000, I18n :: locale("all"));

if($mv -> tasks -> filter -> hasParams())
{
	$conditions = $mv -> tasks -> filter -> getConditions();
	$params = array_merge($params, $conditions);
	
	if(isset($params["status"]))
		if($params["status"] == 100000)
	        unset($params["status->in"], $params["status"]);
	    else
	    	unset($params["status->in"]);
}

$total = $mv -> tasks -> countRecords($params);
$current_page = $mv -> router -> defineCurrentPage("page");
$limit = $mv -> tasks -> definePagerLimit($account);
$mv -> tasks -> runPager($total, $limit, $current_page);

$mass_actions_url = $mv -> tasks -> pager -> addUrlParams($base_url);
$mass_actions_url = $mv -> tasks -> filter -> addUrlParams($mass_actions_url);

$pager_url = $sorter_url = $mv -> root_path.$mv -> tasks -> filter -> addUrlParams($base_url);

if(Http :: isPostRequest() && isset($_POST["mass-action-total"]) && intval($_POST["mass-action-total"]))
{
	if(!$mv -> registry -> getSetting("DemoMode"))
		$mv -> tasks -> applyMassAction($account);

	$check = "&assigned_to=".$account -> id."&";

	if(strpos($_POST["mass-action-fields"], $check) !== false)
		$mv -> tasks -> dropLastSeenAssignedTasks($account, $mv -> statuses, $mv -> projects);

	$mv -> redirect($mass_actions_url);
}
else if(!empty($_POST) && isset($_GET["mass-delete"]))
{
	if(!$mv -> registry -> getSetting("DemoMode"))
		$mv -> tasks -> applyMassDelete($account);

	$mv -> redirect($mass_actions_url);
}
else if(isset($_GET["delete"], $_GET["token"]))
{
	if(!$mv -> registry -> getSetting("DemoMode"))
	{
		if($task = $mv -> tasks -> findRecordById(intval($_GET["delete"])))
			if($task -> author == $account -> id)
				$mv -> tasks -> deleteOneTask($_GET["delete"], $_GET["token"]);
	}
	
	$mv -> redirect($mass_actions_url);
}

$mv -> tasks -> runSorter($mv -> tasks -> getAllowedColumns(), "id", "desc");

if(isset($_GET["sort-field"], $_GET["sort-order"]))
{
	$mv -> tasks -> sorter -> setParams($_GET["sort-field"], $_GET["sort-order"]);
	
	if($mv -> tasks -> sorter -> getField() == $_GET["sort-field"])
		if($mv -> tasks -> sorter -> getOrder() == $_GET["sort-order"])
		{
			$_SESSION["account"]["sorting"]["tasks"]["field"] = $mv -> tasks -> sorter -> getField();
			$_SESSION["account"]["sorting"]["tasks"]["order"] = $mv -> tasks -> sorter -> getOrder();
		}
}

if(isset($_SESSION["account"]["sorting"]["tasks"]["field"]))
	$mv -> tasks -> sorter -> setParams($_SESSION["account"]["sorting"]["tasks"]["field"], 
										$_SESSION["account"]["sorting"]["tasks"]["order"]);

if(isset($_GET["pager-limit"], $limit) && $_GET["pager-limit"] == $limit)
	Accounts :: setSetting($account, "pager-limit", $limit);

$params["order->".$mv -> tasks -> sorter -> getOrder()] = $mv -> tasks -> sorter -> getField();
$params["limit->"] = $mv -> tasks -> pager -> getParamsForSelect();
?>