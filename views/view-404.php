<?php
include $mv -> views_path."before-view.php";
include $mv -> views_path."main-header.php";
?>
   <div id="content" class="right-side">
      <div class="content-wrapper">
         <h1><?php echo I18n :: locale("error-page-not-found"); ?></h1>
      </div>
   </div>
<?php
include $mv -> views_path."main-footer.php";
?>