<?php
/**
 * Base URL path of the website, including domain.
 *
 *     $config['site_domain'] = '/eight/';
 *
 * If the site_domain contains a domain, eg: wwww.example.com/eight/, then a
 * full URL, including the protocol and domain will be generated. If set to a
 * a path, generated URLs will not contain a domain name. (See exception in
 * [site_protocol][ref-sip] below.)
 */
$config['site_domain'] = '/eight/';

/**
 * Set a default protocol protocol for this application.
 *
 *     $config['site_protocol'] = '';
 *
 * If no site_protocol is specified, then the current protocol will be detected.
 * This setting must be left empty if you do not want generated URLs to contain
 * the domain name.
 */
$config['site_protocol'] = '';

/**
 * Name of the front controller for this application.
 *
 *     $config['index_page'] = 'index.php';
 *
 * If the front controller is removed from the URL using [rewriting][ref-url],
 * this setting must be set to an empty string, or generated URLs will still
 * contain the index_page filename.
 *
 * [ref-url]: http://doc.eight.twenty08.com/routing
 */
$config['index_page'] = 'index.php';

/**
 * Length of internal configuration, language, and include path caching.
 *
 *    $config['internal_cache'] = NO;
 *
 * Disabled by default, internal caching can give significant speed improvements
 * at the expense of configuration changes being visibly delayed. Enabling
 * short (30-300) seconds of internal caching on production sites is a highly
 * recommended way to increase performance.
 */
$config['internal_cache'] = NO;

/**
 * Enable or disable gzip output compression.
 *
 *     $config['output_compression'] = NO;
 *
 * Disabled by default, gzip output compression can significantly increase page
 * latency by decreasing server bandwidth usage, at the cost of slightly higher
 * CPU usage. A number from 1-9 can be used to set the compression level, or
 * YES can be used to use the PHP default.
 *
 * **Do not enable this if PHP output compression is enabled in php.ini!**
 */
$config['output_compression'] = NO;

/**
 * Enable or disable statistics in the final output.
 *
 *     $config['render_stats'] = YES;
 *
 * Enabled by default, this will replace specific strings in generated output
 * with generated statistics or information.
 *
 * {execution_time}
 * :  Total execution time in seconds
 *
 * {memory_usage}
 * :  Total memory usage in megabytes (MB)
 *
 * {included_files}
 * :  All of the filenames that are currently loaded
 *
 * {eight_version}
 * :  The Eight release version number
 *
 * {eight_codename}
 * :  The Eight release code name
 *
 * This setting can be disabled for a small performance increase.
 */
$config['render_stats'] = YES;

/**
 * Enable or disable global XSS filtering of GET, POST, and SERVER data.
 *
 *    $config['global_xss_filtering'] = YES;
 *
 * Enabled by default, global XSS filtering prevents client-side output attacks.
 * This can either be YES or 'htmlpurifier' to use [HTMLPurifier][ref-hpr].
 */
$config['global_xss_filtering'] = YES;

/**
 * Sometimes Eight catches page not found errors that we just don't care about.
 *
 * This array will be searched before any 404's are thrown. If the page that an
 * error is about to be thrown for is in this array, Eight will silently discard
 * the error. This prevents tons of "robots.txt not found" showing up in your 
 * server logs.
 */
$config['ignore_page_not_found'] = array
(
	'robots.txt', 
	'favicon.ico',
);

/**
 * Enable or disable displaying of Eight error pages. This will not affect
 * logging. Turning this off will disable ALL error pages.
 */
$config['display_errors'] = YES;

/**
 * Set default logging threshold.
 *
 *     $config['log_threshold'] = 1;
 *
 * It is highly recommended to enable error and exception logging on production
 * websites and to disable
 *
 * - 0: Disable all logging
 * - 1: Log only PHP errors and exceptions
 * - 2: Also log PHP warnings
 * - 3: Also log PHP notices
 * - 4: Also log Eight debugging messages
 */
$config['log_threshold'] = 1;

/**
 * Set default logging directory.
 *
 *     $config['log_directory'] = APPPATH.'logs';
 *
 * Any writable directory can be specified here. Path can be relative to the
 * DOCROOT, or an absolute path.
 */
$config['log_directory'] = APPPATH.'logs';

/**
 * Enable or disable hooks, raw PHP files that are included during setup.
 *
 *     $config['enable_hooks'] = NO;
 *
 * Disabled by default, hooks allow you to change default Events, run custom
 * code, and extend Eight in completely custom ways. This option can be set
 * to YES, NO, or an array of filenames (no extension).
 */
$config['enable_hooks'] = NO;

/**
 * Additional resource paths, or "modules". Each path can either be absolute
 * or relative to the DOCROOT. Modules can include any resource that can exist
 * in your application directory, configuration files, controllers, views, etc.
 */
$config['modules'] = array
(
	// MODPATH.'archive',   // Archive utility
	// MODPATH.'auth',      // Authentication
	// MODPATH.'Formation',     // Form generation
	// MODPATH.'kodoc',     // Self-generating documentation
	// MODPATH.'media',     // Media caching and compression
	// MODPATH.'gmaps',     // Google Maps integration
	// MODPATH.'payment',   // Online payments
	// MODPATH.'unit_test', // Unit testing
	// MODPATH.'object_db', // New OOP Database library (testing only!)
);
