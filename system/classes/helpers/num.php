<?php
/**
 * Number helper class.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class num_Core {

	/**
	 * Round a number to the nearest nth
	 *
	 * @param   integer  number to round
	 * @param   integer  number to round to
	 * @return  integer
	 */
	public static function round($number, $nearest = 5) {
		return round($number / $nearest) * $nearest;
	}
	
	public static function hex2rgb($color) {
	    if ($color[0] == '#')
	        $color = substr($color, 1);
	
	    if (strlen($color) == 6)
	        list($r, $g, $b) = array($color[0].$color[1],
	                                 $color[2].$color[3],
	                                 $color[4].$color[5]);
	    elseif (strlen($color) == 3)
	        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
	    else
	        return false;
	
	    $r = hexdec($r);
	    $g = hexdec($g);
	    $b = hexdec($b);
	
	    return array('r' => $r, 'g' => $g, 'b' => $b);
	}

} // End num