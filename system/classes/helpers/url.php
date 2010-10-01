<?php
/**
 * URL helper class.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

/**
 * @see http://www.php.net/manual/en/function.http-build-url.php#96335
 */
if (!function_exists('http_build_url') && !defined('HTTP_URL_REPLACE')) {
	define('HTTP_URL_REPLACE', 1);				// Replace every part of the first URL when there's one of the second URL
	define('HTTP_URL_JOIN_PATH', 2);			// Join relative paths
	define('HTTP_URL_JOIN_QUERY', 4);			// Join query strings
	define('HTTP_URL_STRIP_USER', 8);			// Strip any user authentication information
	define('HTTP_URL_STRIP_PASS', 16);			// Strip any password authentication information
	define('HTTP_URL_STRIP_AUTH', 32);			// Strip any authentication information
	define('HTTP_URL_STRIP_PORT', 64);			// Strip explicit port numbers
	define('HTTP_URL_STRIP_PATH', 128);			// Strip complete path
	define('HTTP_URL_STRIP_QUERY', 256);		// Strip query string
	define('HTTP_URL_STRIP_FRAGMENT', 512);		// Strip any fragments (#identifier)
	define('HTTP_URL_STRIP_ALL', 1024);			// Strip anything but scheme and host
}

class url_Core {

	/**
	 * Fetches the current URI.
	 *
	 * @param   boolean  include the query string
	 * @return  string
	 */
	public static function current($qs = NO) {
		return ($qs === YES) ? Router::$complete_uri : Router::$current_uri;
	}

	/**
	 * Base URL, with or without the index page.
	 *
	 * If protocol (and core.site_protocol) and core.site_domain are both empty,
	 * then
	 *
	 * @param   boolean  include the index page
	 * @param   boolean  non-default protocol
	 * @return  string
	 */
	public static function base($index = NO, $protocol = NO) {
		if($protocol == NO) {
			// Use the default configured protocol
			$protocol = Eight::config('core.site_protocol');
		}

		// Load the site domain
		$site_domain = (string) Eight::config('core.site_domain', YES);

		if($protocol == NO) {
			if($site_domain === '' OR $site_domain[0] === '/') {
				// Use the configured site domain
				$base_url = $site_domain;
			} else {
				// Guess the protocol to provide full http://domain/path URL
				$base_url = request::protocol().'://'.$site_domain;
			}
		} else {
			if($site_domain === '' OR $site_domain[0] === '/') {
				// Guess the server name if the domain starts with slash
				$base_url = $protocol.'://'.$_SERVER['HTTP_HOST'].$site_domain;
			} else {
				// Use the configured site domain
				$base_url = $protocol.'://'.$site_domain;
			}
		}

		if($index === YES and $index = Eight::config('core.index_page')) {
			// Append the index page
			$base_url = $base_url.$index;
		}

		// Force a slash on the end of the URL
		return rtrim($base_url, '/').'/';
	}

	/**
	 * Fetches an absolute site URL based on a URI segment.
	 *
	 * @param   string  site URI to convert
	 * @param   string  non-default protocol
	 * @return  string
	 */
	public static function site($uri = '', $protocol = NO) {
		if($path = trim(parse_url($uri, PHP_URL_PATH), '/')) {
			// Add path suffix
			$path .= Eight::config('core.url_suffix');
		}

		if($query = parse_url($uri, PHP_URL_QUERY)) {
			// ?query=string
			$query = '?'.$query;
		}

		if($fragment = parse_url($uri, PHP_URL_FRAGMENT)) {
			// #fragment
			$fragment =  '#'.$fragment;
		}

		// Concat the URL
		return url::base(YES, $protocol).$path.$query.$fragment;
	}
	
	/**
	 * Decides whether or not to return a site URL or the passed URL.
	 * Very useful when you're passing URI's and URL's and need 1 function to do both.
	 * 
	 * @param		string		site URL or URI
	 * @param		string		non-default protocol
	 * @return		string
	 */
	public static function ify($uri = '', $protocol = NO) {
		if(str::is_url($uri)) {
			return $uri;
		}
		
		return url::site($uri, $protocol);
	}
		
	/**
	 * Return the URL to a file. Absolute filenames and relative filenames
	 * are allowed.
	 *
	 * @param   string   filename
	 * @param   boolean  include the index page
	 * @return  string
	 */
	public static function file($file, $index = NO) {
		if(strpos($file, '://') === NO) {
			// Add the base URL to the filename
			$file = url::base($index).$file;
		}

		return $file;
	}

	/**
	 * Merges an array of arguments with the current URI and query string to
	 * overload, instead of replace, the current query string.
	 *
	 * @param   array   associative array of arguments
	 * @return  string
	 */
	public static function merge(array $arguments) {
		if($_GET === $arguments) {
			$query = Router::$query_string;
		} elseif($query = http_build_query(array_merge($_GET, $arguments))) {
			$query = '?'.$query;
		}

		// Return the current URI with the arguments merged into the query string
		return Router::$current_uri.$query;
	}

