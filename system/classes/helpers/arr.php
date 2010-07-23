<?php
/**
 * Array helper class.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class arr_Core {

	/**
	 * Return a callback array from a string, eg: limit[10,20] would become
	 * array('limit', array('10', '20'))
	 *
	 * @param   string  callback string
	 * @return  array
	 */
	public static function callback_string($str) {
		// command[param,param]
		if(preg_match('/([^\[]*+)\[(.+)\]/', (string) $str, $match)) {
			// command
			$command = $match[1];

			// param,param
			$params = preg_split('/(?<!\\\\),/', $match[2]);
			$params = str_replace('\,', ',', $params);
		} else {
			// command
			$command = $str;

			// No params
			$params = nil;
		}

		return array($command, $params);
	}

	/**
	 * Rotates a 2D array clockwise.
	 * Example, turns a 2x3 array into a 3x2 array.
	 *
	 * @param   array    array to rotate
	 * @param   boolean  keep the keys in the final rotated array. the sub arrays of the source array need to have the same key values.
	 *                   if your subkeys might not match, you need to pass NO here!
	 * @return  array
	 */
	public static function rotate($source_array, $keep_keys = YES) {
		$new_array = array();
		foreach($source_array as $key => $value) {
			$value = ($keep_keys === YES) ? $value : array_values($value);
			foreach($value as $k => $v) {
				$new_array[$k][$key] = $v;
			}
		}

		return $new_array;
	}
	
	/**
	 * Allows you to insert something into an array at a specific position
	 * 
	 * @param	array 		array to insert into (passed by reference)
	 * @param	mixed		variable to insert
	 * @param	int			position at which the variable will be inserted
	 * 
	 * @return	boolean
	 */
	public static function insert(&$array, $insert, $position = -1) {
	     $position = ($position == -1) ? (count($array)) : $position ;
	     if($position != (count($array))) {
	          $ta = $array;
	          for($i = $position; $i < (count($array)); $i++) {
	               if(!isset($array[$i])) {
	                    throw new Eight_Exception("Invalid array: All keys must be numerical and in sequence.");
	               }
	               $tmp[$i+1] = $array[$i];
	               unset($ta[$i]);
	          }
	          $ta[$position] = $insert;
	          $array = $ta + $tmp;
	     } else {
	          $array[$position] = $insert;          
	     }

	     ksort($array);
	     return true;
	}
	
	/**
	 * Removes a key from an array and returns the value.
	 *
	 * @param   string  key to return
	 * @param   array   array to work on
	 * @return  mixed   value of the requested array key
	 */
	public static function remove($key, & $array) {
		if(!array_key_exists($key, $array))
			return nil;

		$val = $array[$key];
		unset($array[$key]);

		return $val;
	}

	
	/**
	 * Extract one or more keys from an array. Each key given after the first
	 * argument (the array) will be extracted. Keys that do not exist in the
	 * search array will be nil in the extracted data.
	 *
	 * @param   array   array to search
	 * @param   string  key name
	 * @return  array
	 */
	public static function extract(array $search, $keys) {
		// Get the keys, removing the $search array
		$keys = array_slice(func_get_args(), 1);

		$found = array();
		foreach($keys as $key) {
			if(isset($search[$key])) {
				$found[$key] = $search[$key];
			} else {
				$found[$key] = nil;
			}
		}

		return $found;
	}
	
	/**
	 * Retrieve a single key from an array. If the key does not exist in the
	 * array, the default value will be returned instead.
	 * 
	 * @param	array	array to search
	 * @param	string	key
	 * @param	mixed	default value
	 * @return	mixed
	 */
	public static function get($search, $key, $default=NULL) {
		if(is_array($search)) {
			if(array_key_exists($key, $search)) {
				return $search[$key];
			} else {
				return $default;
			}
		} else if(is_object($search)) {
			return $search->$key;
		} else {
			return $default;
		}
	}

	/**
	 * Because PHP does not have this function.
	 *
	 * @param   array   array to unshift
	 * @param   string  key to unshift
	 * @param   mixed   value to unshift
	 * @return  array
	 */
	public static function unshift_assoc( array & $array, $key, $val) {
		$array = array_reverse($array, YES);
		$array[$key] = $val;
		$array = array_reverse($array, YES);

		return $array;
	}

	/**
	 * Because PHP does not have this function, and array_walk_recursive creates
	 * references in arrays and is not truly recursive.
	 *
	 * @param   mixed  callback to apply to each member of the array
	 * @param   array  array to map to
	 * @return  array
	 */
	public static function map_recursive($callback, array $array) {
		foreach($array as $key => $val) {
			// Map the callback to the key
			$array[$key] = is_array($val) ? arr::map_recursive($callback, $val) : call_user_func($callback, $val);
		}

		return $array;
	}

	/**
	 * Binary search algorithm.
	 *
	 * @param   mixed    the value to search for
	 * @param   array    an array of values to search in
	 * @param   boolean  return NO, or the nearest value
	 * @param   mixed    sort the array before searching it
	 * @return  integer
	 */
	public static function binary_search($needle, $haystack, $nearest = NO, $sort = NO) {
		if($sort === YES) {
			sort($haystack);
		}

		$high = count($haystack);
		$low = 0;

		while($high - $low > 1) {
			$probe = ($high + $low) / 2;
			if($haystack[$probe] < $needle) {
				$low = $probe;
			} else {
				$high = $probe;
			}
		}

		if($high == count($haystack) OR $haystack[$high] != $needle) {
			if($nearest === NO)
				return NO;

			// return the nearest value
			$high_distance = $haystack[ceil($low)] - $needle;
			$low_distance = $needle - $haystack[floor($low)];

			return ($high_distance >= $low_distance) ? $haystack[ceil($low)] : $haystack[floor($low)];
		}

		return $high;
	}

	/**
	 * Emulates array_merge_recursive, but appends numeric keys and replaces
	 * associative keys, instead of appending all keys.
	 *
	 * @param   array  any number of arrays
	 * @return  array
	 */
	public static function merge() {
		$total = func_num_args();

		$result = array();
		for($i = 0; $i < $total; $i++) {
			foreach(func_get_arg($i) as $key => $val) {
				if(isset($result[$key])) {
					if(is_array($val)) {
						// Arrays are merged recursively
						$result[$key] = arr::merge($result[$key], $val);
					} elseif(is_int($key)) {
						// Indexed arrays are appended
						array_push($result, $val);
					} else {
						// Associative arrays are replaced
						$result[$key] = $val;
					}
				} else {
					// New values are added
					$result[$key] = $val;
				}
			}
		}

		return $result;
	}

	/**
	 * Overwrites an array with values from input array(s).
	 * Non-existing keys will not be appended!
	 *
	 * @param   array   key array
	 * @param   array   input array(s) that will overwrite key array values
	 * @return  array
	 */
	public static function overwrite($array1) {
		foreach(array_slice(func_get_args(), 1) as $array2) {
			foreach($array2 as $key => $value) {
				if(array_key_exists($key, $array1)) {
					$array1[$key] = $value;
				}
			}
		}

		return $array1;
	}

	/**
	 * Fill an array with a range of numbers.
	 *
	 * @param   integer  stepping
	 * @param   integer  ending number
	 * @return  array
	 */
	public static function range($step = 10, $max = 100) {
		if($step < 1)
			return array();

		$array = array();
		for($i = $step; $i <= $max; $i += $step) {
			$array[$i] = $i;
		}

		return $array;
	}
	
	/**
	 * Makes foreach loops a bit easier with an auto-check built in to prevent "passed var must be an array" errors
	 * 
	 */
	public static function c($array) {
		if(is_array($array)) {
			return $array;
		} elseif(is_string($array)) {
			return array($array);
		} else {
			return array();
		}
	}
	
	
	/**
	 * Grabs a random element from the provided array
	 * 
	 * @param		array 		array of which you want a random element of
	 * @return		mixed		random element of the array
	 */
	public static function random($array) {
		return $array[array_rand($array)];
	}
	
	/**
	 * Sets the key path of an array to the specified value
	 * 
	 * @param		mixed		pass in an key path, keys separated by decimals, or an array of keys.
	 * @return		array		array with the key path set
	 */
	public static function set_key_path($arr, $path, $value) {
		if(!is_array($path))
			$path = explode(".", $path);
			
		if(count($path) == 1) {
			$arr[$path[0]] = $value;
		} else {
			$key = array_shift($path);
			$arr[$key] = arr::set_key_path($arr[$key], $path, $value);
		}

		return $arr;
	}
	
	/**
	 * Checks to see if given array is an associative array or not
	 * 
	 * @param		array		array of elements
	 * @return		bool
	 */
	public static function is_assoc($array) {
	    return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
	}
	
	/**
	 * Recursively converts an object to a multi-dimensional array
	 * 
	 * @param		object
	 * @return		array
	 */
	public static function from_object($obj) {
		if(!is_object($obj) && !is_array($obj)) return NULL;
		
		$raw_arr = is_object($obj) ? get_object_vars($obj) : $obj;
		$arr = array();
		
        foreach($raw_arr as $k => $v) {
               $arr[$k] = (is_array($v) || is_object($v)) ? arr::from_object($v) : $v;
        }

        return $arr;
	}
	
	/**
	 * Converts an associative array of arbitrary depth and dimension into JSON representation.
	 *
	 * NOTE: If you pass in a mixed associative and vector array, it will prefix each numerical
	 * key with "key_". For example array("foo", "bar" => "baz") will be translated into
	 * {'key_0': 'foo', 'bar': 'baz'} but array("foo", "bar") would be translated into [ 'foo', 'bar' ].
	 *
	 * @param $array The array to convert.
	 * @return mixed The resulting JSON string, or false if the argument was not an array.
	 * @author Andy Rusterholz
	 */
	public static function to_json($arr) {
	    if( !is_array( $arr ) ){
	        return "[ ]";
	    }
	
	    if(function_exists('json_encode'))
			return json_encode($arr);
		
	    $parts = array(); 
	    $is_list = false; 

	    //Find out if the given array is a numerical array 
	    $keys = array_keys($arr); 
	    $max_length = count($arr)-1; 
	    if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {//See if the first key is 0 and last key is length - 1 
	        $is_list = true; 
	        for($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position 
	            if($i != $keys[$i]) { //A key fails at position check. 
	                $is_list = false; //It is an associative array. 
	                break; 
	            } 
	        } 
	    } 

	    foreach($arr as $key=>$value) { 
	        if(is_array($value)) { //Custom handling for arrays 
	            if($is_list) $parts[] = array2json($value); /* :RECURSION: */ 
	            else $parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */ 
	        } else { 
	            $str = ''; 
	            if(!$is_list) $str = '"' . $key . '":'; 

	            //Custom handling for multiple data types 
	            if(is_numeric($value)) $str .= $value; //Numbers 
	            elseif($value === false) $str .= 'false'; //The booleans 
	            elseif($value === true) $str .= 'true'; 
	            else $str .= '"' . addslashes($value) . '"'; //All other things 
	            // :TODO: Is there any more datatype we should be in the lookout for? (Object?) 

	            $parts[] = $str; 
	        } 
	    } 
	
	    $json = implode(',',$parts); 

	    if($is_list) return '[' . $json . ']';//Return numerical JSON 
	    return '{' . $json . '}';//Return associative JSON 

	}
	
	/**
	 * Checks if an array is empty or not. If the passed var is
	 * not an array it will return FALSE.
	 * 
	 * @param	array 		Pass an array to be checked
	 * @return	boolean 	Whether or not the array is empty
	 * @author	Saverio Mondelli
	 */
	public static function e($arr) {
		if(is_array($arr) && !empty($arr)) {
			return false;
		} else {
			return true;
		}
	}
	
} // End arr