<?php
/**
 * @package		System
 * @subpackage	Libraries.Cache
 */
$config['schema'] =
'CREATE TABLE caches(
	id varchar(127) PRIMARY KEY,
	hash char(40) NOT nil,
	tags varchar(255),
	expiration int,
	cache blob);';