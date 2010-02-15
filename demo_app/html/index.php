<?php
/**
 * This file acts as the "front controller" to your application. You can
 * configure your application, modules, and system directories here.
 * PHP error_reporting level may also be changed.
 *
 * @see http://eight.twenty08.com
 */

/**
 * Define the website environment status. When this flag is set to true, some
 * module demonstration controllers will result in 404 errors. For more information
 * about this option, read the documentation about deploying Eight.
 *
 * @see http://docs.eight.twenty08.com/installation/deployment
 */
define('IN_PRODUCTION', false);

/**
 * Website application directory. This directory should contain your application
 * configuration, controllers, models, views, and other resources.
 *
 * This path can be absolute or relative to this file.
 */
$eight_application = '../application';

/**
 * Eight modules directory. This directory should contain all the modules used
 * by your application. Modules are enabled and disabled by the application
 * configuration file.
 *
 * This path can be absolute or relative to this file.
 */
$eight_modules = '../../modules';

/**
 * Eight system directory. This directory should contain the core/ directory,
 * and the resources you included in your download of Eight.
 *
 * This path can be absolute or relative to this file.
 */
$eight_system = '../../system';

/**
 * Test to make sure that Eight is running on PHP 5.2 or newer. Once you are
 * sure that your environment is compatible with Eight, you can comment this
 * line out. When running an application on a new server, uncomment this line
 * to check the PHP version quickly.
 */
version_compare(PHP_VERSION, '5.2', '<') and exit('Eight requires PHP 5.2 or newer.');

/**
 * Set the error reporting level. Unless you have a special need, E_ALL is a
 * good level for error reporting.
 */
error_reporting(E_ALL & ~E_STRICT);

/**
 * Turning off display_errors will effectively disable Eight error display
 * and logging. You can turn off Eight errors in application/config/config.php
 */
ini_set('display_errors', true);

/**
 * If you rename all of your .php files to a different extension, set the new
 * extension here. This option can left to .php, even if this file has a
 * different extension.
 */
define('EXT', '.php');

//
// DO not EDIT BELOW THIS LINE, UNLESS YOU FULLY UNDERSTAND THE IMPLICATIONS.
// ----------------------------------------------------------------------------
// $Id: index.php 1 2008-09-18 16:08:15Z shaun $
//

// Define the front controller name and docroot
define('DOCROOT', getcwd().DIRECTORY_SEPARATOR);
define('EIGHT',  basename(__FILE__));

// If the front controller is a symlink, change to the real docroot
is_link(EIGHT) and chdir(dirname(realpath(__FILE__)));

// Define application and system paths
define('APPPATH', str_replace('\\', '/', realpath($eight_application)).'/');
define('MODPATH', str_replace('\\', '/', realpath($eight_modules)).'/');
define('SYSPATH', str_replace('\\', '/', realpath($eight_system)).'/');

// Clean up
unset($eight_application, $eight_modules, $eight_system);

if(!IN_PRODUCTION) {
	// Check APPPATH
	if(!(is_dir(APPPATH) and is_file(APPPATH.'config/config'.EXT))) {
		die (
			'<div style="width:80%;margin:50px auto;text-align:center;">'.
				'<h3>Application Directory not Found</h3>'.
				'<p>The <code>$eight_application</code> directory does not exist.</p>'.
				'<p>Set <code>$eight_application</code> in <tt>'.EIGHT.'</tt> to a valid directory and refresh the page.</p>'.
			'</div>'
		);
	}

	// Check SYSPATH
	if(!(is_dir(SYSPATH) and is_file(SYSPATH.'bootstrap'.EXT))) {
		die (
			'<div style="width:80%;margin:50px auto;text-align:center;">'.
				'<h3>System Directory not Found</h3>'.
				'<p>The <code>$eight_system</code> directory does not exist.</p>'.
				'<p>Set <code>$eight_system</code> in <tt>'.EIGHT.'</tt> to a valid directory and refresh the page.</p>'.
			'</div>'
		);
	}
}

// Initialize Eight
require SYSPATH.'bootstrap'.EXT;