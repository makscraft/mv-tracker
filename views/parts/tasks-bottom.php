          <div class="form-buttons clearfix">
              <input type="button" class="button green medium mass-action" value="<?php echo I18n :: locale("with-selected"); ?>" />
              <input type="hidden" name="csrf-action-token" value="<?php echo Accounts :: generateActionToken($account); ?>" />
              <input type="hidden" name="mass-action-total" value="" />
              <input type="hidden" name="mass-action-fields" value="" />
              <input type="hidden" id="filter-url-params" value="<?php echo $mv -> tasks -> filter -> getUrlParams(); ?>" />
              <?php include $mv -> views_path."parts/pager-limiter.php"; ?>
          </div>
      </form>
      <?php
      	  if($mv -> tasks -> pager -> hasPages())
	      {
      		  echo "<div class=\"pager\">".I18n :: locale("page").":";
      		  echo "<input class=\"active-page\" value=\"".$mv -> tasks -> pager -> getPage()."\">";
      		  echo "<div class=\"page-amount\">".I18n :: locale("from-total");
      		  echo " <span class=\"total-pages\">".$mv -> tasks -> pager -> getIntervals()."</span></div>";
              echo $mv -> tasks -> pager -> displayPrevLink(" ", $pager_url);
      		  echo $mv -> tasks -> pager -> displayNextLink(" ", $pager_url);
	   		  echo "</div>\n";
	      }
      ?>