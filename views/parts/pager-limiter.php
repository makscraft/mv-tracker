              <div class="limiter">
                  <span><? echo I18n :: locale("pager-limit"); ?>:</span>
                  <select id="limit-per-page">
                     <?
                     	foreach(Tasks :: getPagerLimits() as $value)
                     	{
                     		echo "<option value=\"".$value."\"";
                     	
                     		if($limit == $value)
                     			echo " selected=\"selected\"";
                     		
                     		echo ">".$value."</option>\n";
    	                }
	                ?>
                  </select>
              </div>