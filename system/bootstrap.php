<?php
/**
 * Eight process control file, loaded by the front controller.
 * 
 * @package		System
 * @subpackage	Core
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

define('EIGHT_VERSION',  '1.0');
define('YES', TRUE);
define('NO', FALSE);
define('nil', NULL);

// Eight benchmarks are prefixed to prevent collisions
define('SYSTEM_BENCHMARK', 'system_benchmark');

// Load benchmarking support
require SYSPATH.'classes/core/benchmark'.EXT;

// Start total_execution
Benchmark::start(SYSTEM_BENCHMARK.'_total_execution');

// Start environment_test
Benchmark::start(SYSTEM_BENCHMARK.'_environment_test');

// Test of Eight is running in Windows
define('EIGHT_IS_WIN', DIRECTORY_SEPARATOR === '\\');

// Check UTF-8 support
if(!preg_match('/^.$/u', 'ñ')) {
	trigger_error(
		'<a href="http://php.net/pcre">PCRE</a> has not been compiled with UTF-8 support. '.
		'See <a href="http://php.net/manual/reference.pcre.pattern.modifiers.php">PCRE Pattern Modifiers</a> '.
		'for more information. This application cannot be run without UTF-8 support.',
		E_USER_ERROR
	);
}

if(!extension_loaded('iconv')) {
	trigger_error(
		'The <a href="http://php.net/iconv">iconv</a> extension is not loaded. '.
		'Without iconv, strings cannot be properly translated to UTF-8 from user input. '.
		'This application cannot be run without UTF-8 support.',
		E_USER_ERROR
	);
}

if(extension_loaded('mbstring') and (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING)) {
	trigger_error(
		'The <a href="http://php.net/mbstring">mbstring</a> extension is overloading PHP\'s native string functions. '.
		'Disable this by setting mbstring.func_overload to 0, 1, 4 or 5 in php.ini or a .htaccess file.'.
		'This application cannot be run without UTF-8 support.',
		E_USER_ERROR
	);
}

// Check PCRE support for Unicode properties such as \p and \X.
$ER = error_reporting(0);
define('PCRE_UNICODE_PROPERTIES', (bool) preg_match('/^\pL$/u', 'ñ'));
error_reporting($ER);

// SERVER_UTF8 ? use mb_* functions : use non-native functions
if(extension_loaded('mbstring')) {
	mb_internal_encoding('UTF-8');
	define('SERVER_UTF8', YES);
} else {
	define('SERVER_UTF8', NO);
}

// Stop environment_test
Benchmark::stop(SYSTEM_BENCHMARK.'_environment_test');

// Start system_initialization
Benchmark::start(SYSTEM_BENCHMARK.'_system_initialization');

// Load Event support
require SYSPATH.'classes/core/event'.EXT;

// Load Eight core
require SYSPATH.'classes/core/eight'.EXT;

// Prepare the environment
Eight::setup();

// Prepare the system
Event::run('system.ready');

// Determine routing
Event::run('system.routing');

// End system_initialization
Benchmark::stop(SYSTEM_BENCHMARK.'_system_initialization');

// Make the magic happens!
Event::run('system.execute');

// Clean up and exit
Event::run('system.shutdown');