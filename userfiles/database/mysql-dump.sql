SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `login` varchar(30) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `photo` varchar(250) NOT NULL,
  `email` varchar(70) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `send_emails` tinyint(4) NOT NULL,
  `date_last_visit` datetime NOT NULL,
  `date_registration` datetime NOT NULL,
  `password_token` varchar(255) NOT NULL,
  `autologin_key` varchar(255) NOT NULL,
  `settings` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `documentation` (
  `id` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `author` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime NOT NULL,
  `files` text NOT NULL,
  `settings` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `garbage` (
  `id` int(11) NOT NULL,
  `module` varchar(100) NOT NULL,
  `row_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `journal` (
  `id` int(11) NOT NULL,
  `task` int(11) NOT NULL,
  `account` int(11) NOT NULL,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `files` text NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `module` varchar(100) NOT NULL,
  `row_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `operation` varchar(30) NOT NULL,
  `date` datetime NOT NULL,
  `name` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `options` (
  `key` varchar(200) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `priorities` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `position` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `color` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `priorities` (`id`, `name`, `position`, `active`, `color`) VALUES
(3, 'Low', 1, 1, ''),
(4, 'Normal', 2, 1, ''),
(5, 'High', 3, 1, '#ffded4'),
(6, 'Immediate', 4, 1, '#ffded4');

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL,
  `description` text DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `settings` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `projects` (`id`, `name`, `active`, `description`, `date_created`, `date_updated`, `settings`) VALUES
(1, 'Test project', 1, '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '');

CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL,
  `closed` tinyint(1) NOT NULL,
  `position` int(11) DEFAULT NULL,
  `color` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `statuses` (`id`, `name`, `active`, `closed`, `position`, `color`) VALUES
(1, 'New', 1, 0, 1, ''),
(2, 'In progress', 1, 0, 2, ''),
(3, 'Completed', 1, 0, 3, ''),
(4, 'Feedback', 1, 0, 4, ''),
(5, 'Closed', 1, 1, 5, ''),
(6, 'Rejected', 1, 1, 6, '');

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `tracker` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date_due` date DEFAULT NULL,
  `status` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `priority` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `complete` smallint(11) NOT NULL,
  `hours_estimated` float NOT NULL,
  `hours_spent` float NOT NULL,
  `files` text NOT NULL,
  `settings` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `trackers` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `position` int(11) DEFAULT 1,
  `active` tinyint(1) NOT NULL,
  `color` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `trackers` (`id`, `name`, `position`, `active`, `color`) VALUES
(1, 'Bug', 1, 1, ''),
(2, 'Feature', 2, 1, ''),
(3, 'Support', 3, 1, '');

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(100) NOT NULL,
  `login` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_registered` datetime NOT NULL,
  `date_last_visit` datetime NOT NULL,
  `settings` text NOT NULL,
  `active` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_logins` (
  `login` varchar(100) NOT NULL,
  `date` datetime NOT NULL,
  `user_agent` varchar(32) NOT NULL,
  `ip_address` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_passwords` (
  `user_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `password` varchar(255) NOT NULL,
  `code` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_rights` (
  `user_id` int(11) NOT NULL,
  `module` varchar(100) NOT NULL,
  `create` tinyint(4) NOT NULL,
  `read` tinyint(4) NOT NULL,
  `update` tinyint(4) NOT NULL,
  `delete` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_sessions` (
  `user_id` int(11) NOT NULL,
  `session_id` varchar(32) NOT NULL,
  `ip_address` varchar(20) NOT NULL,
  `user_agent` varchar(32) NOT NULL,
  `last_hit` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `versions` (
  `id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `row_id` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `content` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `documentation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_project_id` (`project`),
  ADD KEY `index_documents_on_created_on` (`date_created`);

ALTER TABLE `garbage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module` (`module`);

ALTER TABLE `journal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task` (`task`),
  ADD KEY `account` (`account`);

ALTER TABLE `log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module` (`module`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `options`
  ADD PRIMARY KEY (`key`);

ALTER TABLE `priorities`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active` (`active`);

ALTER TABLE `settings`
  ADD UNIQUE KEY `key` (`key`);

ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active_closed` (`active`,`closed`);

ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project` (`project`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `author` (`author`);

ALTER TABLE `trackers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `email_active` (`email`,`active`);

ALTER TABLE `users_logins`
  ADD KEY `ip_date` (`ip_address`,`date`);

ALTER TABLE `users_passwords`
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `users_rights`
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `users_sessions`
  ADD UNIQUE KEY `users_sessions_all` (`user_id`,`session_id`,`ip_address`,`user_agent`),
  ADD UNIQUE KEY `users_sessions_uid_sid` (`user_id`,`session_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `model_row_id` (`model`,`row_id`),
  ADD KEY `model_version` (`model`,`version`),
  ADD KEY `model_row_id_version` (`model`,`row_id`,`version`);


ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `documentation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `garbage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `journal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `priorities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `trackers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
