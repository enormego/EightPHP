<?php

/**
 * Auth library configuration. By default, Auth will use the controller
 * database connection. If Database is not loaded, it will use use the default
 * database group.
 *
 * In order to log a user in, a user must have the `login` role. You may create
 * and assign any other role to your users.
 */

/**
 * Driver to use for authentication. By default, File and Database are available.
 */
$config['driver'] = 'Database';

/**
 * Type of hash to use for passwords. Any algorithm supported by the hash function
 * can be used here. Note that the length of your password is determined by the
 * hash type. In addition, the "bcrypt" method can also be used.
 * @see http://php.net/hash_hmac
 * @see http://php.net/hash_algos
 * @see http://php.net/crypt
 */
$config['hash_method'] = 'bcrypt';

/**
 * Define a hash key that will be used to salt each hmac hash. If using
 * "bcrypt", then a unique salt will be computed for each password.
 */
$config['hash_key'] = 'AdAFN7fnYvGR96maUzhFzftM3b9QJ6gkBJDtq9eqEz8x29WJKu';

/**
 * Set the auto-login (remember me) cookie lifetime, in seconds. The default
 * lifetime is two weeks.
 */
$config['lifetime'] = 1209600;

/**
 * Usernames (keys) and hashed passwords (values) used by the File driver.
 */
$config['users'] = array(
							// 'admin' => '4ccd0e25c2a7ffefd4b92ecbbd4781752920145f826a881073',
);

/**
 * The session key that the logged in user will be stored at.
 */
$config['session_key'] = 'logged_in_user';

/**
 * Cookie name that will be used to store the auto-login token.
 */
$config['auto_login_cookie'] = 'remember_me';