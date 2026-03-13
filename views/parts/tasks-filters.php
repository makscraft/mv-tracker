<?php
$css_class = "";

if($mv -> tasks -> filter -> hasParams() || isset($_GET["filters-reject"]))
    $css_class = ' class="active"';
?>
<div class="filters-buttons">
    <div id="filters-header"<?php echo $css_class; ?>><?php echo I18n :: locale("filters"); ?></div>
    <?php if($mv -> tasks -> filter -> hasParams()): ?>
       <div class="found-amount"><?php echo I18n :: locale("results-found"); ?>: <?php echo $total; ?></div>
       <input type="button" class="button no-border" value="<?php echo I18n :: locale("drop-filters"); ?>" id="filters-reject" />
    <?php endif; ?>
</div>
<div class="field-list">
    <div class="button options green medium"><?php echo I18n :: locale("tune-table"); ?></div>
    <?php $html = $mv -> tasks -> getActiveAndPassiveColumnsOptions($columns); ?>
    <div class="list">
        <div class="m2m-wrapper clearfix">
            <div class="column">
                <div class="header"><?php echo I18n :: locale("not-selected"); ?></div>
                <select class="m2m-not-selected" multiple="multiple">
                   <?php echo $html["passive"]; ?>
                </select>
            </div>
            <div class="m2m-buttons">
                <span class="m2m-right" title="<?php echo I18n :: locale("move-selected"); ?>"></span>
                <span class="m2m-left" title="<?php echo I18n :: locale("move-not-selected"); ?>"></span>
            </div>
            <div class="column">
                <div class="header"><?php echo I18n :: locale("selected"); ?></div>
                <select class="m2m-selected" multiple="multiple">
                   <?php echo $html["active"]; ?>
                </select>
            </div>
            <div class="m2m-buttons">
                <span class="m2m-up" title="<?php echo I18n :: locale("move-up"); ?>"></span>
                <span class="m2m-down" title="<?php echo I18n :: locale("move-down"); ?>"></span>
            </div>
        </div>
        <div class="controls">
            <input id="save-columns-<?php echo $current_view; ?>" class="apply button green small" type="button" value="<?php echo I18n :: locale("apply"); ?>">
            <input class="cancel button no-border" value="<?php echo I18n :: locale("cancel"); ?>" type="button">
        </div>
    </div>
</div>
<div class="mobile-filters-buttons"></div>
<form<?php echo $css_class; ?> id="filters-form" action="<?php echo $mv -> root_path.$base_url; ?>" method="get">
    <?php
	    foreach ($filter_fields as $field)
	    {
	        echo "<div class=\"section\">\n";
	        echo $mv -> tasks -> filter -> display(array($field));
	        echo "</div>\n";
	    }
    ?>
    <div class="buttons">
        <input type="submit" value="<?php echo I18n :: locale("apply"); ?>" class="button green small"/>
        <span id="filters-hide" class="button no-border"><?php echo I18n :: locale("drop-filters"); ?></span>
    </div>
</form>