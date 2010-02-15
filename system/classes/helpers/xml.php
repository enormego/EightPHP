<?php
/**
 * XML helper class.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class xml_Core {

	/**
	 * Convert an associative array to XML
	 * If array is multi-dimensional, it will attempt to detect keyed vs ordered and build XML accordingly
	 *
	 * @param	array	associative array
	 * @param	string	root element
	 * @return	string	XML String
	 */
	public static function assoc2xml($assoc, $root_element="xml") {
		if(!is_array($assoc) || count($assoc) == 0) return "<".$root_element." />\n";

		foreach($assoc as $key => $value) {
			if($value === YES) {
				$value = "true";
			} else if($value === NO) {
				$value = "false";
			} else if(!is_array($value) && strlen($value) == 0) {
				$xml .= "<".$key." />\n";
				continue;
			} else if(is_array($value)) {
				if(arr::is_assoc($value)) {
					$xml .= xml::assoc2xml($value, $key);
				} else {
					$xml .= xml::ordered2xml($value, $key);
				}
				continue;
			} else if(self::use_cdata($value)) {
				$value = "<![CDATA[".$value."]]>";
			} else {
				$value = htmlentities($value);
			}
			
			$xml .= "<".$key.">".$value."</".$key.">\n";
		}
		
		return "<".$root_element.">\n".xml::pad($xml)."</".$root_element.">\n";
	}
	
	/**
	 * Convert an ordered array to XML
	 * Wraps each element in the singular form of the root element
	 *
	 * @param	array	ordered array
	 * @param	string	root element, plural
	 * @return	string	XML String
	 */
	public static function ordered2xml($ordered, $root_element="elements") {
		if(!is_array($ordered) || count($ordered) == 0) return "<".$root_element." />\n";

		foreach($ordered as $element) {
			$xml .= xml::assoc2xml($element, inflector::singular($root_element));
		}
		
		return "<".$root_element.">\n".xml::pad($xml)."</".$root_element.">\n";
	}
	
	/**
	 * Determine whether or not to wrap a string in CDATA
	 *
	 * @param	string	string to check
	 * @return	bool
	 */
	public static function use_cdata($value) {
		if(is_numeric($value)) {
			return NO;
		}
		
		if(!str::contains($value, array("\n", "\r"))) {
			if(strlen(preg_replace("/[a-z0-9\.\_ ]+/i", "", $value)) == 0) {
				return NO;
			}
		}
		
		// We'll make this more extensive later on
		
		return YES;
	}
	
	/**
	 * Converts an XML String to an associative array
	 * @uses XMLParser_Core
	 *
	 * @param	string	XML String
	 * @return	array
	 */
	public static function xml2assoc($data) {
		$xml = new XMLParser($data);
		return $xml->to_array();
	}
	
	/**
	 * Gets the value for a key in an associative array
	 *
	 * @note Assumes array is structured from XMLParser or xml::xml2assoc
	 * @see XMLParser_Core
	 * @see xml_Core::xml2assoc()
	 *
	 * @param	array	XMLParser/xml2assoc generated array
	 * @param	string	Name of the element
	 * @return	mixed
	 */
	public static function value_for_key($arr, $key) {
		return $arr[$key][0]['_value'];
	}
	
	/**
	 * Gets the value for a keypath in an associative array
	 *
	 * @note Assumes array is structured from XMLParser or xml::xml2assoc
	 * @see XMLParser_Core
	 * @see xml_Core::xml2assoc()
	 *
	 * @param	array	XMLParser/xml2assoc generated array
	 * @param	string	Key path to the element
	 * @return	mixed
	 */
	public static function value_for_path($arr, $path) {
		$node = self::node_for_path($arr, $path);
		return $node[0]['_value'];
	}
	
	/**
	 * Gets the value for an attribute at a key path in an associative array
	 *
	 * @note Assumes array is structured from XMLParser or xml::xml2assoc
	 * @see XMLParser_Core
	 * @see xml_Core::xml2assoc()
	 *
	 * @param	array	XMLParser/xml2assoc generated array
	 * @param	string	Name of the attribute
	 * @param	string	Key path to the element
	 * @return	mixed
	 */
	public static function attribute_value_for_path($arr, $attribute, $path) {
		$node = self::node_for_path($arr, $path);
		return $node[0]['_attributes'][$attribute];
	}
	
	/**
	 * Gets the array node for a key in an associative array
	 *
	 * @note Assumes array is structured from XMLParser or xml::xml2assoc
	 * @see XMLParser_Core
	 * @see xml_Core::xml2assoc()
	 *
	 * @param	array	XMLParser/xml2assoc generated array
	 * @param	string	Name of the element
	 * @return	array
	 */
	public static function node_for_key($arr, $key) {
		return $arr[$key];
	}
	
	/**
	 * Gets the array node for at a key path in an associative array
	 *
	 * @note Assumes array is structured from XMLParser or xml::xml2assoc
	 * @see XMLParser_Core
	 * @see xml_Core::xml2assoc()
	 *
	 * @param	array	XMLParser/xml2assoc generated array
	 * @param	string	Key path to the element
	 * @return	array	array node
	 */
	public static function node_for_path($arr, $path) {
		$path = explode('.', $path);
		$node = $arr;
		$first = NO;
		foreach($path as $key) {
			if(!$first) {
				$node = $node[$key];
				$first = YES;
			} else {
				$node = $node[0][$key];
			}
		}
		
		return $node;
	}
	
	/**
	 * Pads a XML String with a single tab, without breaking any CDATA wrapped elements
	 *
	 * @param	string	XML String
	 * @return	string	Padded XML String
	 */
	public static function pad($xml_string) {
		$org_cdatas = NULL;
		if(preg_match_all("#<!\[CDATA\[(.+?)\]\]>#is", $xml_string, $matches)) {
			$org_cdatas = $matches[0];
		}
		
		$xml_string = "\t".rtrim(implode("\n\t", explode("\n", $xml_string)))."\n";
		
		if(is_array($org_cdatas) && preg_match_all("#<!\[CDATA\[(.+?)\]\]>#is", $xml_string, $matches)) {
			$xml_string = str_replace($matches[0], $org_cdatas, $xml_string);
		}
		
		return $xml_string;
	}
}