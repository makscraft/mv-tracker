<?php
/**
 * Main routing file of the MV application.
 * Views files must be located in 'views' directory.
 * 
 * '' - index page of the application
 * 'e404' - page of 404 error
 * 'fallback' - default fallback url, if it was no any match
 * 
 * Allowed symbols in routes patterns: '*' - any value (dynamic part), '?' - optional part (can be only one in pattern).
 * Url pattern can have any quantity of '*' symbols, and only one symbol '?' at the and of the pattern.
 * 
 * Examples:
 * '/contacts' => 'view-contacts.php'
 * '/news/*'  => 'modules/view-news.php'
 * '/complete/?'  => 'folder/subfolder/view-name.php'
 */

$mvFrontendRoutes = [

'/' => 'view-index.php',
'e404' => 'view-404.php',
'fallback' => 'view-default.php',

'/recovery' => 'login/view-recovery.php',
'/recovery/create' => 'login/view-new-password.php',
'/login' => 'login/view-login.php',

'/home' => 'tasks/view-tasks-my.php',
'/history' => 'profile/view-history.php',
'/profile' => 'profile/view-profile.php',
'/password' => 'profile/view-password.php',
'/search' => 'view-search.php',

'/tasks' => 'tasks/view-tasks-all.php',
'/tasks/create' => 'tasks/view-tasks-create.php',
'/task/*' => 'tasks/view-task.php',
'/task/edit/*' => 'tasks/view-task-edit.php',

'/projects' => 'projects/view-projects-all.php',
'/projects/create' => 'projects/view-project-create.php',
'/project/*' => 'projects/view-project.php',
'/project/edit/*' => 'projects/view-project-edit.php',
'/projects/archive' => 'projects/view-archive.php',
		
'/documentation' => 'documentation/view-index.php',
'/documentation/*' => 'documentation/view-document.php',
'/documentation/create' => 'documentation/view-document-create.php',
'/documentation/edit/*' => 'documentation/view-document-edit.php'
];