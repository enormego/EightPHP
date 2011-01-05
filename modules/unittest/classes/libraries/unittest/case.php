<?php

/**
 * UnitTest Case
 *
 * @package		Modules
 * @subpackage	UnitTest
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

abstract class UnitTest_Case_Core {

	public function assert_true($value, $debug = nil) {
		if($value != TRUE)
			throw new UnitTest_Exception(Eight::lang('unittest.assert_true', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_true_strict($value, $debug = nil) {
		if($value !== TRUE)
			throw new UnitTest_Exception(Eight::lang('unittest.assert_true_strict', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_false($value, $debug = nil) {
		if($value != FALSE)
			throw new UnitTest_Exception(Eight::lang('unittest.assert_false', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_false_strict($value, $debug = nil) {
		if($value !== FALSE)
			throw new UnitTest_Exception(Eight::lang('unittest.assert_false_strict', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_equal($expected, $actual, $debug = nil) {
		if($expected != $actual)
			throw new UnitTest_Exception(Eight::lang('unittest.assert_equal', gettype($expected), var_export($expected, TRUE), gettype($actual), var_export($actual, TRUE)), $debug);

		return $this;
	}

	public function assert_not_equal($expected, $actual, $debug = nil) {
		if($expected == $actual)
			throw new UnitTest_Exception(Eight::lang('unittest.assert_not_equal', gettype($expected), var_export($expected, TRUE), gettype($actual), var_export($actual, TRUE)), $debug);

		return $this;
	}

	public function assert_same($expected, $actual, $debug = nil) {
		if($expected !== $actual)
			throw new UnitTest_Exception(Eight::lang('unittest.assert_same', gettype($expected), var_export($expected, TRUE), gettype($actual), var_export($actual, TRUE)), $debug);

		return $this;
	}

	public function assert_not_same($expected, $actual, $debug = nil) {
		if($expected === $actual)
			throw new UnitTest_Exception(Eight::lang('unittest.assert_not_same', gettype($expected), var_export($expected, TRUE), gettype($actual), var_export($actual, TRUE)), $debug);

		return $this;
	}

	public function assert_boolean($value, $debug = nil) {
		if(!is_bool($value))
			throw new UnitTest_Exception(Eight::lang('unittest.assert_boolean', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_boolean($value, $debug = nil) {
		if(is_bool($value))
			throw new UnitTest_Exception(Eight::lang('unittest.assert_not_boolean', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_integer($value, $debug = nil) {
		if(!is_int($value))
			throw new UnitTest_Exception(Eight::lang('unittest.assert_integer', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_integer($value, $debug = nil) {
		if(is_int($value))
			throw new UnitTest_Exception(Eight::lang('unittest.assert_not_integer', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_float($value, $debug = nil) {
		if(!is_float($value))
			throw new UnitTest_Exception(Eight::lang('unittest.assert_float', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_float($value, $debug = nil) {
		if(is_float($value))
			throw new UnitTest_Exception(Eight::lang('unittest.assert_not_float', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_array($value, $debug = nil) {
		if(!is_array($value))
			throw new UnitTest_Exception(Eight::lang('unittest.assert_array', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_array_key($key, $array, $debug = nil) {
		if(!array_key_exists($key, $array)) {
			throw new UnitTest_Exception(Eight::lang('unittest.assert_array_key', gettype($key), var_export($key, TRUE)), $debug);
		}

		return $this;
	}

	public function assert_in_array($value, $array, $debug = nil) {
		if(!in_array($value, $array)) {
			throw new UnitTest_Exception(Eight::lang('unittest.assert_in_array', gettype($value), var_export($value, TRUE)), $debug);
		}

		return $this;
	}

	public function assert_not_array($value, $debug = nil) {
		if(is_array($value))
			throw new UnitTest_Exception(Eight::lang('unittest.assert_not_array', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_object($value, $debug = nil) {
		if(!is_object($value))
			throw new UnitTest_Exception(Eight::lang('unittest.assert_object', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_object($value, $debug = nil) {
		if(is_object($value))
			throw new UnitTest_Exception(Eight::lang('unittest.assert_not_object', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_null($value, $debug = nil) {
		if($value !== nil)
			throw new UnitTest_Exception(Eight::lang('unittest.assert_null', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_null($value, $debug = nil) {
		if($value === nil)
			throw new UnitTest_Exception(Eight::lang('unittest.assert_not_null', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_empty($value, $debug = nil) {
		if(!empty($value))
			throw new UnitTest_Exception(Eight::lang('unittest.assert_empty', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_empty($value, $debug = nil) {
		if(empty($value))
			throw new UnitTest_Exception(Eight::lang('unittest.assert_empty', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_pattern($value, $regex, $debug = nil) {
		if(!is_string($value) OR!is_string($regex) OR!preg_match($regex, $value))
			throw new UnitTest_Exception(Eight::lang('unittest.assert_pattern', var_export($value, TRUE), var_export($regex, TRUE)), $debug);

		return $this;
	}

	public function assert_not_pattern($value, $regex, $debug = nil) {
		if(!is_string($value) OR!is_string($regex) OR preg_match($regex, $value))
			throw new UnitTest_Exception(Eight::lang('unittest.assert_not_pattern', var_export($value, TRUE), var_export($regex, TRUE)), $debug);

		return $this;
	}

}
