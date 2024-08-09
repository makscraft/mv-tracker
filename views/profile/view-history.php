<?
$total = $mv -> journal -> countRecords(array("extra->" => "(`content`!='' OR `title`!='' OR `files`!='')"));

$current_page = $mv -> router -> defineCurrentPage("page");
$mv -> journal -> runPager($total, 12, $current_page);
$pager_url = $mv -> root_path."history";

include $mv -> views_path."main-header.php";
?>
<div id="content">
	<h1><? echo I18n :: locale("history"); ?></h1>
	<div class="full-history clearfix">
	  <? echo $mv -> journal -> displayFullHistory(); ?>
	</div>
	<?
		$pager_model = $mv -> journal;
		include $mv -> views_path."parts/pager.php";
	?>
</div>
<?
include $mv -> views_path."main-footer.php";
?>