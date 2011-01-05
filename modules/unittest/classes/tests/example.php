<?php
/**
 * Example Test.
 *
 * @package		Modules
 * @subpackage	UnitTest
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Test_Example extends UnitTest_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public $setup_has_run = FALSE;

	public function setup() {
		$this->setup_has_run = TRUE;
	}

	public function setup_test() {
		$this->assert_true_strict($this->setup_has_run);
	}

	public function TRUE_FALSE_test() {
		$var = TRUE;
		$this
			->assert_true($var)
			->assert_true_strict($var)
			->assert_false(!$var)
			->assert_false_strict(!$var);
	}

	public function equal_same_test() {
		$var = '5';
		$this
			->assert_equal($var, 5)
			->assert_not_equal($var, 6)
			->assert_same($var, '5')
			->assert_not_same($var, 5);
	}

	public function type_test() {
		$this
			->assert_boolean(TRUE)
			->assert_not_boolean('TRUE')
			->assert_integer(123)
			->assert_not_integer('123')
			->assert_float(1.23)
			->assert_not_float(123)
			->assert_array(array(1, 2, 3))
			->assert_not_array('array()')
			->assert_object(new stdClass)
			->assert_not_object('X')
			->assert_null(nil)
			->assert_not_null(0)
			->assert_empty('0')
			->assert_not_empty('1');
	}

	public function pattern_test() {
		$var = "Eight\n";
		$this
			->assert_pattern($var, '/^Eight$/')
			->assert_not_pattern($var, '/^Eight$/D');
	}

	public function array_key_test() {
		$array = array('a' => 'A', 'b' => 'B');
		$this->assert_array_key('a', $array);
	}

	public function in_array_test() {
		$array = array('X', 'Y', 'Z');
		$this->assert_in_array('X', $array);
	}

	public function debug_example_test() {
		foreach(array(1, 5, 6, 12, 65, 128, 9562) as $var) {
			// By supplying $var in the debug parameter,
			// we can see on which number this test fails.
			$this->assert_true($var < 100, $var);
		}
	}

	public function error_test() {
		throw new Exception;
	}

}
