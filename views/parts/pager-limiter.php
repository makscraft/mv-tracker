
<div class="limiter">
	<span><?php echo I18nlocale("pager-limit"); ?>:</span>
	<select id="limit-per-page">
		<?php
		foreach(TasksgetPagerLimits() as $value)
		{
			echo "<option value=\"".$value."\"";
		
			if($limit == $value)
				echo " selected=\"selected\"";
			
			echo ">".$value."</option>\n";
		}
	?>
	</select>
</div>