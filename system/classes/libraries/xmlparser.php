<?php
/**
 * Converts XML into an Assoc Array for Easy Working :)
 *
 * @version		$Id: xmlparser.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		System
 * @subpackage	Libraries
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class XMLParser_Core {
	
	private $data; 
	
	public function __construct($xml) {
		if (str::is_path($xml)){
			if(str::is_url($xml)) {
				$r = remote::get($xml);
				$this->data = $r['content'];
			} else {
				$this->data = file_get_contents($xml);
			}
		} else {
			$this->data = $xml;
		}

		if(empty($this->data)) {
			return false;
		}
		
		unset($xml, $r, $fp);
	}
	
	public function __toString() {
		return $this->data;
	}
	
	public function to_array() { 
		$parser = xml_parser_create('UTF-8');
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE,	1);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING,	0);
		xml_parse_into_struct($parser, $this->data, $vals, $index);
		xml_parser_free($parser);
		$tree = array();
		$i = 0;

		if (isset($vals[$i]['attributes'])) {
			$tree[$vals[$i]['tag']][]['_attributes'] = $vals[$i]['attributes'];
			$index = count($tree[$vals[$i]['tag']])-1;
			$tree[$vals[$i]['tag']][$index] =  array_merge($tree[$vals[$i]['tag']][$index], $this->children($vals, $i));
		} else {
			$tree[$vals[$i]['tag']][] = $this->children($vals, $i);
		}

		return $tree;
	}
	
	private function children($vals, &$i) { 
		$children = array(); // Contains node data
		if (isset($vals[$i]['value'])){
			$children['_value'] = $vals[$i]['value'];
		} 

		while (++$i < count($vals)){ 
			switch ($vals[$i]['type']){
				case 'cdata': 
					if (isset($children['_value'])){
						$children['_value'] .= $vals[$i]['value'];
					} else {
						$children['_value'] = $vals[$i]['value'];
					} 

					break;

				case 'complete':
					if (isset($vals[$i]['attributes'])) {
						$children[$vals[$i]['tag']][]['_attributes'] = $vals[$i]['attributes'];
						$index = count($children[$vals[$i]['tag']])-1;

						if (isset($vals[$i]['value'])){ 
							$children[$vals[$i]['tag']][$index]['_value'] = $vals[$i]['value']; 
						} else {
							$children[$vals[$i]['tag']][$index]['_value'] = '';
						}
					} else {
						if (isset($vals[$i]['value'])){
							$children[$vals[$i]['tag']][]['_value'] = $vals[$i]['value']; 
						} else {
							$children[$vals[$i]['tag']][]['_value'] = '';
						} 
					}

					break;

				case 'open': 
					if (isset($vals[$i]['attributes'])) {
						$children[$vals[$i]['tag']][]['_attributes'] = $vals[$i]['attributes'];
						$index = count($children[$vals[$i]['tag']])-1;
						$children[$vals[$i]['tag']][$index] = array_merge($children[$vals[$i]['tag']][$index],$this->children($vals, $i));
					} else {
						$children[$vals[$i]['tag']][] = $this->children($vals, $i);
					}

					break; 
				
				case 'close': 
					return $children; 
					break;
			}
		}
		
		// Just in case the parser doesn't meet one of the cases above.
		return array();
	}

	public function __destruct() {
		unset($this->data);
	}
	
} // End XML Parser Class