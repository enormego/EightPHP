<?php
/**
 * HTML helper class.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class html_Core {

	// Enable or disable automatic setting of target="_blank"
	public static $windowed_urls = NO;

	/**
	 * Convert special characters to HTML entities
	 *
	 * @param   string   string to convert
	 * @param   boolean  encode existing entities
	 * @return  string
	 */
	public static function specialchars($str, $double_encode = YES) {
		// Force the string to be a string
		$str = (string) $str;

		// Do encode existing HTML entities (default)
		if($double_encode === YES) {
			$str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
		} else {
			// Do not encode existing HTML entities
			// From PHP 5.2.3 this functionality is built-in, otherwise use a regex
			if(version_compare(PHP_VERSION, '5.2.3', '>=')) {
				$str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8', NO);
			} else {
				$str = preg_replace('/&(?!(?:#\d++|[a-z]++);)/ui', '&amp;', $str);
				$str = str_replace(array('<', '>', '\'', '"'), array('&lt;', '&gt;', '&#39;', '&quot;'), $str);
			}
		}

		return $str;
	}

	/**
	 * Create HTML link anchors.
	 *
	 * @param   string  URL or URI string
	 * @param   string  link text
	 * @param   array   HTML anchor attributes
	 * @param   string  non-default protocol, eg: https
	 * @return  string
	 */
	public static function anchor($uri, $title = nil, $attributes = nil, $protocol = nil) {
		if($uri === '') {
			$site_url = url::base(NO);
		} elseif(strpos($uri, '://') === NO and strpos($uri, '#') !== 0) {
			$site_url = url::site($uri, $protocol);
		} else {
			if(html::$windowed_urls === YES and empty($attributes['target'])) {
				$attributes['target'] = '_blank';
			}

			$site_url = $uri;
		}

		return
		// Parsed URL
		'<a href="'.html::specialchars($site_url, NO).'"'
		// Attributes empty? Use an empty string
		.(is_array($attributes) ? html::attributes($attributes) : '').'>'
		// Title empty? Use the parsed URL
		.(($title === nil) ? $site_url : $title).'</a>';
	}

	/**
	 * Creates an HTML anchor to a file.
	 *
	 * @param   string  name of file to link to
	 * @param   string  link text
	 * @param   array   HTML anchor attributes
	 * @param   string  non-default protocol, eg: ftp
	 * @return  string
	 */
	public static function file_anchor($file, $title = nil, $attributes = nil, $protocol = nil) {
		return
		// Base URL + URI = full URL
		'<a href="'.html::specialchars(url::base(NO, $protocol).$file, NO).'"'
		// Attributes empty? Use an empty string
		.(is_array($attributes) ? html::attributes($attributes) : '').'>'
		// Title empty? Use the filename part of the URI
		.(($title === nil) ? end(explode('/', $file)) : $title) .'</a>';
	}

	/**
	 * Similar to anchor, but with the protocol parameter first.
	 *
	 * @param   string  link protocol
	 * @param   string  URI or URL to link to
	 * @param   string  link text
	 * @param   array   HTML anchor attributes
	 * @return  string
	 */
	public static function panchor($protocol, $uri, $title = NO, $attributes = NO) {
		return html::anchor($uri, $title, $attributes, $protocol);
	}

	/**
	 * Create an array of anchors from an array of link/title pairs.
	 *
	 * @param   array  link/title pairs
	 * @return  array
	 */
	public static function anchor_array(array $array) {
		$anchors = array();
		foreach($array as $link => $title) {
			// Create list of anchors
			$anchors[] = html::anchor($link, $title);
		}
		return $anchors;
	}

	/**
	 * Generates an obfuscated version of an email address.
	 *
	 * @param   string  email address
	 * @return  string
	 */
	public static function email($email) {
		$safe = '';
		foreach(str_split($email) as $letter) {
			switch (($letter === '@') ? rand(1, 2) : rand(1, 3)) {
				// HTML entity code
				case 1: $safe .= '&#'.ord($letter).';'; break;
				// Hex character code
				case 2: $safe .= '&#x'.dechex(ord($letter)).';'; break;
				// Raw (no) encoding
				case 3: $safe .= $letter;
			}
		}

		return $safe;
	}

	/**
	 * Creates an email anchor.
	 *
	 * @param   string  email address to send to
	 * @param   string  link text
	 * @param   array   HTML anchor attributes
	 * @return  string
	 */
	public static function mailto($email, $title = nil, $attributes = nil) {
		if(empty($email))
			return $title;

		// Remove the subject or other parameters that do not need to be encoded
		if(strpos($email, '?') !== NO) {
			// Extract the parameters from the email address
			list ($email, $params) = explode('?', $email, 2);

			// Make the params into a query string, replacing spaces
			$params = '?'.str_replace(' ', '%20', $params);
		} else {
			// No parameters
			$params = '';
		}

		// Obfuscate email address
		$safe = html::email($email);

		// Title defaults to the encoded email address
		empty($title) and $title = $safe;

		// Parse attributes
		empty($attributes) or $attributes = html::attributes($attributes);

		// Encoded start of the href="" is a static encoded version of 'mailto:'
		return '<a href="&#109;&#097;&#105;&#108;&#116;&#111;&#058;'.$safe.$params.'"'.$attributes.'>'.$title.'</a>';
	}

	/**
	 * Generate a "breadcrumb" list of anchors representing the URI.
	 *
	 * @param   array   segments to use as breadcrumbs, defaults to using Router::$segments
	 * @return  string
	 */
	public static function breadcrumb($segments = nil) {
		empty($segments) and $segments = Router::$segments;

		$array = array();
		while($segment = array_pop($segments)) {
			$array[] = html::anchor
			(
				// Complete URI for the URL
				implode('/', $segments).'/'.$segment,
				// Title for the current segment
				ucwords(inflector::humanize($segment))
			);
		}

		// Retrun the array of all the segments
		return array_reverse($array);
	}

	/**
	 * Creates a meta tag.
	 *
	 * @param   string|array   tag name, or an array of tags
	 * @param   string         tag "content" value
	 * @return  string
	 */
	public static function meta($tag, $value = nil) {
		if(is_array($tag)) {
			$tags = array();
			foreach($tag as $t => $v) {
				// Build each tag and add it to the array
				$tags[] = html::meta($t, $v);
			}

			// Return all of the tags as a string
			return implode("\n", $tags);
		}

		// Set the meta attribute value
		$attr = in_array(strtolower($tag), Eight::config('http.meta_equiv')) ? 'http-equiv' : 'name';

		return '<meta '.$attr.'="'.$tag.'" content="'.$value.'" />';
	}

	/**
	 * Creates a stylesheet link.
	 *
	 * @param   string|array  filename, or array of filenames to match to array of medias
	 * @param   string|array  media type of stylesheet, or array to match filenames
	 * @param   boolean       include the index_page in the link
	 * @return  string
	 */
	public static function stylesheet($style, $media = NO, $index = NO) {
		return html::link($style, 'stylesheet', 'text/css', '.css', $media, $index);
	}

	/**
	 * Creates a link tag.
	 *
	 * @param   string|array  filename
	 * @param   string|array  relationship
	 * @param   string|array  mimetype
	 * @param   string        specifies suffix of the file
	 * @param   string|array  specifies on what device the document will be displayed
	 * @param   boolean       include the index_page in the link
	 * @return  string
	 */
	public static function link($href, $rel, $type, $suffix = NO, $media = NO, $index = NO) {
		$compiled = '';

		if(is_array($href)) {
			foreach($href as $_href) {
				$_rel   = is_array($rel) ? array_shift($rel) : $rel;
				$_type  = is_array($type) ? array_shift($type) : $type;
				$_media = is_array($media) ? array_shift($media) : $media;

				$compiled .= html::link($_href, $_rel, $_type, $suffix, $_media, $index);
			}
		} else {
			if(strpos($href, '://') === NO) {
				// Make the URL absolute
				$href = url::base($index).$href;
			}

			$length = strlen($suffix);

			if(substr_compare($href, $suffix, -$length, $length, NO) !== 0) {
				// Add the defined suffix
				$href .= $suffix;
			}

			$attr = array
			(
				'rel' => $rel,
				'type' => $type,
				'href' => $href,
			);

			if(!empty($media)) {
				// Add the media type to the attributes
				$attr['media'] = $media;
			}

			$compiled = '<link'.html::attributes($attr).' />';
		}

		return $compiled."\n";
	}

	/**
	 * Creates a script link.
	 *
	 * @param   string|array  filename
	 * @param   boolean       include the index_page in the link
	 * @return  string
	 */
	public static function script($script, $index = NO) {
		$compiled = '';

		if(is_array($script)) {
			foreach($script as $name) {
				$compiled .= html::script($name, $index);
			}
		} else {
			if(strpos($script, '://') === NO) {
				// Add the suffix only when it's not already present
				$script = url::base((bool) $index).$script;
			}

			if(substr_compare($script, '.js', -3, 3, NO) !== 0) {
				// Add the javascript suffix
				$script .= '.js';
			}

			$compiled = '<script type="text/javascript" src="'.$script.'"></script>';
		}

		return $compiled."\n";
	}

	/**
	 * Creates a image link.
	 *
	 * @param   string        image source, or an array of attributes
	 * @param   string|array  image alt attribute, or an array of attributes
	 * @param   boolean       include the index_page in the link
	 * @return  string
	 */
	public static function image($src = nil, $alt = nil, $index = NO) {
		// Create attribute list
		$attributes = is_array($src) ? $src : array('src' => $src);

		if(is_array($alt)) {
			$attributes += $alt;
		} elseif(!empty($alt)) {
			// Add alt to attributes
			$attributes['alt'] = $alt;
		}

		if(strpos($attributes['src'], '://') === NO) {
			// Make the src attribute into an absolute URL
			$attributes['src'] = url::base($index).$attributes['src'];
		}

		return '<img'.html::attributes($attributes).' />';
	}

	/**
	 * Creates an embedded flash object. If you use an array of attributes,
	 * define the flash source with the "data" key.
	 *
	 * @param   string        flash source, or an array of attributes
	 * @param   boolean       include the index_page in the link
	 * @return  string
	 */
	public static function flash($data = nil, $index = NO) {
		// Create attribute list
		$attributes = is_array($data) ? $data : array('data' => $data);

		$attributes += array
		(
			// Add the Flash mime type
			'type'  => 'application/x-shockwave-flash',

			// Default alt text
			'alt' => '<a href="http://www.adobe.com/go/getflashplayer">Please download Adobe Flash Player.</a>',
		);

		// Remove the alt text from the string
		$alt = $attributes['alt'];
		unset($attributes['alt']);

		if(strpos($attributes['data'], '://') === NO) {
			// Make the src attribute into an absolute URL
			$attributes['data'] = url::base($index).$attributes['data'];
		}

		return '<object'.html::attributes($attributes).'><param name="movie" value="'.$attributes['data'].'" />'.$alt.'</object>';
	}

	/**
	 * Compiles an array of HTML attributes into an attribute string.
	 *
	 * @param   string|array  array of attributes
	 * @return  string
	 */
	public static function attributes($attrs) {
		if(empty($attrs))
			return '';

		if(is_string($attrs))
			return ' '.$attrs;

		$compiled = '';
		foreach($attrs as $key => $val) {
			$compiled .= ' '.$key.'="'.$val.'"';
		}

		return $compiled;
	}

	
	/**
	 * Creates a group of dropdown boxes for date selection
	 * 
	 * @param	string		comma seperated list of parts to be included
	 * @param	string		form element name prefix example: user_
	 * @param	array		variables to be used within the function
	 * @return	string		html for specified elements
	 */
	public static function date_dropdown($parts = 'month,day,year,time', $prefix='', $other=array(), $selected=array())  {
		if(!is_array($parts)) {
			$parts = explode(',', $parts);
		}
		
		// Blank HTML string.
		$html = '';
		
		foreach($parts as $part) {
			switch($part) {
				case 'month':
					if(!isset($selected['month'])) {
						$selected['month'] = date('n'); 
					}
					$html .= form::dropdown($prefix.'month', date::months(), $selected['month']);
					break;
				case 'day':
					if(!isset($selected['month'])) {
						$selected['day'] = date('j');
					}
					$html .= form::dropdown($prefix.'day', date::days(date('n')), $selected['day']);
					break;
				case 'year':
					if(!isset($selected['year'])) {
						$selected['year'] = date('Y');
					}
					
					if(!isset($other['year']['start'])) {
						$other['year']['start'] = false;
					}
					
					if(!isset($other['year']['end'])) {
						$other['year']['end'] = false;
					}
					
					$html .= form::dropdown($prefix.'year', date::years($other['year']['start'], $other['year']['end']), $selected['year']);
					break;
				case 'time':
					if(!isset($selected['hour'])) {
						$selected['hour'] = date('g');
					}
					$html .= form::dropdown($prefix.'hour', date::hours(), $selected['hour']);
					
					if(!isset($selected['min'])) {
						$selected['min'] = date('i');
					}
					$html .= form::dropdown($prefix.'min', date::minutes(1), $selected['min']);
					
					if(!isset($selected['ampm'])) {
						$selected['ampm'] = date('a');
					}
					$html .= form::dropdown($prefix.'ampm', array('am' => 'am' ,'pm' => 'pm'), $selected['ampm']);
			}
		}
		
		return $html;
	}
	
	
	/**
	 * Converts markdown syntax to html.
	 *
	 * @param   string  markdown string
	 * @return  string
	 */
	public static function markdown2html($str) {
		require_once Eight::find_file('vendor','Markdown');
		return Markdown($str);
	}
	
	/**
	 * Converts RGB to HTML Hex
	 *
	 * @param   integer  red
	 * @param   integer  green
	 * @param   integer  blue
	 * @return  hex
	 */
	public static function rgb2hex($r, $g, $b) {
        return sprintf('#%02X%02X%02X', $r, $g, $b);
	}
	
	/**
	 * Converts HSB to HTML Hex
	 *
	 * @param   integer  hue
	 * @param   integer  saturation
	 * @param   integer  brightness
	 * @return  hex
	 */
	public static function hsb2hex($hue, $saturation, $brightness) {
		$c = html::hsb2rgb($hue, $saturation, $brightness);
		return html::rgb2hex($c['red'], $c['green'], $c['blue']);
	}
	
	public static function hsb2rgb($hue, $saturation, $brightness) {
		$hue = num::round($hue, 3);
		$saturation = num::round($saturation, 3);
		$brightness = num::round($brightness, 3);

	    $hexBrightness = (int) round($brightness * 2.55);
	    if ($saturation == 0) {
	        return array('red' => $hexBrightness, 'green' => $hexBrightness, 'blue' => $hexBrightness);
	    }

		$Hi = floor($hue / 60);
	    $f = $hue / 60 - $Hi;
	    $p = (int) round($brightness * (100 - $saturation) * .0255);
	    $q = (int) round($brightness * (100 - $f * $saturation) * .0255);
	    $t = (int) round($brightness * (100 - (1 - $f) * $saturation) * .0255);
	    switch ($Hi) {
	        case 0:
	            return array('red' => $hexBrightness, 'green' => $t, 'blue' => $p);
	        case 1:
	            return array('red' => $q, 'green' => $hexBrightness, 'blue' => $p);
	        case 2:
	            return array('red' => $p, 'green' => $hexBrightness, 'blue' => $t);
	        case 3:
	            return array('red' => $p, 'green' => $q, 'blue' => $hexBrightness);
	        case 4:
	            return array('red' => $t, 'green' => $p, 'blue' => $hexBrightness);
	        case 5:
	            return array('red' => $hexBrightness, 'green' => $p, 'blue' => $q);
	    }
	
	    return false;    
	}

} // End html

