<?
$document = $mv -> documentation -> defineDocumentPage($mv -> router);
$mv -> display404($document);
$token = $mv -> documentation -> generateDeleteToken($document -> id);

$account_id = ($account -> id == $document -> author) ? $account -> id : false;
$edit_url = $mv -> root_path."documentation/edit/".$document -> id;
$back_url = $mv -> root_path."documentation";

include $mv -> views_path."main-header.php";
?>
    <div id="content" class="document-content">
        <? echo $mv -> accounts -> displayReloadMessage(); ?>
        <h1><? echo $document -> name; ?></h1>
        <div class="item-actions document-actions">
            <a class="create" href="<? echo $edit_url; ?>"><? echo I18n :: locale("edit"); ?></a>
            <span class="delete" id="delete-document-<? echo $document -> id."-".$token; ?>">
                <? echo I18n :: locale("delete"); ?>
            </span>
        </div>
        <div class="clear"></div>
        <? if($document -> files): ?>
             <div class="attached-files">
                 <h3><? echo I18n :: locale("attached-files"); ?></h3>
                 <? echo $mv -> journal -> displayFiles($document -> files, $account_id, "documentation-".$document -> id); ?>
             </div>
         <? endif; ?>
         <div class="documentation-content">
             <? echo Tasks :: processDescriptionText($document -> content); ?>
         </div>
        <div class="form-buttons clearfix">
             <a id="back-button" class="button green medium" href="<? echo $back_url; ?>"><? echo I18n :: locale("back"); ?></a>
        </div>         
    </div>
<?
include $mv -> views_path."main-footer.php";
?>