	/**
	 * Convert a phrase to a URL-safe title.
	 *
	 * @param   string  phrase to convert
	 * @param   string  word separator (- or _)
	 * @return  string
	 */
	public static function title($title, $separator = '-') {
		$separator = ($separator === '-') ? '-' : '_';

		// Replace accented characters by their unaccented equivalents
		$title = utf8::transliterate_to_ascii($title);

		// Remove all characters that are not the separator, a-z, 0-9, or whitespace
		$title = preg_replace('/[^'.$separator.'a-z0-9\s]+/', '', strtolower($title));

		// Replace all separator characters and whitespace by a single separator
		$title = preg_replace('/['.$separator.'\s]+/', $separator, $title);

		// Trim separators from the beginning and end
		return trim($title, $separator);
	}

	/**
	 * Sends a page redirect header.
	 *
	 * @param  mixed   string site URI or URL to redirect to, or array of strings if method is 300
	 * @param  string  HTTP method of redirect
	 * @return void
	 */
	public static function redirect($uri = '', $method = '302') {
		if(Event::has_run('system.send_headers'))
			return;

		$uri = (array) $uri;

		for($i = 0, $count_uri = count($uri); $i < $count_uri; $i++) {
			if(strpos($uri[$i], '://') === NO) {
				$uri[$i] = url::site($uri[$i]);
			}
		}

		if($method == '300') {
			if($count_uri > 0) {
				header('HTTP/1.1 300 Multiple Choices');
				header('Location: '.$uri[0]);

				$choices = '';
				foreach($uri as $href) {
					$choices .= '<li><a href="'.$href.'">'.$href.'</a></li>';
				}

				exit('<h1>301 - Multiple Choices:</h1><ul>'.$choices.'</ul>');
			}
		} else {
			$uri = $uri[0];

			if($method == 'refresh') {
				header('Refresh: 0; url='.$uri);
			} else {
				$codes = array
				(
					'301' => 'Moved Permanently',
					'302' => 'Found',
					'303' => 'See Other',
					'304' => 'Not Modified',
					'305' => 'Use Proxy',
					'307' => 'Temporary Redirect'
				);

				$method = isset($codes[$method]) ? $method : '302';

				header('HTTP/1.1 '.$method.' '.$codes[$method]);
				header('Location: '.$uri);
			}

			exit('<h1>'.$method.' - '.$codes[$method].'</h1><p><a href="'.$uri.'">'.$uri.'</a></p>');
		}
	}
	
	/**
	 * Build an URL
	 * The parts of the second URL will be merged into the first according to the flags argument.
	 * 
	 * @param	mixed	(Part(s) of) an URL in form of a string or associative array like parse_url() returns
	 * @param	mixed	Same as the first argument
	 * @param	int		A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
	 * @param	array	If set, it will be filled with the parts of the composed url like parse_url() would return 
	 * 
	 * @see http://www.php.net/manual/en/function.http-build-url.php
	 * @see http://www.php.net/manual/en/function.http-build-url.php#96335
	 */
	public static function build($url, $parts=array(), $flags=HTTP_URL_REPLACE, &$new_url=false) {
		if (function_exists('http_build_url')) {
			return http_build_url($url, $parts, $flags, $new_url);
		} else {
			$keys = array('user','pass','port','path','query','fragment');
			
			// HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
			if ($flags & HTTP_URL_STRIP_ALL) {
				$flags |= HTTP_URL_STRIP_USER;
				$flags |= HTTP_URL_STRIP_PASS;
				$flags |= HTTP_URL_STRIP_PORT;
				$flags |= HTTP_URL_STRIP_PATH;
				$flags |= HTTP_URL_STRIP_QUERY;
				$flags |= HTTP_URL_STRIP_FRAGMENT;
			}
			
			// HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
			else if ($flags & HTTP_URL_STRIP_AUTH) {
				$flags |= HTTP_URL_STRIP_USER;
				$flags |= HTTP_URL_STRIP_PASS;
			}
			
			// Parse the original URL
			$parse_url = parse_url($url);
			
			// Scheme and Host are always replaced
			if (isset($parts['scheme']))
				$parse_url['scheme'] = $parts['scheme'];
				
			if (isset($parts['host']))
				$parse_url['host'] = $parts['host'];
			
			// (If applicable) Replace the original URL with it's new parts
			if ($flags & HTTP_URL_REPLACE) {
				foreach ($keys as $key) {
					if (isset($parts[$key]))
						$parse_url[$key] = $parts[$key];
				}
			} else {
				// Join the original URL path with the new path
				if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH)) {
					if (isset($parse_url['path']))
						$parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
					else
						$parse_url['path'] = $parts['path'];
				}
				
				// Join the original query string with the new query string
				if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY)) {
					if (isset($parse_url['query']))
						$parse_url['query'] .= '&' . $parts['query'];
					else
						$parse_url['query'] = $parts['query'];
				}
			}
			
			// Strips all the applicable sections of the URL
			// Note: Scheme and Host are never stripped
			foreach ($keys as $key) {
				if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key)))
					unset($parse_url[$key]);
			}
			
			$new_url = $parse_url;
			
			return 
				 ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
				.((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') .'@' : '')
				.((isset($parse_url['host'])) ? $parse_url['host'] : '')
				.((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
				.((isset($parse_url['path'])) ? $parse_url['path'] : '')
				.((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
				.((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '')
			;
		}
	}

} // End url