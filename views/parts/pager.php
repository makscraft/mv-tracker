<?
if($pager_model -> pager -> hasPages())
{
    echo "<div class=\"pager\">".I18n :: locale("page").":";
    echo "<input class=\"active-page module\" value=\"";
    echo $pager_model -> pager -> getPage()."\">";
    echo "<div class=\"page-amount\">".I18n :: locale("from-total");
    echo " <span class=\"total-pages\">".$pager_model -> pager -> getIntervals()."</span></div>";
    echo $pager_model -> pager -> displayPrevLink(" ", $pager_url);
    echo $pager_model -> pager -> displayNextLink(" ", $pager_url);
    echo "</div>\n";
}
?>