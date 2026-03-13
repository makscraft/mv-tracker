<?php
if(isset($_GET['text']) && trim($_GET['text']))
	$search_text = trim(htmlspecialchars($_GET['text'], ENT_QUOTES));
else
	$search_text = false;

$total = 0;
	
if($search_text && mb_strlen($search_text, "utf-8") >= 2)
{
	$parameters = array("tasks" => array("search_fields" => array("name", "description"),
										 "result_fields" => array("content" => "description")),
			
						"projects" => array("search_fields" => array("name", "description"),
											"result_fields" => array("content" => "description")),
			
						"journal" => array("search_fields" => array("content")),
						
						"documentation" => array("search_fields" => array("name", "content"))
	);
	
	$mv -> search -> setModels($parameters) -> setSearchRequest($search_text);
	$total = $mv -> search -> makeSearchAndCountResults();
	$current_page = $mv -> router -> defineCurrentPage("page");
	$limit = $mv -> tasks -> definePagerLimit($account);
	
	$mv -> search -> runPager($total, $limit, $current_page);
}

$path = $pager_url = $mv -> root_path."search?text=".$search_text;

include $mv -> views_path."main-header.php";
?>
	<div id="content" class="right-side">
		<div class="content-wrapper">
			<h1><?php echo I18n :: locale("search"); ?></h1>
            <?php
           		echo "<p>".I18n :: locale('results-found').": ".$total."</p>\n";
           		echo $mv -> search -> display();
            ?>
            <div class="form-buttons clearfix">
               <?php
               	   $pager_model = $mv -> search;
            	   include $mv -> views_path."parts/pager.php";
         	   ?>            
               <input type="hidden" id="filter-url-params" value="text=<?php echo $search_text; ?>" />
               <?php include $mv -> views_path."parts/pager-limiter.php"; ?>
            </div>

		</div>
		<div class="clear"></div>
	</div>
<?php
include $mv -> views_path."main-footer.php";
?>