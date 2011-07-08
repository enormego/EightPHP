<p>The following tables must be installed in your database: users, roles, and users_roles. If you have not already installed these tables, please run the following query:</p>

<pre>

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` int(11) NOT NULL auto_increment,
  `role_name` varchar(50) NOT NULL,
  PRIMARY KEY  (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `user_email` varchar(50) NOT NULL,
  `user_password` varchar(50) NOT NULL,
  `user_logins` int(11) NOT NULL,
  `user_last_login` int(11) NOT NULL,
  `user_created` int(11) NOT NULL,
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE IF NOT EXISTS `user_roles` (
  `user_role_id` int(11) NOT NULL auto_increment,
  `user_role_user_id` int(11) NOT NULL,
  `user_role_role_id` int(11) NOT NULL,
  PRIMARY KEY  (`user_role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE IF NOT EXISTS `user_tokens` (
  `user_token_id` int(11) NOT NULL auto_increment,
  `user_token_token` varchar(32) NOT NULL,
  `user_token_user_id` int(11) NOT NULL,
  `user_token_user_agent` varchar(50) NOT NULL,
  `user_token_created` int(11) NOT NULL,
  `user_token_expires` int(11) NOT NULL,
  PRIMARY KEY  (`user_token_id`),
  UNIQUE KEY `user_token_token` (`user_token_token`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

</pre>

<p>After the tables have been installed, <?php echo html::anchor('auth_demo/create', 'create a user') ?>.</p>