<?php
/**
 * MV - content management framework for developing internet sites and applications.
 * 
 * Main configuration settings array.
 * These values go to Registry object to get the settings from any part of the application.
 * Any value can be taken through Registry :: get('name') method.
 */

$mvMainSettings = [

//Supported regional packages for internationalization located at ~adminpanel/i18n/
//'us' - american, the same as english ('en') exept for date format.
'SupportedRegions' => ['en', 'us', 'ru'],

//Initial version of MV framework for internal needs (do not change it).
'Version' => 3.15,

//Initial version of MV tracker for internal needs (do not change it).
'MvTrackerVersion' => 1.31,

//Allowed data types for models' fields
'ModelsDataTypes' => ['bool','int','float','char','url','redirect','email','phone','password','text','enum','parent',
					  'order','date','date_time','image','multi_images','file','many_to_one','many_to_many','group'],

 //All allowed types of files for uploading.
'AllowedFiles' => ['gif', 'jpg', 'jpeg', 'png', 'svg', 'webp', 'zip', 'rar', 'gzip', 'txt', 'doc', 'docx', 'rtf', 
                   'xls','xlsx', 'csv', 'pdf'],

//All allowed types of images to for uploading.
'AllowedImages' => ['gif', 'jpg', 'jpeg', 'png', 'svg', 'webp'],

//Quality of .jpg images created by GD functions when resize.
'JpgQuality' => 90,

//Mime types to check for uploaded images
'DefaultImagesMimeTypes' => ['image/jpeg', 'image/gif', 'image/png', 'image/svg+xml', 'image/webp'],

//Max allowed file size of any type of file, excluding images (in bytes).
'MaxFileSize' => 1048576 * 3, 

//Max allowed image file size (in bytes).
'MaxImageSize' => 1048576 * 2, 

//Max allowed width of uploaded image (in pixels).
'MaxImageWidth' => 1920, 

//Max allowed height of uploaded image (in pixels).
'MaxImageHeight' => 1600, 

//Admin panel user session maximum duration (in seconds).
'SessionLifeTime' => 3600 * 3, 

 //New generated password lifiteime to be confirmed by user.
'NewPasswordLifeTime' => 10800 / 3,

 //After 3 incorrect passwords when login in admin panel the ip of user is added into special list 
 // and during the next login attempts the users will have to fill captcha before this time lasts.
'LoginCaptchaLifeTime' => 3600,

//Time interval in seconds from last hit of user when we show that user is online.
'UserOnlineTime' => 900,

//Time interval in seconds for autologin cookies for admin panel.
'AutoLoginLifeTime' => 3600 * 24 * 31 * 3,

//Forbidden (reserved) names of models fields.
'ForbiddenFieldsNames' => ['page','done','pager-limit','sort-field','sort-order', 'multi-action','multi-value',
						   'version','continue','restore','edit'],
			
//Forbidden names of models.
'ForbiddenModelsNames' => ['model','settings','users_logins','users_passwords',
						   'users_rights','users_sessions','versions'],
								
//Maximum execution time of data processing during csv files uploading in admin panel.
'CsvUploadTimeLimit' => 180,

//Maximum number of versions for each model record in admin panel (false - disables versions saving).
'ModelVersionsLimit' => 25
];