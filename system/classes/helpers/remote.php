<?php
/**
 * Remote url/file helper.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class remote_Core {
	/**
	 * Host for the proxy
	 */
	public static $proxy_host = NULL;

	/**
	 * Port for the proxy
	 */
	public static $proxy_port = NULL;

	/**
	 * Username for proxy, optional
	 */
	public static $proxy_user = NULL;

	/**
	 * Password for proxy, optional
	 */
	public static $proxy_pass = NULL;
	
	/**
	 * Setup helper to use proxies, currently only works with get and post
	 * @see remote_Core::get()
	 * @see remote_Core::post()
	 *
	 * @param   string	Host for the proxy
	 * @param   string	Port for the proxy
	 * @param   string	Username for proxy, optional
	 * @param   string	Password for proxy, optional
	 */
	public static function set_proxy($host, $port, $user=NULL, $pass=NULL) {
		self::$proxy_host = $host;
		self::$proxy_port = $port;
		self::$proxy_user = $user;
		self::$proxy_pass = $pass;
	}
	
	/**
	 * Clears all proxy settings
	 */
	public static function clear_proxy() {
		self::$proxy_host = self::$proxy_port = self::$proxy_user = self::$proxy_pass = NULL;
	}
	
	/**
	 * Uses sockets to check the status of a url.
	 *
	 * @param   string	url to post to
	 * @return  mixed	NO if URL is down or invalid, otherwise returns status code.
	 */
	public static function status($url) {
		if(!valid::url($url, 'http'))
			return NO;

		// Get the hostname and path
		$url = parse_url($url);

		if(empty($url['path'])) {
			// Request the root document
			$url['path'] = '/';
		}

		// Open a remote connection
		$remote = fsockopen($url['host'], 80, $errno, $errstr, 5);

		if(!is_resource($remote))
			return NO;

		// Set CRLF
		$CRLF = "\r\n";

		// Send request
		fwrite($remote, 'HEAD '.$url['path'].' HTTP/1.0'.$CRLF);
		fwrite($remote, 'Host: '.$url['host'].$CRLF);
		fwrite($remote, 'Connection: close'.$CRLF);
		fwrite($remote, 'User-Agent: Eight Framework (+http://eightphp.com/)'.$CRLF);

		// Send one more CRLF to terminate the headers
		fwrite($remote, $CRLF);

		while(!feof($remote)) {
			// Get the line
			$line = trim(fgets($remote, 512));

			if($line !== '' and preg_match('#^HTTP/1\.[01] (\d{3})#', $line, $matches)) {
				// Response code found
				$response = (int) $matches[1];

				break;
			}
		}

		// Close the connection
		fclose($remote);

		return isset($response) ? $response : NO;
	}
	
	/**
	 * Uses cURL to retrieve the given URL
	 *
	 * @param   string	url to grab
	 * @param   int		timeout period
	 * @param   string	user agent
	 * @return  array
	 */
	public static function get($url, $timeout=120, $ua='GoogleBot') {
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($c, CURLOPT_USERAGENT, $ua); 
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		self::populate_proxy($c);
		$output = curl_exec($c);
		$info = curl_getinfo($c);
		curl_close($c);
		
		$result = array(
							'content'		=>	$output,
							'info'			=>	$info,
							'http_code'		=>	$info['http_code'],
						);
		
		return $result;
	}
	
	/**
	 * Uses cURL to perform an HTTP post.
	 *
	 * @param   string	url to post to
	 * @param   array	array of form data to post
	 * @return  curl_exec() result
	 */
	public static function post($url, $data, $return_headers=NO) {
		if(!is_array($data)) {
			$data = http_build_query($data);
		}
		
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_HEADER, $return_headers); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
		self::populate_proxy($ch);
		$data = curl_exec($ch); 
		curl_close($ch);
		return $data;
	}
	
	/**
	 * Populates the cURL handle with proxy info
	 * 
	 * @param	resources	curl handle
	 */
	protected function populate_proxy($ch) {
		if(!str::e(self::$proxy_host) && self::$proxy_port > 0) {
			curl_setopt($ch, CURLOPT_PROXY, self::$proxy_host.':'.self::$proxy_port); 
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0); 
			
			if(!str::e(self::$proxy_user) && !str::e(self::$proxy_pass)) {
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, self::$proxy_user.':'.self::$proxy_pass); 
			}
		}
	}

} // End remote