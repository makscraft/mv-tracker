<?
$task = $mv -> tasks -> defineTaskPage($mv -> router);
$mv -> display404($task);

$token_delete = $mv -> tasks -> generateDeleteToken($task -> id);
$token_comment = $mv -> tasks -> generateDeleteToken($task -> id);
$back_url = $mv -> root_path."tasks";
$can_delete = $task -> author == $account -> id;

if(isset($_SESSION["last-page"]) && $_SESSION["last-page"] != "task/".$task -> id)
	$back_url = $mv -> root_path.$_SESSION["last-page"];

$_SESSION["last-page"] = "task/".$task -> id;

$comment_id = false;

if(isset($_SESSION["scroll-to-comment"]) && $_SESSION["scroll-to-comment"])
{
	$comment_id = intval($_SESSION["scroll-to-comment"]);
	unset($_SESSION["scroll-to-comment"]);
}

$to_project = $mv -> root_path."project/".$task -> project;
$complete_value = $task -> complete ? $task -> getEnumTitle("complete") : "0%";
$date_due_value = ($task -> date_due && $task -> date_due != "0000-00-00") ? I18n :: formatDate($task -> date_due) : "-";
$comments_total = $mv -> journal -> countRecords(array("task" => $task -> id)) - 1;
$mv -> journal -> removeTaskToSee($account -> id, $task -> id);

include $mv -> views_path."main-header.php";
?>
<div id="content" class="task-content">
    <div class="wrapper-left clearfix">
        <? echo $mv -> accounts -> displayReloadMessage(); ?>
        <? if($comment_id && $comments_total > 1): ?>
        <script type="text/javascript">
            $(document).ready(function()
        	{
                $("html, body").animate({ scrollTop: $("#comment-<? echo $comment_id; ?>").offset().top - 60 }, 1000);
        	}); 
        </script>
        <? endif; ?>
        <a class="back" href="<? echo $to_project; ?>"><? echo $task -> getEnumTitle("project"); ?></a>
        <div class="clear"></div>
        <h1><? echo $task -> name; ?></h1>
        <div class="item-actions task-actions">
            <a class="create" href="<? echo $mv -> root_path; ?>task/edit/<? echo $task -> id; ?>"><? echo I18n :: locale("edit"); ?></a>
            <span class="delete<? echo $can_delete ? "" : " off"; ?>" id="delete-task-<? echo $task -> id."-".$token_delete; ?>"><? echo I18n :: locale("delete"); ?></span>
        </div>        
        <div class="clear"></div>
        <div class="task-info">
           <span class="task-info-photo">
               <span class="account-image">
                  <? echo $mv -> accounts -> displayAvatar($task); ?>
               </span>
               <? echo $task -> getEnumTitle("author"); ?>
           </span>
           <span><? echo Tasks :: processDateTimeValue($task -> date_created); ?></span>
        </div>        
        <div class="task-description">
            <? echo $mv -> tasks -> processDescriptionText($task -> description); ?>
        </div>
        <? if($task -> files): ?>
            <div class="task-files">
                <h3><? echo I18n :: locale("attached-files"); ?></h3>
                <? echo $mv -> journal -> displayFiles($task -> files, $account -> id, "tasks-".$task -> id); ?>
            </div>
        <? endif; ?>
        <hr>
        <div class="button green big add-comment" id="comment-task-<? echo $task -> id."-".$token_comment; ?>">
            <? echo I18n :: locale("add-comment"); ?>
        </div>
        <? if($html = $mv -> journal -> displayTaskHistory($task, $account)): ?>
            <div class="task-history">
                <h2><? echo I18n :: locale("task-comments"); ?></h2>
                <? echo $html; ?>
            </div>
        <?
        	endif;
        	
        	if($comments_total > 2):
        ?>
        <div class="button green big add-comment" id="comment-task-<? echo $task -> id."-".$token_comment; ?>">
            <? echo I18n :: locale("add-comment"); ?>
        </div>
        <? endif; ?>
        <div class="form-buttons clearfix">
            <a class="back" href="<? echo $to_project; ?>"><? echo $task -> getEnumTitle("project"); ?></a>
            <a id="back-button" class="button green medium" href="<? echo $back_url; ?>"><? echo I18n :: locale("back"); ?></a>
        </div>
    </div>
    <div class="task-params">
        <table>
            <tr>
                <td class="second">#</td>
                <td><? echo $task -> id; ?></td>
            </tr>
            <tr>
                <td class="second"><? echo I18n :: locale("project"); ?>:</td>
                <td><? echo $task -> getEnumTitle("project"); ?></td>
            </tr>
            <tr>
                <td class="second"><? echo I18n :: locale("tracker"); ?>:</td>
                <td><? echo $task -> getEnumTitle("tracker"); ?></td>
            </tr>
            <tr>
                <td class="second"><? echo I18n :: locale("date-due"); ?>:</td>
                <td><? echo $date_due_value; ?></td>
            </tr>
            <tr>
                <td><? echo I18n :: locale("assigned-to"); ?>:</td>
                <td><? echo $task -> assigned_to ? $task -> getEnumTitle("assigned_to") : "-"; ?></td>
            </tr>
            <tr>
                <td class="second"><? echo I18n :: locale("priority"); ?>:</td>
                <td><? echo $task -> getEnumTitle("priority"); ?></td>
            </tr>
            <tr class="line">
                <td colspan="2"><hr></td>
            </tr>
            <tr>
                <td><? echo I18n :: locale("status"); ?>:</td>
                <td><? echo $task -> getEnumTitle("status"); ?></td>
            </tr>
            <tr>
                <td><? echo I18n :: locale("implementation"); ?>:</td>
                <td><? echo $complete_value; ?></td>
            </tr>
            <tr>
                <td><? echo I18n :: locale("hours-estimated"); ?>:</td>
                <td><? echo $task -> hours_estimated ? $task -> hours_estimated : "-"; ?></td>
            </tr>
            <tr>
                <td class="second"><? echo I18n :: locale("hours-spent"); ?>:</td>
                <td><? echo $task -> hours_spent ? $task -> hours_spent : "-"; ?></td>
            </tr>
        </table>
    </div>
</div>
<?
include $mv -> views_path."main-footer.php";
?>      